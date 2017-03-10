<?php

namespace Puz\Router\Validators;

use Puz\Router\Contracts\ValidatorContract;
use Puz\Router\Route;

class MethodValidator implements ValidatorContract
{

    /**
     * @param \Puz\Router\Route $route
     * @param array             $data
     *
     * @return bool
     */
    public function handle(Route $route, array $data)
    {
        $method = strtolower($data['method']);
        $routeMethods = $route->getMethods();

        return in_array($method, $routeMethods);
    }
}
