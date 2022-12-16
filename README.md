Rammewerk ErrorHandler
======================

A simple error handler for PHP. A great starting point to handle errors in your PHP project.

* PHP errors are turned into exceptions by default.
* You can register multiple closures to handle your errors.
* No other dependencies - very small size - 1 file only.

Getting Started
---------------

```
$ composer require rammewerk/error-handler
```

```php
use Rammewerk\Component\ErrorHandler;

$errorHandling = new ErrorHandler();

$errorHandling->log( static function (\Throwable $e) {
    # Log your exceptions
});

$errorHandling->report( static function (\Throwable $e) {
    # Show the exception to your user.
});
```

_There are basically no difference between the `log` and `report`. The log closures will be called first, then the
reports. So you can add different log handlers at later point in script, even though the report closures might be used
to exit PHP._

Why use Rammewerk ErrorHandler?
---------------
PHP will either throw exceptions - which are intended to be caught - or print Errors which are generally unrecoverable.
But instead of having to deal with both exceptions and errors, we convert errors so you only havel to deal
with exceptions. No more `@file_get_contents` just nice and neat try/catch.

Uncaught exceptions will be given to your `log` and `report` closures.

Many other frameworks or libraries are way more advanced, and comes with loads of functions. This ErrorHandler is simple
by design - you decide how to handle your uncaught exceptions and errors.

Tips
---------------

* Add the ErrorHandler class as early in your application as possible.
* Add `log` and `report` closure at any point you want. The `ErrorHandler` will only call these closures if the
  exception occurred after the registered closure. So, consider adding a `log` closure as soon as possible.
* You can add multiple closures. `log` closures will be called before `report` closures.
* If you want to reset previous registered closures - set the second argument as true in `log` or `report`.
  Example: `$error->log( $closure, true )`. This can be useful if you want to replace logging after calling other
  dependencies at a later point in your scripts.

Example
---------------

```php
use Rammewerk\Component\ErrorHandler;

CONST DEBUG_MODE = true;

$errorHandling = new ErrorHandler();

/** Log latest exception to JSON file */
$errorHandling->log( static function (\Throwable $e) {
  file_put_contents( 'latest_error.json', json_encode($e->getMessage()) );
});

/** Show default 500 error page */
$errorHandling->report( static function () {
    http_response_code( 500 );
    echo file_get_contents( "errors/500.html" );
    die;
} );

/** Show error details if DEBUG_MODE */
if( DEBUG_MODE ) { 
  # Notice that the second argument is set to true - to remove previously registered report closures.
  $errorHandling->report( function (\Throwable $e) {
      $debug = new \MyCustomDebugClass();
      $debug->exception($e);
      die;
  }, true );
}
```
