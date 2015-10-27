<?php
require "vendor/autoload.php";

use Puz\Router\Router;
use Puz\Router\Route;
use Puz\Router\Exceptions\NoMatchFoundException;

// This tries to autoconfigure the basepath, uri and requested method
Router::autoconfig();
// If it would fail or you want to change it manually, you can use Router::config()
// Router::config(['option' => 'value', 'otherOption' => 'value']);

$api1 = new Router("v1");
$api2 = new Router("v2");

// When returning in the callback function, it will generate a json response if array or object
$api2->add("get", "/", function() {
    $class = new stdClass;
    $class->id = 1;
    return $class;
});
// Because this there already exists a route on "/" with the GET method,
// The route below won't trigger on that request. But it will trigger at POST, PUT and DELETE
$api2->add(['get','post', 'put','delete'], "/", function() {
    return ['message' => 'This is the homepage of v2'];
});
$api2->add(['get'], "/onlyv2", function() {
    return ['message' => 'I only exist on v2'];
});

$api1->add(['get','post', 'put','delete'], "/", function(){
    return ['message' => 'This is the homepage of v1'];
});
$api1->add(['get'], '/onlyv1', function() {
    return ['message' => 'I only exist on v1'];
});



// Get the global instance
try {
    // You can call multiple routes at once, or just one if you prefer (Router::run("v2")) or $routerobject->run();
    Router::run("v2", "v1");
} catch(NoMatchFoundException $e) {
    echo "404 - Page not found";
    echo $e->getUrl();
}
