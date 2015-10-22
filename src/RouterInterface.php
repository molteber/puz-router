<?php
namespace Puz\Router;

interface RouterInterface
{
    public static function make($method, $uri, $callback);
    public function where($key, $regex);
    public function name($alias = null);

    public function getName();
    public function getParams();
    public function getRegex($param);
    public function getUri($params = null);
    public function getMethod();

    public static function run($debugUri = null, $method = null);
}
