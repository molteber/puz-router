<?php

namespace Puz\Router;

use Puz\Router\Contracts\ValidatorContract;
use Puz\Router\Exceptions\RouteNotFoundException;

/**
 * @method Route get(string|null $url = null, callable|null $callback = null)
 * @method Route head(string|null $url = null, callable|null $callback = null)
 * @method Route post(string|null $url = null, callable|null $callback = null)
 * @method Route put(string|null $url = null, callable|null $callback = null)
 * @method Route patch(string|null $url = null, callable|null $callback = null)
 * @method Route delete(string|null $url = null, callable|null $callback = null)
 * @method Route method(string|array $methods)
 */
class Router
{
    /** @var \Puz\Router\Route[] */
    protected $routes = [];

    /** @var string[] */
    protected $validators = [];

    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param string $validator
     */
    public function registerValidator($validator)
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

        $this->validators[] = $validator;
    }

    /**
     * @param string $method
     * @param string $url
     *
     * @return mixed
     *
     * @throws \Puz\Router\Exceptions\RouteNotFoundException
     */
    public function run($method, $url)
    {
        // Initialise all validators
        $validators = array_map(function ($validator) {
            return new $validator;
        }, $this->validators);

        $data = [
            'method' => $method,
            'url' => $url
        ];

        foreach ($this->routes as $route) {

            /** @var \Puz\Router\Contracts\ValidatorContract $validator */
            foreach ($validators as $validator) {
                if ($validator->handle($route, $data) !== true)
                    continue 2;
            }

            return $route->call();
        }

        throw new RouteNotFoundException("No route found on given url: " . $url);
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
