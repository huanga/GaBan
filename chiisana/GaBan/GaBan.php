<?php
namespace chiisana\GaBan;

use Imagine\Image\ImageInterface;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\Color;
use Imagine\Image\Palette\Color\RGB as RGBColor;
use Imagine\Image\Palette\RGB as RGBPalette;

class GaBan {
    public $imagine;

    public function __construct(Imagine $imagine) {
        $this->imagine = $imagine;
    }

    public function findimagedupes($path) {
        $image = $this->imagine->open($path);
        $image->resize(new Box(160,160));
        $image
            ->effects()
            ->grayscale()
            ->blur(10);
        $image = $this->normalize($image);
        $image->resize(new Box(16,16));

        $dimension = $image->getSize();
        $signature = array();
        for($x = 0; $x < $dimension->getWidth(); $x++) {
            for ($y = 0; $y < $dimension->getHeight(); $y++) {
                $pixelPosition = new Point($x,$y);
                $pixelColor    = $image->getColorAt($pixelPosition);
                $signature[]   = ( $pixelColor->getRed() > 125 ) ? 0 : 1;
            }
        }

        return implode('',$signature);
    }

    public function normalize(ImageInterface $image) {
        $min = 255;
        $max = 0;
        $peak = 254;

        $dimension = $image->getSize();

        // Calculate min/max of each color space
        for($x = 0; $x < $dimension->getWidth(); $x++) {
            for($y = 0; $y < $dimension->getHeight(); $y++) {
                $pixelPosition = new Point($x,$y);
                $pixelColor    = $image->getColorAt($pixelPosition);
                $red           = $pixelColor->getRed();

                $min = ($red<$min) ? $red : $min;
                $max = ($red>$max) ? $red : $max;
            }
        }

        // Calculate the normalized range
        $displacement = $min;

        if($max != $displacement) {
            $scale = $peak / ($max - $displacement);
        } else {
            $scale = 1;
        }

        // Re-draw Normalized Image
        for($x = 0; $x < $dimension->getWidth(); $x++) {
            for($y = 0; $y < $dimension->getHeight(); $y++) {
                $pixelPosition = new Point($x,$y);
                $pixelColor    = $image->getColorAt($pixelPosition);
                $shade         = ($pixelColor->getRed() - $displacement) * $scale;

                $newPixelColor = new RGBColor(new RGBPalette(), array($shade, $shade, $shade), $pixelColor->getAlpha());
                $image
                    ->draw()
                    ->dot($pixelPosition, $newPixelColor);
            }
        }

        return $image;
    }

    public function signature($path) {
        return array(
            'findimagedupes.pl' => $this->findimagedupes($path)
        );
    }

    public function fingerprint($path, Box $box) {
        $image = $this->imagine->open($path);
        $image->resize($box);
        $image
            ->effects()
            ->grayscale()
            ->blur(10);

        $dimension = $image->getSize();

        $prints = array();
        for($x = 0; $x < $dimension->getWidth(); $x++) {
            for($y = 0; $y < $dimension->getHeight(); $y++) {
                $pixelPosition = new Point($x,$y);
                $pixelColor    = $image->getColorAt($pixelPosition);
                $prints[] = $pixelColor->getRed();
            }
        }

        return $prints;
    }

    public function getHashes($path) {
        $signature = $this->signature($path);

        $box32x32  = new Box(32,32);
        $box16x128 = new Box(16,128);
        $box128x16 = new Box(128,16);

        $fingerprint = array();
        $fingerprint = array_merge($fingerprint, $this->fingerprint($path, $box32x32));
        $fingerprint = array_merge($fingerprint, $this->fingerprint($path, $box16x128));
        $fingerprint = array_merge($fingerprint, $this->fingerprint($path, $box128x16));

        return array(
            'signature'   => $signature,
            'fingerprint' => $fingerprint
        );
    }
} 