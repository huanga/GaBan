<?php
namespace chiisana\GaBan\Fingerprinter;

use Imagine\Image\ImageInterface;

abstract class GenericFingerprinter implements FingerprintStrategyInterface {
    public $configuration;

    public function __construct(array $configuration = []) {
        $this->configure($configuration);
    }

    public function configure(array $configuration) {
        $this->configuration = $configuration;
    }

    abstract public function run(ImageInterface $image);
}