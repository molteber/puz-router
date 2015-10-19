# Puz Router
###### The most fluffiest PHP router you have ever used

## Installation
You will need the following information in your `composer.json` file.

    {
        "repositories": [
            {
                "type": "git",
                "url": "http://celia.mij.no/puz/Router.git"
            }
        ],
        "require": {
            "puz/router": "dev-master"
        },
        "minimum-stability": "dev",
    }

When ready, run `composer install` and watch it being downloaded to your vendor folder.  

If your project is a git repository as well, please add `/vendor` to your `.gitignore` file so you won't be storing unwanted files and folders on your repository. If you need to have the vendor folder, which may be because you don't have access to run `composer install` on the production server, please read the note underneath if you run into problems.

**NOTE** Because this is currently a git repository, it will also download the `.git` folder. If it causes problems for you, I believe you can just go ahead and delete it.

## How-To
### Create a route
There are multiple ways to create a single route. **Note that a single route won't be automaticly called, only the Router does that.**
Here are some examples on how to create the route:

    <?php
    require "vendor/autoload.php";
    use Puz\Router\Route;

    $route = new Route(); // Accepts up to three arguments, $method, $url, $callback

    // Equivalent ways to set the method
    Route::get($url);
    Route::method("get");
    Route::method(['get','post']);
    $route->setMethod("get"); // $route->setMethod(['get','post']);
    $route->method = "get"; // $route->method = ['get', 'post'];

    // Equivalent ways to set the url
    // Note the last one with forward slash in the beginning and end.
    // It will trim away the slashes and add a forward slash at the beginning at any time
    Route::url("/hello-world");
    $route->setUrl("hello-world");
    $route->url = "hello-world";
    $route->setUrl("/hello-world/");

    // Equivalent ways to set the callback
    Route::callback(function(){ echo "Hello world"});
    $route->setCallback("Hello world"); // Hey what? A string? If not a callable argument, it will make a anonynomous function and echo out the string
    $route->callback = function(){ echo "Hello world"; };

    // If you want to see what the current values is, you may do following:
    echo $route->method;
    echo $route->getMethod();
    echo $route->url;
    echo $route->getUrl();
    // It is not recommended to call the callback directly
    call_user_func_array($route->callback, []);
    call_user_func_array($route->getCallback(), []);
    // Instead we would recommend you to use the `call` method.
    $route->call([]);

### Create a router
###### A collection of routes
A router is as the subheading says, a collection of routes.
The router can be used in two different ways. **Globally** or **separately**.

A **global route collection** means that every route you add to the global collection, will be available everywhere. You use this by using the static methods when adding new routes.  
Example: `Router::add("get", "/hello-world", "Hello world");`

A **separate route collection** means that you can restrict some routes based on which routes you want to be available. You use this by using the direct object when adding new routes. This can be usefull if you have versioning on your API (if you use this on your API routes).  
Example: `$router->add("get", /hello-world", "Hello world");`

If you want to make a separate route collection, you start of by creating a object:

    // Remember to include the autoloader
    use Puz\Router\Router;

    // Naming your new router is important so you can call it from eeeverywhere
    $router = new Router("v1");

    // Now you can start adding routes
    $router->get("/hello-world", "Hello world");
    $router->add(Route::get("/hello-world", function() { echo "Hello world"; }));

    // When you are done adding routes and setting up other stuff before running the application
    $router->run();
    // If you don't have direct access to the object, fear not, static call method is here to help
    Router::run("v1");

    // If you want to run through multiple route collections, but not all?? EASY!
    Router::run("v1", "optional-routes");
    Router::run(['v1', 'optional-routes']);
    // Yes, the order means something. If it doesn't find anything on the "v1" collection, it will go see if it find anything on "optional-routes"

    // But hey, what if the url won't match anything??
    // Fear not my friend. You can (**should**) surround your run call with a try catch.
    try {
        Router::run("v1");
    } catch(Puz\Router\Exceptions\NoMatchFoundException $e) {
        // Do stuff to show error page here
    } catch(\Exception $e) {
        // Unwanted stuff happened, should probably log this
    }
