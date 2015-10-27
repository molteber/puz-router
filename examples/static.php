<?php
require "../vendor/autoload.php";

use Puz\Router\Router;
use Puz\Router\Route;
use Puz\Router\Exceptions\NoMatchFoundException;

// This tries to autoconfigure the basepath, uri and requested method
Router::autoconfig();

// If it would fail or you want to change it manually, you can use Router::config()
// Router::config(['option' => 'value', 'otherOption' => 'value']);
Router::add("GET", "/", function() {
    echo "Velkommen til dette API'et";
});

Router::add("GET", "/json", function($querystring) {
    return $querystring;
});

Router::add(["get", "post"], "/posting", function() {
    echo '<form method="post"><input type="text" name="ok"><button>Test</button></form>';
    var_dump($_POST);
});
Router::add("POST", "/", function($qs){
    return [$_SERVER];
});

Router::add("PUT", "/", function() {
    return ['method' => 'put'];
});

Router::add("delete", "/", function() {
    return ['method' => 'delete'];
});

// Get the global instance
try {
    Router::run("global", ['method' => "post"]);
} catch(NoMatchFoundException $e) {
    echo "404 - Page not found";
    echo $e->getUrl();
}
