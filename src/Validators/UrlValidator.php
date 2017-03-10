<?php

namespace Puz\Router\Validators;

use Puz\Router\Contracts\ValidatorContract;
use Puz\Router\Route;

class UrlValidator implements ValidatorContract
{
    /**
     * @param \Puz\Router\Route $route
     * @param array             $data
     *
     * @return bool
     */
    public function handle(Route $route, array $data)
    {
        $routeUrl = rtrim($route->getUrl(), "/");
        $url = rtrim($data['url'], "/");

        // See if exact match
        if ($routeUrl == $url)
            return true;

        // See if regex match
        $routeRegexUrl = $route->getRegexUrl();
        dump($routeRegexUrl);
        if (preg_match("/^".$routeRegexUrl."$/", $url))
            return true;

        return;
    }
}
