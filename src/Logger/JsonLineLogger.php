<?php

declare(strict_types=1);

namespace Rammewerk\Component\ErrorHandler\Logger;

use Rammewerk\Component\ErrorHandler\Logger\Entity\LogEntry;
use RuntimeException;
use Throwable;

final readonly class JsonLineLogger {

    /**
     * Saves an exception to a JSON Lines file.
     *
     * Logs exception details including timestamp, class, message, file, line, trace, and request URI.
     * As this script will usually log exceptions, it shall not throw another exception.
     * Use the debug parameter to print debug information if an error occurs.
     *
     * @param Throwable $e  The exception to log
     * @param string $path  Path to the JSONL file
     * @param bool $debug   Print debug information if an error occurs
     * @param int $maxBytes Maximum size of the log file in bytes. Defaults to 0.5MB.
     */
    public function __construct(
        \Throwable $e,
        string $path,
        bool $debug = false,
        int $maxBytes = 524288,
    ) {

        try {
            $this->createLogDirectory($path);
            $logLine = LogEntry::fromThrowable($e);

            $encoded = json_encode($logLine, JSON_THROW_ON_ERROR);
            file_put_contents($path, $encoded . PHP_EOL, FILE_APPEND);
            $this->truncateIfOverLimit($path, $maxBytes);

        } catch (Throwable $logException) {
            if ($debug) {
                /** @noinspection ForgottenDebugOutputInspection */
                var_dump($logException);
                die;
            }
        }

    }



    private function createLogDirectory(string $path): void {
        $dir = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Unable to create log directory "%s"', $dir));
        }
        if (!is_writable($dir)) {
            throw new RuntimeException(sprintf('Log directory "%s" is not writable', $dir));
        }
    }



    private function truncateIfOverLimit(string $path, int $maxBytes): void {
        if (filesize($path) <= $maxBytes) return;
        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if ($lines !== false && count($lines) > 1) {
            array_shift($lines);
            file_put_contents($path, implode(PHP_EOL, $lines) . PHP_EOL);
        }
    }


}