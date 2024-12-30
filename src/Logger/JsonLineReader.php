<?php

declare(strict_types=1);

namespace Rammewerk\Component\ErrorHandler\Logger;

final class JsonLineReader {

    public function __construct(private string $path) {}



    public function deleteAll(): void {
        unlink( $this->path );
    }



    public function get(): array {
        if( !file_exists( $this->path ) ) return [];
        $lines = file( $this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ) ?: [];
        return array_reverse( $lines );
    }



    public function getEncoded(): array {
        $lines = $this->get();
        return array_map( static function($line) {
            try {
                return json_decode( $line, true, 512, JSON_THROW_ON_ERROR );
            } catch( \JsonException $e ) {
                return $line;
            }
        }, $lines );
    }



    public function deleteLine(int $line): void {
        $lines = $this->get();
        if( isset( $lines[$line] ) ) {
            unset( $lines[$line] );
            $lines = array_reverse( $lines );
            file_put_contents( $this->path, implode( PHP_EOL, $lines ) . PHP_EOL );
        }
    }


}