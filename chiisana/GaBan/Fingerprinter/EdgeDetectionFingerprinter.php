<?php
namespace chiisana\GaBan\Fingerprinter;

use Imagine\Image\Box;
use Imagine\Image\Palette\Color\ColorInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;

class EdgeDetectionFingerprinter extends GenericFingerprinter implements FingerprintStrategyInterface {
    CONST DEFAULT_GRID_SIZE        = 8;
    CONST DEFAULT_GRID_SECTOR_SIZE = 10;

    public function __construct(array $configuration = [])
    {
        $configuration['sectorsWide'] = isset($configuration['sectorsWide']) ? $configuration['sectorsWide'] : self::DEFAULT_GRID_SIZE;
        $configuration['sectorsHigh'] = isset($configuration['sectorsHigh']) ? $configuration['sectorsHigh'] : self::DEFAULT_GRID_SIZE;
        $configuration['gridSectorSize'] = isset($configuration['gridSectorSize']) ? $configuration['gridSectorSize'] : self::DEFAULT_GRID_SECTOR_SIZE;

        parent::__construct($configuration);
    }

    public function prepare(ImageInterface $image)
    {
        $localImage = $image->copy();

        $scaledSize = new Box(
            $this->configuration['sectorsWide'] * $this->configuration['gridSectorSize'],
            $this->configuration['sectorsHigh'] * $this->configuration['gridSectorSize']
        );

        $localImage->resize($scaledSize);

        return $localImage;
    }

    /**
     * Detect edge using the Sobel operator's convolution mask:
     *          -1  0  1                  1  2  1
     * L(x) = [ -2  0  2 ] * L, L(y) = [  0  0  0 ] * L
     *          -1  0  1                 -1 -2 -1
     *
     * Magnitude:
     * |derivative(L)| = sqrt(L(x)^2 + L(y)^2)
     *
     * Orientation:
     * Theta = atan2(L(y), L(x))
     *
     * Reference:
     * 1. http://en.wikipedia.org/wiki/Edge_detection#Other_first-order_methods
     * 2. http://en.wikipedia.org/wiki/Sobel_operator
     */
    public function run(ImageInterface $image) {
        $resultMatrix = [];
        $localImage = $this->prepare($image);
        $dimension = $localImage->getSize();

        /**
         * Iterate through each pixel, recess 1 pixel from each side;
         * determine the appropriate value assuming we're the "center" like such:
         *
         *  1  2  3
         *  4 [5] 6
         *  7  8  9
         */
        for($x = 1; $x < $dimension->getWidth()-1; $x++) {
            for ($y = 1; $y < $dimension->getHeight()-1; $y++) {
                // Get pixel luminance
                $pixel1 = $this->getLuminance($this->getColorAt($localImage, $x-1, $y-1));
                $pixel2 = $this->getLuminance($this->getColorAt($localImage, $x  , $y-1));
                $pixel3 = $this->getLuminance($this->getColorAt($localImage, $x+1, $y-1));
                $pixel4 = $this->getLuminance($this->getColorAt($localImage, $x-1, $y  ));
                $pixel5 = $this->getLuminance($this->getColorAt($localImage, $x  , $y  ));
                $pixel6 = $this->getLuminance($this->getColorAt($localImage, $x+1, $y  ));
                $pixel7 = $this->getLuminance($this->getColorAt($localImage, $x-1, $y+1));
                $pixel8 = $this->getLuminance($this->getColorAt($localImage, $x  , $y+1));
                $pixel9 = $this->getLuminance($this->getColorAt($localImage, $x+1, $y+1));

                // Apply convolution mask
//                $convX = ($pixel3+($pixel6*2)+$pixel9)-($pixel1+($pixel4*2)+$pixel7);
//                $convY = ($pixel1+($pixel2*2)+$pixel3)-($pixel7+($pixel8*2)+$pixel9);

                /**
                 * Alternative Matrix:
                 * http://en.wikipedia.org/wiki/Kernel_%28image_processing%29
                 *
                 *   -1 -1 -1
                 * [ -1  8 -1 ]
                 *   -1 -1 -1
                 */
                 $magnitude = ($pixel1 * -1) + ($pixel2 * -1) + ($pixel3 * -1)
                            + ($pixel4 * -1) + ($pixel5 *  8) + ($pixel6 * -1)
                            + ($pixel7 * -1) + ($pixel8 * -1) + ($pixel9 * -1);

                // Obtain magnitude of gradient
//                $magnitude = sqrt($convX*$convX+ $convY*$convY);

                // Normalize
                $magnitude = 255 - $magnitude;
                $magnitude = ($magnitude > 255) ? 255 : $magnitude;
                $magnitude = ($magnitude < 0) ? 0 : $magnitude;

                $resultMatrix[] = $magnitude;
            }
        }

        // Return a higher or lower matrix as we do in Findimagedupes
        $average = array_sum($resultMatrix) / count($resultMatrix);
        $matrix  = [];
        foreach($resultMatrix as $result) {
            $matrix[] = ($result > $average) ? 1 : 0;
        }

        return implode('', $matrix);
    }

    public function getColorAt(ImageInterface $image, $x, $y) {
        $pixelPosition = new Point($x, $y);
        $pixelColor    = $image->getColorAt($pixelPosition);

        return $pixelColor;
    }

    /**
     * Detects luminance of the RGB color
     *
     * Reference:
     * 1. http://stackoverflow.com/questions/596216/formula-to-determine-brightness-of-rgb-color
     * 2. http://alienryderflex.com/hsp.html
     *
     * @param ColorInterface $color
     * @return float
     */
    public function getLuminance(ColorInterface $color) {
        $red   = $color->getRed() * $color->getRed() * 0.299;
        $green = $color->getGreen() * $color->getGreen() * 0.587;
        $blue  = $color->getBlue() * $color->getBlue() * 0.114;

        $luminance = sqrt($red + $green + $blue);
        return $luminance;
    }
}