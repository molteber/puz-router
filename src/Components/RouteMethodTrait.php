<?php

namespace Puz\Router\Components;

use Puz\Router\Exceptions\InvalidRouteMethodException;
use Puz\Router\Exceptions\RouteMethodDoesNotExist;

trait RouteMethodTrait
{
    protected $methods = [];

    protected $availableMethods = [
        'get',
        'head',
        'post',
        'put',
        'patch',
        'delete'
    ];

    /**
     * @param string|null   $url
     * @param callable|null $callback
     *
     * @return $this
     */
    public function get($url = null, $callback = null)
    {
        return $this->setMethod(["get", "head"], $url, $callback);
    }

    /**
     * @param string|null   $url
     * @param callable|null $callback
     *
     * @return $this
     */
    public function head($url = null, $callback = null)
    {
        return $this->setMethod("head", $url, $callback);
    }

    /**
     * @param string|null   $url
     * @param callable|null $callback
     *
     * @return $this
     */
    public function post($url = null, $callback = null)
    {
        return $this->setMethod("post", $url, $callback);
    }

    /**
     * @param string|null   $url
     * @param callable|null $callback
     *
     * @return $this
     */
    public function put($url = null, $callback = null)
    {
        return $this->setMethod("put", $url, $callback);
    }

    /**
     * @param string|null   $url
     * @param callable|null $callback
     *
     * @return $this
     */
    public function patch($url = null, $callback = null)
    {
        return $this->setMethod("patch", $url, $callback);
    }

    /**
     * @param string|null   $url
     * @param callable|null $callback
     *
     * @return $this
     */
    public function delete($url = null, $callback = null)
    {
        return $this->setMethod("delete", $url, $callback);
    }

    /**
     * @param string|array $methods
     *
     * @return $this
     */
    public function method($methods)
    {
        $methods = is_array($methods) ? $methods : func_get_args();

        $this->validateMethod($methods);

        $this->methods = $methods;

        return $this;
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param string|array  $method
     * @param string|null   $url
     * @param callable|null $callback
     *
     * @return $this
     *
     * @throws \Puz\Router\Exceptions\RouteMethodDoesNotExist
     */
    protected function setMethod($method, $url, $callback)
    {
        $methods = is_array($method) ? $method : [$method];

        $this->validateMethod($methods);

        $this->methods = $methods;

        if ($url) {
            if (method_exists($this, 'url')) {
                call_user_func_array([$this, 'url'], [$url]);
            } else {
                throw new RouteMethodDoesNotExist('This Route object does not contain the url component yet. You can find the default one in ' . RouteUrlTrait::class);
            }
        }

        if ($callback) {
            if (method_exists($this, 'callback')) {
                call_user_func_array([$this, 'callback'], [$callback]);
            } else {
                throw new RouteMethodDoesNotExist('This Route object does not contain the callback component yet. You can find the default one in ' . RouteCallbackTrait::class);
            }
        }

        return $this;
    }

    /**
     * @param string|array $methods
     *
     * @throws \Puz\Router\Exceptions\InvalidRouteMethodException
     */
    protected function validateMethod($methods)
    {
        $methods = is_array($methods) ? $methods : [$methods];

        $invalidMethods = array_filter($methods, function ($method) {
            return !in_array(strtolower($method), $this->availableMethods);
        });

        if ($invalidMethods) {
            throw new InvalidRouteMethodException("Tried to apply invalid method to the route: " . implode(", ",
                    $invalidMethods));
        }
    }
}
