<?php
namespace chiisana\GaBan;

use chiisana\GaBan\Exception\InvalidFingerprinterException;

class FingerprinterFactory {
    public $validFingerprinter = ['Findimagedupes', 'HistogramGrid'];

    public function getFingerprinter($fingerprinter, array $configuration = []) {
        if (!in_array($fingerprinter, $this->validFingerprinter)) {
            throw new InvalidFingerprinterException();
        }
        $class = '\\chiisana\\GaBan\\Fingerprinter\\' . $fingerprinter . 'Fingerprinter';

        return new $class($configuration);
    }
} 