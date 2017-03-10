# Puz Router
###### The most fluffiest PHP router you have ever used

## Installation
To use it with composer, simply type `composer require puz/router` or add the following to your `composer.json` file.

If you want to use the latest unreleased version, you need to require the dev version, like this:
```json
{
  "require": {
    "puz/router": "dev-master"
  },
  "minimum-stability": "dev",
  "prefer-stable": true
}
```

## How-To
### Create a router
Easy as:
```php
<?php
use \Puz\Router\Router;

$router = new Router;
```
The router is the object which contains different validation rules on how to validate the url. By default there are two validation rules added.
`\Puz\Router\Validations\MethodValidator` and `\Puz\Router\Validations\UrlValidator`. These will validate routes against the request method and the given url.  
You can modify this list like this:
```php
<?php
// ...
// Adding a new one
Router::registerValidator(MyValidatorClass::class);

// Replacing the list and then add a new one
Router::registerValidator(MyValidatorClass::class, true);
```

### Create a route
This is done through the Router object. Per now i have added six different request methods. `GET`, `HEAD`, `POST`, `PUT`, `PATCH`, `DELETE`. To create a route with one of these methods:
```php
<?php
// ...
$router = new Router;
$router->get("/optional-url", optionalCallback())->...;
$router->head("/optional-url", optionalCallback())->...;
$router->post("/optional-url", optionalCallback())->...;
$router->put("/optional-url", optionalCallback())->...;
$router->patch("/optional-url", optionalCallback())->...;
$router->delete("/optional-url", optionalCallback())->...;
```
If you chain two request method methods, the last one will overwrite the previous. To allow multiple request methods you have to use the `method` method.
```php
<?php
// ...
$router->method("get", "post")->...
$router->method(["get", "post"])->...
```

To provide the url you can (if you did not specify it in the method methods) use the `url` method.
```php
<?php
// ...
$route = $router->method("get")->url("/hello-world", optionalCallback())->...
```

To provide the callback you can (if you did not specify it in the method methods or the url method) use the `callback` method.
```php
<?php
// ...
$router->post("/support")->callback(function() {
    // Send email to support
});

// You can create parameters by using ":" followed by letters a-z
$router->get("/hello-:name", function ($name) {
    echo "Hello, $name";
});
```
For now the router only accepts `callable` callbacks. (And pure strings that is just for echoing, ex: ->callback("ping") results in ping being echoed if it's not a valid function).

### Run the router
To run the router you have to end your application with `$router->run(...)`.
This router will not auto-detect your request method nor requested uri, so you have to fill in the blanks. Heres an easy example of how to do that.
```php
<?php
// ...
$router->run([
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
]);
```
This also opens up for a easy way to debug or do it just the way you want it!

_If you are using this router in a subfolder on your domain, you might have a bad time with the router uri if you're using this exact run example. You will need to filter away the subfolder from the request uri before giving it to the router. As for now i use this:_
```php
<?php
// ...
// Ugly code to remove the subfolders.
// Got a cleaner and more understandable way? Give me a ping
$url = explode("/".trim(str_replace($_SERVER['DOCUMENT_ROOT'], "", dirname($_SERVER['SCRIPT_FILENAME'])), "/"), $_SERVER['REQUEST_URI'], 2)[1];

$router->run([
    'method' => $_SERVER['REQUEST_METHOD'],
    'url' => $url
]);
```

## Examples
I will update the examples some other time, but not too far in the future. I hope this readme will provide enough information on how you can use the router.

## Planned changes
[ ] Adding response converters. If your callback returns data, these converters will convert your response to ex. json output and such.

**Got some ideas? Please give me a pling in the issue section!**
