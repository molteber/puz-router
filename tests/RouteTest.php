<?php
use Puz\Router\Route;
use Puz\Router\Exceptions\InvalidRouteCreationException;

class RouteTest extends PHPUnit_Framework_TestCase
{

    public function testCreatingRouteStaticlyWithoutParameters()
    {
        try {
            $get = true;
            Route::get();
        } catch (InvalidRouteCreationException $e) {
            $get = false;
        }

        try {
            $post = true;
            Route::post();
        } catch(InvalidRouteCreationException $e) {
            $post = false;
        }

        try {
            $put = true;
            Route::put();
        } catch(InvalidRouteCreationException $e) {
            $put = false;
        }

        try {
            $delete = true;
            Route::delete();
        } catch(InvalidRouteCreationException $e) {
            $delete = false;
        }

        $this->assertFalse($get, "Creating static GET route should only be legal with a URL as parameter");
        $this->assertFalse($post, "Creating static POST route should only be legal with a URL as parameter");
        $this->assertFalse($put, "Creating static PUT route should only be legal with a URL as parameter");
        $this->assertFalse($delete, "Creating static DELETE route should only be legal with a URL as parameter");
    }

    public function testCreatingRouteStaticlyWithParameters()
    {
        $get = Route::get("/");
        $post = Route::post("/1");
        $put = Route::put("/2");
        $delete = Route::delete("/3");

        // Check that each creating got the correct method set
        $this->assertEquals($get->getMethod(), "get");
        $this->assertEquals($post->getMethod(), "post");
        $this->assertEquals($put->getMethod(), "put");
        $this->assertEquals($delete->getMethod(), "delete");

        // We set the url to "/", let's see if it was stored as that
        $this->assertEquals($get->getUrl(), "/");
        $this->assertEquals($post->getUrl(), "/1");
        $this->assertEquals($put->getUrl(), "/2");
        $this->assertEquals($delete->getUrl(), "/3");

        // Also ensure that the callback has NOT been set
        $this->assertNull($get->getCallback());
        $this->assertNull($post->getCallback());
        $this->assertNull($put->getCallback());
        $this->assertNull($delete->getCallback());

        $getc = Route::get("/", function() {
            echo "Hello world";
        });
        $postc = Route::post("/1", function() {
            echo "Hello world 2";
        });
        $putc = Route::put("/2", function() {
            echo "Hello world 3";
        });
        $deletec = Route::delete("/3", function() {
            echo "Hello world 4";
        });
    }
}
