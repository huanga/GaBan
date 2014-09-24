<?php
namespace chiisana\GaBan\Fingerprinter;

use Imagine\Image\ImageInterface;

interface FingerprintStrategyInterface {
    public function configure(array $configuration);
    public function run(ImageInterface $image);
}