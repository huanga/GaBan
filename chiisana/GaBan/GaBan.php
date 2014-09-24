<?php
namespace chiisana\GaBan;

use chiisana\GaBan\Exception\NoImageLoadedException;
use chiisana\GaBan\Exception\RuntimeException;
use chiisana\GaBan\Fingerprinter\FindimagedupesFingerprinter;
use chiisana\GaBan\Fingerprinter\HistogramGridFingerprinter;
use Imagine\Gd\Imagine;
use Imagine\Image\ImageInterface;
use Imagine\Exception\Exception as ImagineException;

class GaBan {
    public $imagine;
    public $image;

    public function __construct(Imagine $imagine = null) {
        if (empty($imagine)) {
            // BAD, should throw exception and DI this.
            $imagine = new Imagine();
        }
        $this->imagine = $imagine;
    }

    public function setImageFromPath($path) {
        try {
            $image = $this
                ->imagine
                ->open($path);
            $this->setImage($image);
        } catch ( ImagineException $e ) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function setImage(ImageInterface $image) {
        $this->image = $image->copy();

        return $this;
    }

    // TODO: Refactor these so we are not doing new XxxxxFingerprinter() and deal with them using factory and generics
    public function getSignature() {
        if (empty($this->image)) {
            throw new NoImageLoadedException();
        }

        $fingerprinter = new FindimagedupesFingerprinter();

        return $fingerprinter->run($this->image);
    }

    public function getHash() {
        if (empty($this->image)) {
            throw new NoImageLoadedException();
        }

        $fingerprinter = new HistogramGridFingerprinter();

        return $fingerprinter->run($this->image);
    }
} 