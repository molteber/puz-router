<?php
spl_autoload_register(function($name){
    if (!defined('DS')) {
        define('DS', DIRECTORY_SEPARATOR);
    }
    // Please change this according to your file structure
    $pathtoautoclass = dirname(__FILE__).DS."class";
    $class = $pathtoautoclass. DS .str_replace("\\", DS, $name);
    $file = $class.".php";

    if (is_file($file)) {
        require_once $file;
    }
});
