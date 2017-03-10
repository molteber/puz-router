<?php

namespace Puz\Router\Contracts;

use Puz\Router\Route;

interface ValidatorContract
{
    /**
     * @param \Puz\Router\Route $route
     * @param array             $data
     *
     * @return bool
     */
    public function handle(Route $route, array $data);
}
