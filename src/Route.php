<?php

namespace Puz\Router;

use Puz\Router\Components\RouteCallbackTrait;
use Puz\Router\Components\RouteMethodTrait;
use Puz\Router\Components\RouteNameTrait;
use Puz\Router\Components\RouteUrlTrait;

class Route
{
    /** @var \Puz\Router\Router */
    protected $router;

    use RouteMethodTrait,
        RouteNameTrait,
        RouteCallbackTrait,
        RouteUrlTrait;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }
}
