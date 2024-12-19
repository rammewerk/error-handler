<?php

namespace Rammewerk\Component\ErrorHandler\Tests;

use Closure;
use ErrorException;
use LogicException;
use PHPUnit\Framework\TestCase;
use Rammewerk\Component\ErrorHandler\ErrorHandler;
use ReflectionClass;
use RuntimeException;
use Throwable;

class ErrorHandlerTest extends TestCase {

    public function testHandleExceptionCallsLogAndReportCallbacks(): void {
        $errorHandler = new ErrorHandler();
        $testException = new RuntimeException( "Test Exception" );

        // Registering callbacks
        $errorHandler->log( $this->createCallbackMock() );
        $errorHandler->report( $this->createCallbackMock() );

        // Expect output to be the exception message twice
        $this->expectOutputString( "Test ExceptionTest Exception" );

        // Triggering the error handling
        $errorHandler->handleException( $testException );
    }

    private function createCallbackMock(): Closure {
        return static function(Throwable $e): void {
            echo $e->getMessage();
        };
    }

    public function testGeneratingLog(): void {
        // Set up and reflection
        $errorHandler = new ErrorHandler();
        $reflection = new ReflectionClass( $errorHandler );
        $property_log = $reflection->getProperty( 'logCallbacks' );
        # Register log and assert count
        $errorHandler->log( $this->createCallbackMock() );
        $logs = $property_log->getValue( $errorHandler );
        $this->assertCount( 1, $logs );
        # Register second log and assert count
        $errorHandler->log( $this->createCallbackMock() );
        $logs = $property_log->getValue( $errorHandler );
        $this->assertCount( 2, $logs );
        # Register third log with reset and assert count
        $errorHandler->log( $this->createCallbackMock(), true );
        $logs = $property_log->getValue( $errorHandler );
        $this->assertCount( 1, $logs );
    }


    public function testHandleError(): void {
        $errorHandler = new ErrorHandler();
        $this->expectException( ErrorException::class );
        $errorHandler->handleError( E_WARNING, 'Test error', 'test.php', 123 );
    }

}