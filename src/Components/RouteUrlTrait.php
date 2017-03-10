<?php

namespace Puz\Router\Components;

use Puz\Router\Exceptions\RouteMethodDoesNotExist;

/**
 * @property string url
 * @property array  params
 */
trait RouteUrlTrait
{
    /** @var string  */
    protected static $parameterCharacter = ":";

    /** @var string */
    protected $url;

    /** @var array */
    protected $params;

    public function url($url, $callback = null)
    {
        // Validate the url and scan for parameters
        $url = "/" . trim($url, "/");
        $this->url = $url;

        $this->scanForParameters();

        if ($callback) {
            if (method_exists($this, 'callback')) {
                call_user_func_array([$this, 'callback'], [$callback]);
            } else {
                throw new RouteMethodDoesNotExist('This Route object does not contain the callback component yet. You can find the default one in ' . RouteCallbackTrait::class);
            }
        }

        return $this;
    }

    protected function scanForParameters()
    {
        if (preg_match_all("/(" . preg_quote(self::$parameterCharacter) . "([a-z]+))/", $this->url, $matches)) {

            $params = array_map(function ($param) {
                return [
                    'param' => $param,
                    'required' => true
                ];
            }, $matches[2]);
            $this->params = $params;
        } else {
            $this->params = [];
        }
    }
}
