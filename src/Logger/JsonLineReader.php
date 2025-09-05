<?php

declare(strict_types=1);

namespace Rammewerk\Component\ErrorHandler\Logger;

use Rammewerk\Component\ErrorHandler\Logger\Entity\LogEntry;

final readonly class JsonLineReader {

    public function __construct(private string $path) {}



    public function deleteAll(): void {
        unlink($this->path);
    }



    public function getRaw(): array {
        if (!file_exists($this->path)) return [];
        $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        return array_reverse($lines);
    }



    public function getEncoded(): array {
        return array_map(static function ($line) {
            try {
                return json_decode($line, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                return $line;
            }
        }, $this->getRaw());
    }



    /** @return LogEntry[] */
    public function get(): array {
        return array_filter(array_map(static function ($data) {
            return is_array($data) ? LogEntry::fromData($data) : null;
        }, $this->getEncoded()), function ($item) {
            return $item instanceof LogEntry;
        });
    }



    public function deleteLine(int $line): void {
        $lines = $this->getRaw();
        if (isset($lines[$line])) {
            unset($lines[$line]);
            $lines = array_reverse($lines);
            file_put_contents($this->path, implode(PHP_EOL, $lines) . PHP_EOL);
        }
    }


}