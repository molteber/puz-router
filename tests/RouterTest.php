<?php
use Puz\Router\Router;
use Puz\Router\Route;

use Puz\Router\Exceptions\IncorrectRouterCreationException;
use Puz\Router\Exceptions\InvalidRouterNameException;
use Puz\Router\Exceptions\RouternameAlreadyExistsException;
class RouterTest extends PHPUnit_Framework_TestCase
{
    /*
    public function setup() {
        $this->global = new Router(true);
        $this->custom = new Router("custom");
        $this->hello = new Router("hello");
    }*/
/*
    public function testStaticCreation()
    {
        try {
            $router = Router::create();
        } catch(IncorrectRouterCreationException $e) {
            $router = false;
        }
        $this->assertFalse($router, "Routers should set a name for the router collection or give a boolean value 'true' for global scope.");

        try {
            $router = Router::create("");
        } catch(InvalidRouterNameException $e) {
            $router = false;
        }
        $this->assertFalse($router, "Routers should have a VALID name, /([a-z])([a-z0-9_-]+)/i or be set as 'true' for global scope. Note. 'global' is a reserved router name.");

        try {
            $router = Router::create("global");
        } catch(InvalidRouterNameException $e) {
            $router = false;
        }
        $this->assertFalse($router, "'global' is a reserved router name.");

        $router = Router::create(true);
        $this->assertNotNull($router, "Router should have been created in the global scope, not fail...");

        $this->assertEquals($router->getScope(), "global");

        $router = Router::create("custom");
        $this->assertEquals($router->getScope(), "custom");

        try {
            $router = Router::create("custom");
        } catch (RouternameAlreadyExistsException $e) {
            $router = false;
        }
        $this->assertFalse($router, "The router should not create duplicates of same scope name.");
    }*/

    public function testDirectCreation()
    {
        try {
            $router = new Router();
        } catch(IncorrectRouterCreationException $e) {
            $router = false;
        }
        $this->assertFalse($router, "Routers should set a name for the router colletion or give a boolean value 'true' for global scope.");

        try {
            $router = new Router("");
        } catch (InvalidRouterNameException $e) {
            $router = false;
        }
        $this->assertFalse($router, "Routers should have a VALID name, /([a-z])([a-z0-9_-]+)/i or be set as 'true' for global scope. Note: 'global' is a reserved router name.");

        try {
            $router = new Router("global");
        } catch (InvalidRouterNameException $e) {
            $router = false;
        }
        $this->assertFalse($router, "'global' is a reserved router name.");

        $router = Router::getInstance("global");
        $this->assertEquals($router->getScope(), "global", "Scope should be returned as 'global'");

        $router = new Router("hello-world");
        $this->assertEquals($router->getScope(), "hello-world");

        try {
            $router = new Router("hello-world");
        } catch (RouternameAlreadyExistsException $e) {
            $router = false;
        }
        $this->assertFalse($router, "The router should not create duplicates of same scope name.");
    }

    public function testBuiltinRouteCreation()
    {
        try {
            $this->global = new Router(true);
        } catch (RouterNameAlreadyExistsException $e) {
            $this->global = Router::getInstance('global');
        }
        $this->custom = new Router("custom");
        $this->hello = new Router("hello");

        $this->global->add("GET", "/", "callback");
        $this->global->get("/1", "callback");
        $this->global->post("/2", "callback");
        $this->global->put("/3", "callback");
        $this->global->delete("/4", "callback");

        $this->custom->add("POST", "/", "callback");
        $this->custom->get("/1", "callback");
        $this->custom->post("/2", "callback");
        $this->custom->put("/3", "callback");
        $this->custom->delete("/4", "callback");

        $this->hello->add("PUT", "/", "callback");
        $this->hello->get("/1", "callback");
        $this->hello->post("/2", "callback");
        $this->hello->put("/3", "callback");
        $this->hello->delete("/4", "callback");

        // Get the scopes
        $scopes = Router::getScopes();
        $this->assertContains("global", $scopes);
        $this->assertContains("custom", $scopes, null, true);
        $this->assertContains("hello-world", $scopes, null, true);

        // Check the global scope routes
        $global = Router::getInstance("global");
        $routes = $global->getRoutes();
        $this->assertNotEmpty($routes, "The global route scope should have some routes by now.");

        // Check that they are all a instance of Route
        foreach($routes as $route) {
            $this->assertInstanceOf('Puz\Router\Route', $route);
        }

        $one = $routes[0];
        $this->assertEquals($one->getUrl(), "/");
        $this->assertEquals($one->getMethod(), "get");
        $this->assertTrue($one->hasCallback());

        $two = $routes[1];
        $this->assertEquals("/1", $two->getUrl());
        $this->assertEquals($two->getMethod(), "get");
        $this->assertTrue($two->hasCallback());

        $three = $routes[2];
        $this->assertEquals($three->getUrl(), "/2");
        $this->assertEquals($three->getMethod(), "post");
        $this->assertTrue($three->hasCallback());

        $four = $routes[3];
        $this->assertEquals($four->getUrl(), "/3");
        $this->assertEquals($four->getMethod(), "put");
        $this->assertTrue($four->hasCallback());

        $five = $routes[4];
        $this->assertEquals($five->getUrl(), "/4");
        $this->assertEquals($five->getMethod(), "delete");
        $this->assertTrue($five->hasCallback());

        // Check the 'custom' scope routes
        $custom = Router::getInstance("custom");
        $routes = $custom->getRoutes();
        $this->assertNotEmpty($routes, "The 'custom' route scope should have some routes by now.");

        // Check that they are all a instance of Route
        foreach($routes as $route) {
            $this->assertInstanceOf('Puz\Router\Route', $route);
        }

        $one = $routes[0];
        $this->assertEquals($one->getUrl(), "/");
        $this->assertEquals($one->getMethod(), "post");
        $this->assertTrue($one->hasCallback());

        $two = $routes[1];
        $this->assertEquals($two->getUrl(), "/1");
        $this->assertEquals($two->getMethod(), "get");
        $this->assertTrue($two->hasCallback());

        $three = $routes[2];
        $this->assertEquals($three->getUrl(), "/2");
        $this->assertEquals($three->getMethod(), "post");
        $this->assertTrue($three->hasCallback());

        $four = $routes[3];
        $this->assertEquals($four->getUrl(), "/3");
        $this->assertEquals($four->getMethod(), "put");
        $this->assertTrue($four->hasCallback());

        $five = $routes[4];
        $this->assertEquals($five->getUrl(), "/4");
        $this->assertEquals($five->getMethod(), "delete");
        $this->assertTrue($five->hasCallback());

        // Check the 'hello-world' scope routes
        $hello = Router::getInstance("hello");
        $routes = $hello->getRoutes();
        $this->assertNotEmpty($routes, "The 'hello-world' route scope should have some routes by now.");

        // Check that they are all a instance of Route
        foreach($routes as $route) {
            $this->assertInstanceOf('Puz\Router\Route', $route);
        }

        $one = $routes[0];
        $this->assertEquals($one->getUrl(), "/");
        $this->assertEquals($one->getMethod(), "put");
        $this->assertTrue($one->hasCallback());

        $two = $routes[1];
        $this->assertEquals($two->getUrl(), "/1");
        $this->assertEquals($two->getMethod(), "get");
        $this->assertTrue($two->hasCallback());

        $three = $routes[2];
        $this->assertEquals($three->getUrl(), "/2");
        $this->assertEquals($three->getMethod(), "post");
        $this->assertTrue($three->hasCallback());

        $four = $routes[3];
        $this->assertEquals($four->getUrl(), "/3");
        $this->assertEquals($four->getMethod(), "put");
        $this->assertTrue($four->hasCallback());

        $five = $routes[4];
        $this->assertEquals($five->getUrl(), "/4");
        $this->assertEquals($five->getMethod(), "delete");
        $this->assertTrue($five->hasCallback());
    }

}
