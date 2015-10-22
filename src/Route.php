<?php
namespace Puz\Router;

use Puz\Router\Exceptions\InvalidRouteCreationException;

class Route
{
    protected $method;
    protected $url;
    protected $callback;

    protected $data = [];

    public function __construct()
    {
        $args = func_get_args();
        for ($i = 0; $i < func_num_args(); $i++) {
            if ($i == 0) {
                $this->setMethod($args[$i]);
            } elseif ($i == 1) {
                $this->setUrl($args[$i]);
            } elseif ($i == 2) {
                $this->setCallback($args[$i]);
            }
        }

        return $this; // Chaining
    }

    public function setUrl($url)
    {
        // Validate the url and scan for parameters
        $url = "/" . trim($url, "/");
        $this->url = $url;

        // Scan for parameters @TODO
        $parameterchar = Router::getParameterCharacter();

        if (preg_match_all("/(:([a-z]+))/", $url, $matches)) {
            $this->params = $matches[2];
        } else {
            $this->params = [];
        }

        return $this; // Chaining
    }

    public function setMethod($method)
    {
        if (!is_array($method)) $method = strtolower($method);
        else {
            foreach($method as &$m) {
                $m = strtolower($m);
            }
        }
        $this->method = $method;
        return $this; // Chaining
    }

    public function setCallback($callback)
    {
        if (!is_callable($callback)) {
            $callback = function() use ($callback) {
                echo (string) $callback;
            };
        }
        $this->callback = $callback;
    }

    public function getRegex($key)
    {
        if (isset($this->data['regex'][$key])) {
            return $this->data['regex'][$key];
        } else {
            return null;
        }
    }

    public function hasMethod($method)
    {
        if (is_array($method)) {
            if (empty($method)) return false;

            foreach($method as $m) {
                if (!$this->hasMethod($m)) return false;
            }
            return true;
        } else {
            $method = strtolower($method);

            if ($method == $this->method || (is_array($this->method) && in_array($method, $this->method))) return true;
            else return false;
        }
    }

    public function where($key, $regex = null, $modifier = null)
    {
		if(is_array($key)){
			foreach($key as $k => $regex){
				$this->where($k, $regex, $modifier);
			}
		}
		else{
            $params = $this->params;

            // Does the argument exists?
            if (!in_array($key, $params))
				throw new \Exception("Parameter \"{$key}\" does not exists in this route ({$this->route}).");

			// Validate regex
			$isvalid = @preg_match($regex, null);
			if($isvalid === false){
				throw new \Exception("RegEx \"{$regex}\" is malformed or invalid. Please provide a valid RegEx code.");
			}

            if (!is_array($this->regex)) $this->regex = [];
            if (empty($regex)) {
                unset($this->data['regex'][$key]);
            } else {
                $regex = [$regex, $modifier];
                $this->data['regex'][$key] = $regex;
            }
		}

        return $this; // For chaining
    }

    /**
	 *	Optional method to use. Could be used to set an alias for a route to for easier redirection or connection to current route
	 *
	 *	@throws \Exception If alias already exists
	 *
	 *	@param string $alias Alias for the current route. Preferable with no special chars and spaces.
	 *	@return void
	 */
	public function name($alias = null){
		$route = self::findRoute($alias);
		if($route === null){
			$this->alias = $alias;
		}
		else throw new \Exception("Alias \"{$alias}\" is already set on the following route (".strtoupper($route->getMethod())." {$route->getRoute()})");

        return $this; // For chaining
	}

    public function __call($name, $arguments)
    {
        $allowedMethods = Router::getAllowedMethods();

        if (strpos($name, "set") === 0) {
            $name = explode("set", $name, 2);
            if (count($name) == 2) {
                $name = strtolower($name[1]);

                if (in_array($name, ['method', 'callback', 'url'])) {
                    $this->{$name} = $arguments[0];
                }
            } else {
                throw new \Exception("Eeeeh wut?");
            }
        } elseif(strpos($name, "get") === 0) {
            $name = explode("get", $name, 2);
            $name = strtolower($name[1]);

            return $this->{$name};
        } elseif (strpos($name, "has") === 0) {
            $name = explode("has", $name, 2);
            $name = strtolower($name[1]);

            if (in_array($name, $allowedMethods)) {
                return $this->hasMethod($name);
            } elseif ($name == "params") {
                return !empty($this->data['params']);
            }
        }
        return $this; // Chaining
    }

    public static function __callStatic($name, $arguments)
    {
        $allowedMethods = Router::getAllowedMethods();

        $route = new Route();
        if (in_array($name, $allowedMethods)) {
            $route->setMethod($name);
            if (!isset($arguments[0]) || empty($arguments[0])) {
                throw new InvalidRouteCreationException("Missing static call parameter value.");
            } else {
                $route->setUrl($arguments[0]);
            }
        } elseif (in_array($name, ['method', 'callback', 'url'])) {
            if (!isset($arguments[0]) || empty($arguments[0])) {
                throw new InvalidRouteCreationException("Missing static call parameter value.");
            } else {
                call_user_func_array([$route, 'set'.ucfirst($name)], $arguments);
            }
        }

        return $route; // Chaining
    }

    public function __set($name, $value)
    {
        if (in_array($name, ['method', 'url', 'callback'])) {
            call_user_func_array([$this, 'set'.ucfirst($name)], [$value]);
        } else {
            $this->data[$name] = $value;
        }
    }

    public function __get($name)
    {
        if (isset($this->data[$name]))
            return $this->data[$name];
        else return null;
    }

    public function __isset($var)
    {
        return isset($this->{$var});
    }

    public function __toString()
    {
        $method = "&lt;method_not_set>";
        if (is_array($this->method)) {
            $method = implode("|", array_map('strtoupper', $this->method));
        } elseif(!empty($this->method)) {
            $method = strtoupper($this->method);
        }

        $url = !empty($this->url) ? $this->url : "&lt;url_not_set>";
        return $method . " ". $url;
    }

    public function call()
    {
        $arguments = func_get_args();
        if (count($arguments) > 0) {
            if (is_array($arguments[0])) $args = $arguments[0];
            else $args = $arguments;
        }

        $callargs = $this->prepareParameters($args);
        // Validate regexes
        if (!$this->validateRegex($callargs['assoc'])) {
            throw new \Exception("RegEx match failed");
        } else {
            if (is_callable($this->callback)) {
                return call_user_func_array($this->callback, $callargs['num']);
            } else {
                throw new \Exception("Uncallable callback");
            }
        }
    }

    public function validateRegex(array $args = [])
    {
        $params = $this->getParams();
        foreach ($params as $param) {
            if (!isset($args[$param])) return false;
            else {
                $regex = $this->getRegex($param);
                if (!is_null($regex)) {
                    $match = preg_match("/^".$regex[0]."$/".$regex[1], $args[$param]);
                    if ($match !== 1) return false;
                }
            }
        }
        return true;
    }

    private function prepareParameters(array $args = [])
    {
        if ($this->hasParams()) {
            $params = $this->params;
            $checkparams = [];
            $assoc = [];
            $type = null;
            foreach ($args as $key => $arg) {
                if (is_int($key) && $type !== "string") {
                    $type = "int";
                    $checkparams[$key] = $arg;
                    $assoc[$params[$key]] = $arg;
                } elseif (is_string($key) && $type !== "int") {
                    $type = "string";
                    if (in_array($key, $params)) {
                        $index = array_keys($params, $key)[0];
                        $checkparams[$index] = $arg;
                        $assoc[$key] = $arg;
                    } else {
                        throw new \Exception("Invalid call argument #1");
                    }
                } else {
                    throw new \Exception("Invalid call argument #2");
                }
            }

            ksort($checkparams);

            return ['num' => $checkparams, 'assoc' => $assoc];
        } else {
            return ['num' => [], 'assoc' => []];
        }
    }
}
