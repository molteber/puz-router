<?php
namespace Puz\Router\Exceptions;
use Exception;

class NoMatchFoundException extends Exception
{
    protected $url;

    public function __construct($msg, $url = null)
    {
        parent::__construct($msg);
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }
}
