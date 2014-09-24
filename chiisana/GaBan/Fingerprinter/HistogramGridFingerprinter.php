<?php
namespace chiisana\GaBan\Fingerprinter;

use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;

class HistogramGridFingerprinter extends GenericFingerprinter implements FingerprintStrategyInterface {
    CONST DEFAULT_GRID_SIZE        = 8;
    CONST DEFAULT_GRID_SECTOR_SIZE = 10;
    CONST DEFAULT_BLUR_RATIO       = 32;      // Blurring factor to combat compression artifacts

    public $configuration;

    public function __construct(array $configuration = [])
    {
        $configuration['sectorsWide'] = isset($configuration['sectorsWide']) ? $configuration['sectorsWide'] : self::DEFAULT_GRID_SIZE;
        $configuration['sectorsHigh'] = isset($configuration['sectorsHigh']) ? $configuration['sectorsHigh'] : self::DEFAULT_GRID_SIZE;
        $configuration['gridSectorSize'] = isset($configuration['gridSectorSize']) ? $configuration['gridSectorSize'] : self::DEFAULT_GRID_SECTOR_SIZE;
        $configuration['blurRatio'] = isset($configuration['blurRatio']) ? $configuration['blurRatio'] : self::DEFAULT_BLUR_RATIO;

        parent::__construct($configuration);
    }

    public function prepare(ImageInterface $image)
    {
        $localImage = $image->copy();

        $scaledSize = new Box(
            $this->configuration['sectorsWide'] * $this->configuration['gridSectorSize'],
            $this->configuration['sectorsHigh'] * $this->configuration['gridSectorSize']
        );

        $localImage
            ->effects()
            ->blur($this->configuration['blurRatio']);
        $localImage->resize($scaledSize);

        return $localImage;
    }

    public function run(ImageInterface $image)
    {
        $localImage = $this->prepare($image);

        $histogram = [];
        for($sectorX = 0; $sectorX < $this->configuration['sectorsWide']; $sectorX++) {
            for($sectorY = 0; $sectorY < $this->configuration['sectorsHigh']; $sectorY++) {
                $gridRed   = [];
                $gridGreen = [];
                $gridBlue  = [];
                $gridAlpha = [];

                for($localX = 0; $localX < $this->configuration['gridSectorSize']; $localX++) {
                    for($localY = 0; $localY < $this->configuration['gridSectorSize']; $localY++) {
                        $pixelPosition = new Point($localX, $localY);
                        $pixelColor    = $localImage->getColorAt($pixelPosition);
                        $gridRed[]     = $pixelColor->getRed();
                        $gridGreen[]   = $pixelColor->getGreen();
                        $gridBlue[]    = $pixelColor->getBlue();
                        $gridAlpha[]   = $pixelColor->getAlpha();
                    }
                }

                $red   = intval(array_sum($gridRed)   / count($gridRed));
                $green = intval(array_sum($gridGreen) / count($gridGreen));
                $blue  = intval(array_sum($gridBlue)  / count($gridBlue));
                $alpha = intval(array_sum($gridAlpha) / count($gridAlpha));

                $histogram[] =
                    base_convert($red, 10, 16) .
                    base_convert($green, 10, 16) .
                    base_convert($blue, 10, 16) .
                    base_convert($alpha, 10, 16);
            }
        }

        return $histogram;
    }
}