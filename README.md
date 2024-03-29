Rammewerk ErrorHandler
======================

A simple and flexible way to handle and manage errors and exceptions in your PHP application.

* PHP errors are turned into exceptions by default.
* You can register multiple closures to handle your errors.
* No other dependencies - small size - 1 file only.

Installation
---------------

```
$ composer require rammewerk/error-handler
```

Usage
---------------
The error handler should be registered early in your application to ensure
they capture any issues that might occur from the outset. If registered late,
initial errors might go uncaught. Early registration also promotes structured
error management, facilitating easier debugging and maintenance.

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

_There are basically no difference between the `log` and `report`. Log closures will be called first, then the
reports. So you can add different `log` handlers at later point in script, even though an earlier registered `report`
closure might be used to exit PHP._

`log` closures are called in the order they got registered. `reports` are called the in reverse order. So you can early
in your application add a general message and then later on add new reports to handle different scenarios the handler 
calls the "general" error report.

Why use Rammewerk ErrorHandler?
---------------
PHP will either throw exceptions - which are intended to be caught - or print Errors which are generally unrecoverable.
But instead of having to deal with both exceptions and errors, we convert errors so you only have to deal
with exceptions. No more `@file_get_contents` just nice and neat try/catch.

**Uncaught** exceptions will be given to your `log` and `report` closures.

Many other frameworks or libraries are way more advanced, and comes with loads of functions. This ErrorHandler is simple
by design - you decide how to handle your uncaught exceptions and errors.

Tips
---------------

* Add the ErrorHandler class as early in your application as possible.
* Add `log` and `report` closure at any point you want. The `ErrorHandler` will only call these closures if the
  exception occurred after the registered closure. So, consider adding `log` and `report` closures as soon as possible.
* You can add multiple `log` and `report` closures. `log` will be called before `report` closures.
* If you want to remove previous registered closures - set the second argument as true in `log` or `report`.
  Example: `$error->log( $closure, true )`. This can be useful if you want to replace logging after calling other
  dependencies at a later point in your scripts.

Example
---------------

```php
use Rammewerk\Component\ErrorHandler;

CONST DEBUG_MODE = true;

$errorHandler = new ErrorHandler();

/** Log latest exception to JSON file */
$errorHandler->log( static function (\Throwable $e) {
  file_put_contents( 'latest_error.json', json_encode($e->getMessage()) );
});

/** Show default 500 error page */
$errorHandler->report( static function () {
    http_response_code( 500 );
    echo file_get_contents( "errors/500.html" );
    die;
} );

/** 
 * Show error details if DEBUG_MODE. 
 * Second argument here (true) is used to reset the report list.
 * report closures 
 */
if( DEBUG_MODE ) { 
  $errorHandler->report( function (\Throwable $e) {
      $debug = new \CustomDebugClass();
      $debug->exception($e);
      die;
  }, true);
}
```

Handle different type of exceptions
---------------
`report` and `log` closures handle any `Throwable` exceptions. To distinguish between exception types, incorporate
an `instanceOf` check within the closure.

```php
$errorHandling->report( function (\Throwable $e) {
    if( $e instanceof \MyCustomException) ) {
        die('MyCustomException was thrown');
    }
  } );
```