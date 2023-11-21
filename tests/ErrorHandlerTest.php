<?php

namespace Rammewerk\Component\ErrorHandler\Tests;

use LogicException;
use PHPUnit\Framework\TestCase;
use Rammewerk\Component\ErrorHandler\ErrorHandler;

class ErrorHandlerTest extends TestCase {

    public function testHandleExceptionCallsLogAndReportCallbacks(): void {
        $errorHandler = new ErrorHandler();
        $testException = new \RuntimeException( "Test Exception" );

        // Registering callbacks
        $errorHandler->log( $this->createCallbackMock() );
        $errorHandler->report( $this->createCallbackMock() );

        // Expect output to be the exception message twice
        $this->expectOutputString( "Test ExceptionTest Exception" );

        // Triggering the error handling
        $errorHandler->handleException( $testException );
    }

    private function createCallbackMock(): \Closure {
        return static function(\Throwable $e): void {
            echo $e->getMessage();
        };
    }

    public function testGeneratingLog(): void {
        // Set up and reflection
        $errorHandler = new ErrorHandler();
        $reflection = new \ReflectionClass( $errorHandler );
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


    public function testLogToJsonl(): void {
        // Assert that dir has been deleted if exist
        $dir = __DIR__ . '/testDir/';
        $file_path = $dir . 'test.jsonl';

        // Delete the file if it exists.
        if( is_file( $file_path ) && !unlink( $file_path ) ) {
            throw new LogicException( "Failed to delete file: $file_path" );
        }

        // Attempt to remove the directory.
        if( is_dir( $dir ) && !rmdir( $dir ) ) {
            throw new LogicException( "Failed to delete directory: $dir" );
        }

        $this->assertDirectoryDoesNotExist( $dir );

        // Register log
        $errorHandler = new ErrorHandler();
        $errorHandler->registerJsonl( $file_path );
        // Make sure dir now exists
        $this->assertDirectoryExists( $dir );
        $testException = new \RuntimeException( "Test Exception" );
        $errorHandler->handleException( $testException );
        $this->assertFileExists( $file_path );
        $content = file_get_contents( $file_path );
        $this->assertStringContainsString( "Test Exception", $content );
    }

    public function testHandleError() {
        $errorHandler = new ErrorHandler();
        $this->expectException( \ErrorException::class );
        $errorHandler->handleError( E_WARNING, 'Test error', 'test.php', 123 );
    }

}