<?php

namespace Rammewerk\Component\ErrorHandler\Logger\Entity;

use Throwable;

class LogEntry {

    public string $date = '';
    public string $class = '';
    public string $message = '';
    public string $file = '';
    public int $line = 0;
    public int $code = 0;
    public string $request_uri = '';
    public array $trace = [];
    public null|LogEntry $previous = null;



    public static function fromThrowable(Throwable $e): LogEntry {
        $log = new static();
        $log->date = date('Y-m-d H:i:s');
        $log->class = get_class($e);
        $log->message = $e->getMessage();
        $log->file = $e->getFile();
        $log->line = $e->getLine();
        $log->code = $e->getCode();
        $log->trace = $e->getTrace();
        $log->request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $log->previous = $e->getPrevious()
            ? static::fromThrowable($e->getPrevious())
            : null;
        return $log;
    }



    public static function fromData(array $stored): LogEntry {
        $log = new static();
        $log->date = $stored['date'] ?? '';
        $log->class = $stored['class'] ?? '';
        $log->message = $stored['message'] ?? '';
        $log->file = $stored['file'] ?? '';
        $log->line = $stored['line'] ?? 0;
        $log->code = $stored['code'] ?? 0;
        $log->trace = $stored['trace'] ?? [];
        $log->request_uri = $stored['request_uri'] ?? '';
        $log->previous = !empty($stored['previous'])
            ? static::fromData($stored['previous'])
            : null;
        return $log;
    }


}