Rammewerk ErrorHandler
======================

A simple customizable error handler for PHP. A good starting point to handle errors and logging.

* PHP errors are turned into exceptions by default.
* You access the exceptions by defining your own Closures.
* No other dependencies - small size.

Getting Started
---------------

```
$ composer require rammewerk/error-handler
```

```php
use Rammewerk\Component\ErrorHandler;

$errorHandling = new ErrorHandler();

/** Register a report that should be shown to the user. */
$errorHandling->report( static function (\Throwable $e) {
    // Handle the exception that is caught
});

/** Log errors */
$errorHandling->log( static function (\Throwable $e) {
    // Handle the exception that is caught
});
```

Tips
---------------

* Add the ErrorHandler class as early in your application as possible.
* Add `log` and `report` closure at any point you want, but the `ErrorHandler` will only call these if the error or
  exception
  occurred after the registered closure. So, consider adding a `log` closure as soon as possible.
* You can add multiple closures. `log` closures will be called before `report` closures.
* If you want to reset previous registered closures the `log` and `report` set the seconds argument as true.
  Example: `->log( $closure, true )`. This can be useful if you want to replace logging after calling other dependencies
  at a later point in your scripts.

Examples
---------------

```php
use Rammewerk\Component\ErrorHandler;

CONST DEBUG_MODE = true;

$errorHandling = new ErrorHandler();

/** Log exceptions to JSON file */
$errorHandling->log( static function (\Throwable $e) {
  file_put_contents( 'latest_error.json', json_encode([
    'message' => $e->getMessage(),
    'file'    => $e->getFile(),
    'line'    => $e->getLine(),
    'trace'   => $e->getTrace()
  ], JSON_PRETTY_PRINT ));
});

/** Show exceptions if DEBUG_MODE is set */
if( DEBUG_MODE ) {

    /** Use filp/whoops to show errors if it does exist */
    if( class_exists( \Whoops\Run::class ) ) {
        $errorHandling->report( function (\Throwable $e) {
            $whoops = new \Whoops\Run();
            $handler = new \Whoops\Handler\PrettyPageHandler();
            $whoops->pushHandler( $handler );
            $whoops->handleException( $e );
            die;
        } );
    }

} else {

    /** Show default 500 error page if not debug mode */
    $errorHandling->report( static function () {
        http_response_code( 500 );
        echo file_get_contents( ROOT_DIR . "templates/errors/500.html" );
        die;
    } );

}
```
