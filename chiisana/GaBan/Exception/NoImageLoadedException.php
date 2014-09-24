<?php
namespace chiisana\GaBan\Exception;

use chiisana\GaBan\Exception\Exception as GaBanException;
use Exception;
use RuntimeException;

class NoImageLoadedException extends RuntimeException implements GaBanException {
    public function __construct ($message = "No image loaded to perform analysis on!", $code = 0, Exception $previous = NULL) {
        parent::__construct($message, $code, $previous);
    }
} 