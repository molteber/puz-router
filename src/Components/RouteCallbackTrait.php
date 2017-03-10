<?php

namespace Puz\Router\Components;

use Puz\Router\Exceptions\InvalidRouteCallbackException;
use Puz\Router\Exceptions\RouteCallbackImplementationMissingException;

trait RouteCallbackTrait
{
    protected $callback;

    protected $typeOfCallback;

    /**
     * @param callable $callback
     */
    public function callback($callback)
    {
        $this->typeOfCallback = $this->validateCallback($callback);

        $this->callback = $callback;
    }

    /**
     * @param array ...$args
     *
     * @return mixed
     */
    public function call(...$args)
    {
        if (!$this->typeOfCallback || !$this->callback) {
            throw new InvalidRouteCallbackException("Callback is not set");
        }

        return $this->performCall($this->typeOfCallback, $this->callback, $args);
    }

    /**
     * @param callable $callback
     *
     * @return string
     *
     * @throws \Puz\Router\Exceptions\InvalidRouteCallbackException
     */
    protected function validateCallback($callback)
    {
        if (is_callable($callback)) {
            return "callable";
        } elseif (is_string($callback)) {
            return "string";
        }

        throw new InvalidRouteCallbackException("Given callback (of type " . gettype($callback) . ") cannot be called by this trait (" . RouteCallbackTrait::class . ").");
    }

    protected function performCall($type, $callback, array $arguments = [])
    {
        $methodName = "performCall" . ucfirst($type);
        if (method_exists($this, $methodName)) {
            return call_user_func_array([$this, $methodName], [$callback, $arguments]);
        } else {
            throw new RouteCallbackImplementationMissingException("This route object does not have way to handle \"{$type}\" callbacks.");
        }
    }

    protected function performCallCallable(callable $callback, array $arguments)
    {
        return call_user_func_array($callback, $arguments);
    }

    protected function performCallString($callback)
    {
        echo $callback;
    }
}
