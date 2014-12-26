<?php
namespace chiisana\GaBan\Fingerprinter;

use Imagine\Image\ImageInterface;

interface FingerprintStrategyInterface {
    public function setConfiguration(array $configuration);
    public function run(ImageInterface $image);
}