<?php
namespace chiisana\GaBan\Fingerprinter;

use Imagine\Image\ImageInterface;

abstract class GenericFingerprinter implements FingerprintStrategyInterface {
    public $configuration;

    public function __construct(array $configuration = []) {
        $this->setConfiguration($configuration);
    }

    public function setConfiguration(array $configuration) {
        $this->configuration = $configuration;
    }

    abstract public function run(ImageInterface $image);
}