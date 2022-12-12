<?php

namespace Rammewerk\Component\ErrorHandler;

use Closure;
use Throwable;
use ErrorException;
use JetBrains\PhpStorm\NoReturn;

class ErrorHandler {

    /** @var Closure[] */
    private array $reportCallbacks = [];

    /** @var Closure[] */
    private array $logCallbacks = [];




    /**
     * ErrorHandler Constructor
     *
     * Only use $initialize = true if used
     *
     */
    public function __construct() {
        error_reporting( E_ALL );
        set_error_handler( [ $this, 'handleError' ], E_ALL );
        set_exception_handler( [ $this, 'handleException' ] );
        register_shutdown_function( [ $this, 'handleShutdown' ] );
    }




    /**
     * Activate Debug Mode
     */
    public function display_errors(): void {
        ini_set( 'display_errors', 'On' );
    }




    /**
     * Convert PHP errors to ErrorException instances
     *
     * @param int $level
     * @param string $message
     * @param string $file
     * @param int $line
     *
     * @throws \ErrorException
     */
    public function handleError(int $level, string $message, string $file = '', int $line = 0): void {
        if( $level && error_reporting() ) {
            throw new ErrorException( $message, 0, $level, $file, $line );
        }
    }




    /**
     * Handle an uncaught exception from the application.
     *
     * @param \Throwable $e
     */
    #[NoReturn] public function handleException(Throwable $e): void {

        foreach( $this->logCallbacks as $callback ) {
            $callback( $e );
        }

        if( empty( $this->reportCallbacks ) ) {
            echo '<h1>' . $e->getMessage() . '</h1>';
            echo '<pre>';
            /** @noinspection ForgottenDebugOutputInspection */
            var_export( $e );
            echo '</pre>';
            die;
        }

        foreach( $this->reportCallbacks as $callback ) {
            $callback( $e );
        }
        die;
    }




    /**
     * Handle the PHP shutdown event.
     */
    public function handleShutdown(): void {
        if( ! is_null( $error = error_get_last() ) && $this->isFatal( $error['type'] ) ) {
            $this->handleException( $this->fatalExceptionFromError( $error ) );
        }
    }




    /**
     * Check if fatal
     *
     * @param int $type
     *
     * @return bool
     */
    private function isFatal(int $type): bool {
        return in_array( $type, [ E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE ], true );
    }




    /**
     * Fatal exception from error
     *
     * @param array $error
     *
     * @return \ErrorException
     */
    private function fatalExceptionFromError(array $error): ErrorException {
        return new ErrorException( $error['message'], $error['type'], 0, $error['file'], $error['line'] );
    }




    /**
     * Register the exception handling callbacks for the application.
     *
     * @param \Closure $closure
     *
     * @return void
     */
    public function log(Closure $closure): void {
        $this->logCallbacks[] = $closure;
    }




    /**
     * Register the exception handling callbacks for the application.
     *
     * @param \Closure $closure
     *
     * @return void
     */
    public function report(Closure $closure): void {
        $this->reportCallbacks[] = $closure;
    }


}
