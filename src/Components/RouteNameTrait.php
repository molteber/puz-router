<?php

namespace Puz\Router\Components;

trait RouteNameTrait
{
    /** @var  string */
    protected $name;

    /**
     * Set a name for the route
     *
     * @param string $name
     *
     * @return $this
     */
    public function name($name)
    {
        $this->name = $name;

        return $this;
    }
}
