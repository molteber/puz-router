<?php
namespace Puz\Router;

use Puz\Router\Exceptions\InvalidRouterNameException;
use Puz\Router\Exceptions\InvalidRouteCreationException;

class Router
{
    /**
     *  @var array
     */
    protected static $allowedMethods = ['get','post','put','delete'];

    /**
     *  @var string
     */
    protected static $parameterCharacter = ":";

    /**
     *  @var array
     */
    protected static $scopes = ['global' => null];
    protected $data = [];
    protected $routes = [];

    public function __construct($scope = null)
    {
        if (is_string($scope) || (is_bool($scope) && $scope === true)) {
            if (is_string($scope)) {
                // First, validate the name.
                if (empty($scope) || !preg_match("/^([a-z])([a-z0-9_-]+)$/i", $scope)) {
                    throw new InvalidRouterNameException("The router name cannot be empty and must be validate against /^([a-z])([a-z0-9_-]+)$/i");
                }
                $scope = strtolower($scope);
                // Create the scope. If it exists, throw error
                if (in_array($scope, self::$scopes)) {
                    throw new RouterNameAlreadyExistsException("The router name '{$scope}' has already been created. You should fetch it by using Router::('{$scope}')");
                }

                self::$scopes[$scope] = $this;
            } elseif (is_bool($scope) && $scope === true) {
                if (!is_null(self::$scopes['global'])) {
                    throw new RouterNameAlreadyExistsException("The global router has already been created. You should fetch it by using Router::get('global')");
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
        $scope = self::getInstance('global');

        if ($method == "add" || in_array($method, self::$allowedMethods)) {
            call_user_func_array([$scope, $method], $arguments);
            // if ($method == "add") {
            //     if (isset($arguments[0]) && is_object($arguments[0]) && get_class($arguments[0]) == Route::class) {
            //         $route = $arguments[0];
            //     } else {
            //         call_user_func_array([$scope, ], $arguments);
            //     }
            // } else {
            //     call_user_func_array([$scope, $method])
            //     if ($args !== 2) {
            //         throw new InvalidRouteCreationException("Creating a route directly from Router with the method already given, you need to give the Url and Callback as arguments");
            //     }
            //     $route = $class->newInstanceArgs(array_merge([$method], $arguments));
            // }
            //
            // $this->routes[] = $route;
        }
    }

    public function __call($method, $arguments)
    {
        if ($method == "add" || in_array($method, self::$allowedMethods)) {
            $class = new \ReflectionClass(Route::class);
            $arguments = array_filter($arguments);
            $args = count($arguments);
            if ($method == "add") {
                if (isset($arguments[0]) && is_object($arguments[0]) && get_class($arguments[0]) == Route::class) {
                    $route = $arguments[0];
                } else {
                    if ($args !== 3) {
                        throw new InvalidRouteCreationException("Creating a route directly from the Router class depends on 3 arguments. Method, Url and callback");
                    }
                    $route = $class->newInstanceArgs($arguments);
                }
            } else {
                if ($args !== 2) {
                    throw new InvalidRouteCreationException("Creating a route directly from Router with the method already given, you need to give the Url and Callback as arguments");
                }
                $route = $class->newInstanceArgs(array_merge([$method], $arguments));
            }

            $this->routes[] = $route;
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
        return self::$allowedMethods;
    }

    /**
     *  @TODO
     */
    public static function getParameterCharacter()
    {
        return self::$parameterCharacter;
    }
}
