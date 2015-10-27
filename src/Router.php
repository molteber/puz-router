<?php
namespace Puz\Router;

use Puz\Router\Exceptions\InvalidRouterNameException;
use Puz\Router\Exceptions\IncorrectRouterCreationException;
use Puz\Router\Exceptions\MethodSourceNotFoundException;
use Puz\Router\Exceptions\UrlSourceNotFoundException;
use Puz\Router\Exceptions\BasepathNotFoundException;
use Puz\Router\Exceptions\NoMatchFoundException;
use Puz\Router\Exceptions\RouterNameAlreadyExistsException;

class Router
{
    /**
     *  @var array
     */
    protected static $scopes = ['global' => null];
    protected $data = [];
    protected $routes = [];

    protected static $config = [
        'basepath' => null,
        'url' => null,
        'method' => null,
        'allowedMethods' => ['get','post','put','delete'],
        'parameterCharacter' => ':',
    ];

    public static function config(array $opts)
    {
        foreach($opts as $key => $value) {
            if (array_key_exists($key, self::$config)) {
                self::$config[$key] = $value;
            }
        }
    }

    public static function getScopes()
    {
        return array_keys(self::$scopes);
    }

    public function getScope()
    {
        return $this->data['scope'];
    }

    public function __construct($scope = null)
    {
        if (is_string($scope) || (is_bool($scope) && $scope === true)) {
            if (is_string($scope)) {
                // First, validate the name.
                if (empty($scope) || !preg_match("/^([a-z])([a-z0-9_-]+)$/i", $scope)) {
                    throw new InvalidRouterNameException("The router name cannot be empty and must be validate against /^([a-z])([a-z0-9_-]+)$/i");
                }
                $scope = strtolower($scope);

                if ($scope == "global") {
                    throw new InvalidRouterNameException("'global' is a reserved scope name.");
                }
                // Create the scope. If it exists, throw error
                elseif (array_key_exists($scope, self::$scopes)) {
                    throw new RouterNameAlreadyExistsException("The router name '{$scope}' has already been created. You should fetch it by using Router::('{$scope}')");
                }

                self::$scopes[$scope] = $this;
            } elseif (is_bool($scope) && $scope === true) {
                if (!is_null(self::$scopes['global'])) {
                    throw new RouterNameAlreadyExistsException("The global router has already been created. You should fetch it by using Router::getInstance('global')");
                } else {
                    self::$scopes['global'] = $this;
                    $scope = "global";
                }
            } else {
                throw new InvalidRouterNameException("The router needs to get a name or be set to global.\nSo either give it a name starting with a-z, while the rest can contain a-z0-9_-. If you want to set it to global, give it the boolean value true instead.");
            }
        } else {
            throw new IncorrectRouterCreationException("Please provide a string or boolean argument. String is name, boolean value true is global scope.");
        }
        $this->scope = $scope;

        return $this;
    }

    protected function addRoute(Route $route)
    {

    }

    public static function getInstance($scope)
    {
        if ($scope == "global" && self::$scopes['global'] === null) {
            return new Router(true);
        } else {
            if (isset(self::$scopes[$scope])) {
                return self::$scopes[$scope];
            } else {
                return null;
            }
        }
    }

    public static function __callStatic($method, $arguments) {

        if ($method == "add" || in_array($method, self::$config['allowedMethods'])) {
            $scope = self::getInstance('global');
            return call_user_func_array([$scope, $method], $arguments);
        } elseif (method_exists(__CLASS__, "static".ucfirst($method))) {
            return forward_static_call_array([__CLASS__, "static".ucfirst($method)], $arguments);
        }
    }

    public function __call($method, $arguments)
    {
        if ($method == "add" || in_array($method, self::$config['allowedMethods'])) {
            $arguments = array_filter($arguments);
            $args = count($arguments);
            if ($method == "add") {
                if (isset($arguments[0]) && is_object($arguments[0]) && get_class($arguments[0]) == Route::class) {
                    $route = $arguments[0];
                } else {
                    $class = new \ReflectionClass(Route::class);
                    $route = $class->newInstanceArgs($arguments);
                }
            } else {
                $class = new \ReflectionClass(Route::class);
                $route = $class->newInstanceArgs(array_merge([$method], $arguments));
            }
            $this->routes[] = $route;
            return $route;
        } elseif($method == "run") {
            call_user_func_array([$this, 'doRun'], $arguments);
        }
    }

    public function getRoutes()
    {
        return $this->routes;
    }
    public function __get($name)
    {
        return (isset($this->data[$name])? $this->data[$name] : null);
    }

    public function __set($name, $val)
    {
        $this->data[$name] = $val;
    }
    /**
     *  @TODO
     */
    public static function getAllowedMethods()
    {
        return self::$config['allowedMethods'];
    }

    /**
     *  @TODO
     */
    public static function getParameterCharacter()
    {
        return self::$config['parameterCharacter'];
    }

    protected function doRun($url = null, $method = null)
    {
        if (empty($url)) {
            if (empty(self::$config['url'])) {
                throw new UrlSourceNotFoundException("You have not provided a debug url or configurated the income url source. Please look up in the readme file for how to configure the router.");
            } else {
                $url = self::$config['url'];
            }
        }

        if (empty($method)) {
            if (empty(self::$config['method'])) {
                throw new MethodSourceNotFoundException("You have not provided a debug method or configurated the income method source. Please look up in the readme file for how to configure the router.");
            } else {
                $method = self::$config['method'];
            }
        }

        if (empty(self::$config['basepath'])) {
            throw new BasepathNotFoundException("You have not configured the basepath value. Please look up the readme file for how to configure the router.");
        }

        // Parse the string and kick out the unwanted stuff
        $parsed = parse_url($url);
        $url = "/".trim(preg_replace("#^".preg_quote(self::$config['basepath'], "#")."#", "", $parsed['path'], 1), "/");
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $querystring);
        } else {
            $querystring = [];
        }

        // Look for matches on the url
        foreach ($this->routes as $route) {
            if ($route->isValid()) {
                // See if we have a route call history for the specific route and method.
                $routehistoryindex = md5($route->getMethodAsString()."|".$route->getUrl());
                if (isset($route->history[$routehistoryindex])) {
                    $history = $route->history[$routehistoryindex];
                    $history['route']->call($history['params'], $querystring);

                    return;
                } elseif ($route->getUrl() == $url && $route->hasMethod($method) && !$route->hasParams()) {
                    // Perfect match!
                    // Call it and return
                    $route->call([], $querystring);

                    return;
                } elseif ($route->hasMethod($method)) {
                    $paramC = self::$config['parameterCharacter'];
                    $charquote = preg_quote($paramC, "/");
                    $urlregex = preg_quote($route->getUrl(), "/");

                    // If the character was escaped, unescape it again
                    $urlregex = strtr($urlregex, [$charquote => $paramC]);

                    // Get all regex params and replace them with a regex string
                    $params = $route->getParams();
                    $replaceRegex = [];
                    foreach ($params as $param) {
                        $replaceRegex[$paramC.$param] = $route->getRegex($param);
                    }
                    $urlregex = strtr($urlregex, $replaceRegex);

                    if (preg_match("/^".$urlregex."$/", $url, $matches)) {
                        array_splice($matches, 0,1);
                        $route->call($matches, $querystring);

                        return;
                    }
                }
            }
        }

        // If we reach this lonely place down here, well..
        // We did not find any matches.. :'''( Let's throw an error!
        // YEAAAH! That will show'em! MWohaha
        throw new NoMatchFoundException("Did not find any matches on the given route: ".strtoupper($method). " {$url}", $url);
    }

    protected static function staticRun($scopes = [], $debug = null)
    {
        $url = $method = null;

        if (!is_array($scopes)) {
            $scopes = func_get_args();
            $debug = array_pop($scopes);
            if (!is_array($debug)) {
                $scopes[] = $debug;
                $debug = null;
            }
        }

        if (is_array($debug)) {
            if (isset($debug['url'])) {
                $url = $debug['url'];
            }

            if (isset($debug['method'])) {
                $method = $debug['method'];
            }
        }

        if (empty($scopes)) {
            $scopes = ['global'];
        }
        $totalScopes = count($scopes);
        $count = 1;
        foreach ($scopes as $scope) {
            try {
                $router = self::getInstance($scope);
                if (!empty($router)) {
                    $router->run();
                    return;
                }
                // var_dump($scope, self::getInstance($scope));
                // var_dump("lol", self::getInstance($scope));
                // $router = static::getInstance($scope);
            } catch (NoMatchFoundException $e) {
                if ($count >= $totalScopes) {
                    throw $e;
                }
            } finally {
                $count++;
            }
        }
    }

    public static function autoconfig()
    {
        self::$config['defaultRegex'] = "([a-zA-Z0-9-._]+)";
        self::$config['method'] = isset($_SERVER, $_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
        self::$config['url'] = isset($_SERVER, $_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;

        // Find out the basepath
        if (isset($_SERVER, $_SERVER['DOCUMENT_ROOT'], $_SERVER['SCRIPT_FILENAME'])) {
            $scriptdir = dirname($_SERVER['SCRIPT_FILENAME']);
            self::$config['basepath'] = "/".trim(str_replace($_SERVER['DOCUMENT_ROOT'], "", $scriptdir), "/");
        }
    }
}
