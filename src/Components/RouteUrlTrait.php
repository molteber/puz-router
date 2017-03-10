<?php

namespace Puz\Router\Components;

use Puz\Router\Exceptions\RouteMethodDoesNotExist;

/**
 * @property string url
 * @property array  params
 */
trait RouteUrlTrait
{
    protected static $parameterRegex = "([a-zA-Z0-9-._]+)";

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

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function getRegexUrl()
    {
        $params = array_map(function ($param) {
            return self::$parameterCharacter . $param['param'];
        }, $this->params);

        $regex = array_map(function ($param) {
            return $param['regex'];
        }, $this->params);

        $paramAsRegex = array_combine($params, $regex);

        $charQuote = preg_quote(self::$parameterCharacter, "/");
        $url = preg_quote($this->url, "/");

        // If the character was escaped, unescape it again
        $url = strtr($url, [$charQuote => self::$parameterCharacter]);

        return strtr($url, $paramAsRegex);
    }

    /**
     * @param string $url
     *
     * @return array
     */
    public function getUrlParameterData($url)
    {
        $data = [];

        if (!empty($this->params)) {
            $url = rtrim($url, "/");
            $routeRegexUrl = $this->getRegexUrl();
            if (preg_match("/^" . $routeRegexUrl . "$/", $url, $data)) {
                array_splice($data, 0,1);
            }
        }
        return $data;
    }

    protected function scanForParameters()
    {
        if (preg_match_all("/(" . preg_quote(self::$parameterCharacter) . "([a-z]+))/", $this->url, $matches)) {

            $params = array_map(function ($param) {
                return [
                    'param' => $param,
                    'required' => true,
                    'regex' => self::$parameterRegex
                ];
            }, $matches[2]);
            $this->params = $params;
        } else {
            $this->params = [];
        }
    }
}
