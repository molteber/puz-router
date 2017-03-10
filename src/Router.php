<?php

namespace Puz\Router;

use Puz\Router\Contracts\ValidatorContract;
use Puz\Router\Exceptions\RouteNotFoundException;
use Puz\Router\Validators\MethodValidator;
use Puz\Router\Validators\UrlValidator;

/**
 * @method Route get(string | null $url = null, callable | null $callback = null)
 * @method Route head(string | null $url = null, callable | null $callback = null)
 * @method Route post(string | null $url = null, callable | null $callback = null)
 * @method Route put(string | null $url = null, callable | null $callback = null)
 * @method Route patch(string | null $url = null, callable | null $callback = null)
 * @method Route delete(string | null $url = null, callable | null $callback = null)
 * @method Route method(string | array $methods)
 */
class Router
{
    /** @var string[] */
    protected static $validators = [
        MethodValidator::class,
        UrlValidator::class,
    ];

    /** @var \Puz\Router\Route[] */
    protected $routes = [];

    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param string $validator
     * @param bool   $reset If true, it will clear the list of validators before adding the new one.
     */
    public static function registerValidator($validator, $reset = false)
    {
        if (!is_string($validator)) {
            throw new \InvalidArgumentException("Expected type of string, got " . gettype($validator));
        }

        if (!class_exists($validator, true)) {
            throw new \InvalidArgumentException("Class {$validator} does not exist.");
        }

        if (!is_a($validator, ValidatorContract::class, true)) {
            throw new \InvalidArgumentException("Expected {$validator} to be a valid class name which implements " . ValidatorContract::class);
        }

        if ($reset === true) {
            self::$validators = [];
        }

        self::$validators[] = $validator;
    }

    /**
     * @param array $request
     *
     * @return void
     *
     * @throws \Puz\Router\Exceptions\RouteNotFoundException
     */
    public function run(array $request)
    {
        // Validate request dependencies
        if (!isset($request['method'], $request['url'])) {
            throw new \InvalidArgumentException("Missing critical information for router. We need the indexes 'method' and 'url'");
        }

        // Initialise all validators
        $validators = array_map(function ($validator) {
            return new $validator;
        }, self::$validators);

        $data = [
            'method' => $request['method'],
            'url' => rtrim($request['url'], "/"),
        ];

        foreach ($this->routes as $route) {

            /** @var \Puz\Router\Contracts\ValidatorContract $validator */
            foreach ($validators as $validator) {
                if ($validator->handle($route, $data) !== true) {
                    continue 2;
                }
            }

            $route->call(...$route->getUrlParameterData($data['url']));
            return;
        }

        throw new RouteNotFoundException("No route found on given url: " . $data['url']);
    }

    public function __call($method, $arguments)
    {
        $route = new Route($this);

        if (method_exists($route, $method)) {
            $this->routes[] = $route;
            return call_user_func_array([$route, $method], $arguments);
        } else {
            unset($route);
        }

        throw new \BadMethodCallException("Method {$method} does not exist");
    }
}
