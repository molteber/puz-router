<?php

namespace Puz\Router\Components;

/**
 * Makes it possible to name the route
 *
 * @property string name
 */
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
