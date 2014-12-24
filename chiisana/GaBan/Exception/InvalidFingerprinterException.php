<?php
namespace chiisana\GaBan\Exception;

use chiisana\GaBan\Exception\Exception as GaBanException;
use Exception;
use RuntimeException as StdRuntimeException;

class InvalidFingerprinterException extends StdRuntimeException implements GaBanException {
    public function __construct ($message = "", $code = 0, Exception $previous = NULL) {
        parent::__construct($message, $code, $previous);
    }
} 