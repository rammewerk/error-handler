<?php

namespace Rammewerk\Component\ErrorHandler;

use Closure;
use ErrorException;
use RuntimeException;
use Throwable;

class ErrorHandler {

    /** @var Closure[] Array of closures for reporting errors. */
    private array $reportCallback = [];

    /** @var Closure[] Array of closures for logging errors. */
    private array $logCallbacks = [];

    /**
     * Constructor for ErrorHandler.
     * Initializes error handling by setting custom error, exception, and shutdown handlers.
     */
    public function __construct() {
        error_reporting( E_ALL );
        ini_set( 'display_errors', 'Off' );
        set_error_handler( [$this, 'handleError'], E_ALL );
        set_exception_handler( [$this, 'handleException'] );
        register_shutdown_function( [$this, 'handleShutdown'] );
    }


    /**
     * Handles uncaught exceptions.
     * Executes all log and report callbacks with the given exception.
     *
     * @param Throwable $e The uncaught exception.
     * @return void
     */
    public function handleException(Throwable $e): void {

        foreach( $this->logCallbacks as $callback ) {
            $callback( $e );
        }

        foreach( array_reverse( $this->reportCallback ) as $callback ) {
            $callback( $e );
        }

    }


    /**
     * Converts PHP errors to ErrorException instances.
     * Throws an ErrorException if the error level is non-zero and error reporting is enabled.
     *
     * @param int $level Error level.
     * @param string $message Error message.
     * @param string $file (optional) File in which the error occurred.
     * @param int $line (optional) Line number at which the error occurred.
     * @return bool Returns false if error level is zero or error reporting is not enabled.
     * @throws ErrorException Thrown when a non-zero error level is reported.
     */
    public function handleError(int $level, string $message, string $file = '', int $line = 0): bool {
        if( !($level && error_reporting()) ) return false;
        throw new ErrorException( $message, 0, $level, $file, $line );
    }


    /**
     * Handles the PHP shutdown event.
     * Checks for a fatal error and processes it as an exception.
     */
    public function handleShutdown(): void {
        if( !is_null( $error = error_get_last() ) && $this->isFatal( $error['type'] ) ) {
            $this->handleException( $this->fatalExceptionFromError( $error ) );
        }
    }


    /**
     * Checks if an error type is considered fatal.
     *
     * @param int $type Error type constant.
     * @return bool Returns true if error type is fatal, false otherwise.
     */
    private function isFatal(int $type): bool {
        return in_array( $type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE], true );
    }


    /**
     * Creates an ErrorException from a PHP error array.
     *
     * @param array{type:int,message:string,file:string,line:int} $error Error information.
     * @return ErrorException The constructed ErrorException.
     */
    private function fatalExceptionFromError(array $error): ErrorException {
        return new ErrorException( $error['message'], $error['type'], 0, $error['file'], $error['line'] );
    }

    /**
     * Registers a closure for logging exceptions.
     *
     * @param Closure $closure The closure to register.
     * @param bool $reset Whether to reset existing log closures.
     * @return void
     */
    public function log(Closure $closure, bool $reset = false): void {
        if( $reset ) $this->logCallbacks = [];
        $this->logCallbacks[] = $closure;
    }


    /**
     * Registers a closure for reporting exceptions.
     *
     * @param Closure(Throwable):void $closure The closure to register.
     * @param bool $reset Whether to reset existing report closures.
     * @return void
     */
    public function report(Closure $closure, bool $reset = false): void {
        if( $reset ) $this->reportCallback = [];
        $this->reportCallback[] = $closure;
    }

    /**
     * Register exception logging to JSON Line file.
     * A simple helper function to register all uncaught exceptions to a JSON Line file.
     * Registers the saveExceptionToJsonL() function to log que.
     *
     * @param string $file_path Path to the JSON Lines file.
     * @return void
     * @throws RuntimeException If the directory for the JSON Lines file cannot be created.
     */
    public function registerJsonl(string $file_path): void {
        $dir = dirname( $file_path );
        if( !is_dir( $dir ) && !mkdir( $dir, 0755, true ) && !is_dir( $dir ) ) {
            throw new RuntimeException( sprintf( 'Jsonl Log directory "%s" was not created', $dir ) );
        }
        $this->log( fn(Throwable $e) => $this->saveExceptionToJsonL( $e, $file_path ) );
    }

    /**
     *  Saves an exception to a JSON Lines file.
     *  Logs exception details including timestamp, class, message, file, line, and request URI.
     *
     * @param Throwable $e The exception to log
     * @param string $path Path to the JSONL file
     * @return int|null Returns the line-number of which the log was added, or null if it failed to save log.
     */
    public function saveExceptionToJsonL(Throwable $e, string $path): ?int {
        try {
            if( file_put_contents( $path, json_encode( [
                    'date' => date( 'Y-m-d H:i:s' ),
                    'class' => get_class( $e ),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'request_uri' => $_SERVER['REQUEST_URI'] ?? ''
                ], JSON_THROW_ON_ERROR ) . PHP_EOL, FILE_APPEND ) ) {
                /** @phpstan-ignore-next-line */
                return count( file( $path ) );
            }
        } catch( Throwable ) {
            // Silence!
        }
        return null;
    }


}
