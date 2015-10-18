<?php
namespace Puz\Router;

class Router
{

    /**
     *  @TODO
     */
    public static function getAllowedMethods()
    {
        return ['get','post','put','delete'];
    }

    /**
     *  @TODO
     */
    public static function getParameterCharacter()
    {
        return ":";
    }
}
