<?php
namespace chiisana\GaBan\Fingerprinter;

use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Palette\Color\RGB as RGBColor;
use Imagine\Image\Palette\RGB as RGBPalette;
use Imagine\Image\Point;

class FindimagedupesFingerprinter extends GenericFingerprinter implements FingerprintStrategyInterface {
    CONST DEFAULT_GRID_SIZE        = 8;       // 8x8 by default; produces a 64bit signature
    CONST DEFAULT_GRID_SECTOR_SIZE = 10;      // Scale image to 10x grid size for sampling
    CONST DEFAULT_BLUR_RATIO       = 32;      // Blurring factor to combat compression artifacts

    CONST DEFAULT_NORMALIZE_PEAK = 254; // Peak value for normalized image (used for scaling color space)
    CONST INITIAL_NORMALIZE_MIN  = 0;
    CONST INITIAL_NORMALIZE_MAX  = 255;

    public $configuration;

    public function __construct(array $configuration = []) {
        $configuration['sectorsWide']    = isset($configuration['sectorsWide']) ? $configuration['sectorsWide'] : self::DEFAULT_GRID_SIZE;
        $configuration['sectorsHigh']    = isset($configuration['sectorsHigh']) ? $configuration['sectorsHigh'] : self::DEFAULT_GRID_SIZE;
        $configuration['gridSectorSize'] = isset($configuration['gridSectorSize']) ? $configuration['gridSectorSize'] : self::DEFAULT_GRID_SECTOR_SIZE;
        $configuration['blurRatio']      = isset($configuration['blurRatio']) ? $configuration['blurRatio'] : self::DEFAULT_BLUR_RATIO;

        $configuration['normalizePeak'] = isset($configuration['normalizePeak']) ? $configuration['normalizePeak'] : self::DEFAULT_NORMALIZE_PEAK;
        $configuration['normalizeMin']  = isset($configuration['normalizeMin']) ? $configuration['normalizeMin'] : self::INITIAL_NORMALIZE_MIN;
        $configuration['normalizeMax']  = isset($configuration['normalizeMax']) ? $configuration['normalizeMax'] : self::INITIAL_NORMALIZE_MAX;

        parent::__construct($configuration);
    }

    public function prepare(ImageInterface $image)
    {
        $localImage = $image->copy();

        $prepareSize = new Box(
            $this->configuration['sectorsWide'] * $this->configuration['gridSectorSize'],
            $this->configuration['sectorsHigh'] * $this->configuration['gridSectorSize']
        );

        $scaledSize = new Box( $this->configuration['sectorsWide'], $this->configuration['sectorsHigh'] );

        $localImage
            ->resize($prepareSize)
            ->effects()
            ->grayscale()
            ->blur($this->configuration['blurRatio']);

        $localImage = $this
            ->normalize($localImage)
            ->resize($scaledSize);

        return $localImage;
    }

    public function normalize(ImageInterface $image) {
        $dimension = $image->getSize();

        // Calculate min/max, since we're in grayscale, we should be able to use just one color
        for($x = 0; $x < $dimension->getWidth(); $x++) {
            for($y = 0; $y < $dimension->getHeight(); $y++) {
                $pixelPosition = new Point($x,$y);
                $pixelColor    = $image
                    ->getColorAt($pixelPosition)
                    ->grayscale();
                $gray          = $pixelColor->getRed();

                $this->configuration['normalizeMin'] = ($gray<$this->configuration['normalizeMin']) ? $gray : $this->configuration['normalizeMin'];
                $this->configuration['normalizeMax'] = ($gray>$this->configuration['normalizeMax']) ? $gray : $this->configuration['normalizeMax'];
            }
        }

        // Calculate the normalized range
        $displacement = $this->configuration['normalizeMin'];

        if($this->configuration['normalizeMax'] != $displacement) {
            $scale = $this->configuration['normalizePeak'] / ($this->configuration['normalizeMax'] - $displacement);
        } else {
            $scale = 1;
        }

        // Re-draw Normalized Image
        for($x = 0; $x < $dimension->getWidth(); $x++) {
            for($y = 0; $y < $dimension->getHeight(); $y++) {
                $pixelPosition = new Point($x,$y);
                $pixelColor    = $image
                    ->getColorAt($pixelPosition)
                    ->grayscale();
                $gray          = ($pixelColor->getRed() - $displacement) * $scale;
                $newPixelColor = new RGBColor(new RGBPalette(), [$gray, $gray, $gray], $pixelColor->getAlpha());
                $image
                    ->draw()
                    ->dot($pixelPosition, $newPixelColor);
            }
        }

        return $image;
    }

    public function run(ImageInterface $image)
    {
        $localImage = $this->prepare($image);

        $dimension = $localImage->getSize();
        $shades    = [];
        for($x = 0; $x < $dimension->getWidth(); $x++) {
            for ($y = 0; $y < $dimension->getHeight(); $y++) {
                $pixelPosition = new Point($x,$y);
                $pixelColor    = $localImage
                    ->getColorAt($pixelPosition)
                    ->grayscale();
                $shades[]      = $pixelColor->getRed();
            }
        }

        $average   = array_sum($shades) / count($shades);
        $signature = [];
        for($x = 0; $x < $dimension->getWidth(); $x++) {
            for ($y = 0; $y < $dimension->getHeight(); $y++) {
                $pixelPosition = new Point($x,$y);
                $pixelColor    = $localImage
                    ->getColorAt($pixelPosition)
                    ->grayscale();
                $signature[]   = ( $pixelColor->getRed() > $average ) ? 0 : 1;
            }
        }

        return implode('',$signature);
    }
}