<?php

namespace yii\captcha\drivers;

use yii\base\Object;

/**
 * Renders the CAPTCHA image based on the code using ImageMagick library.
 */
class ImagickDriver extends Object implements DriverInterface
{
    /**
     * {@inheritdoc}
     */
    public function renderCaptcha($code, ImageSettings $imageSettings)
    {
        $backColor = $imageSettings->transparent ? new \ImagickPixel('transparent') : new \ImagickPixel('#' . str_pad(dechex($imageSettings->backColor), 6, 0, STR_PAD_LEFT));
        $foreColor = new \ImagickPixel('#' . str_pad(dechex($imageSettings->foreColor), 6, 0, STR_PAD_LEFT));

        $image = new \Imagick();
        $image->newImage($imageSettings->width, $imageSettings->height, $backColor);

        $draw = new \ImagickDraw();
        $draw->setFont($imageSettings->fontFile);
        $draw->setFontSize(30);
        $fontMetrics = $image->queryFontMetrics($draw, $code);

        $length = strlen($code);
        $w = (int) $fontMetrics['textWidth'] - 8 + $imageSettings->offset * ($length - 1);
        $h = (int) $fontMetrics['textHeight'] - 8;
        $scale = min(($imageSettings->width - $imageSettings->padding * 2) / $w, ($imageSettings->height - $imageSettings->padding * 2) / $h);
        $x = 10;
        $y = round($imageSettings->height * 27 / 40);
        for ($i = 0; $i < $length; ++$i) {
            $draw = new \ImagickDraw();
            $draw->setFont($imageSettings->fontFile);
            $draw->setFontSize((int) (rand(26, 32) * $scale * 0.8));
            $draw->setFillColor($foreColor);
            $image->annotateImage($draw, $x, $y, rand(-10, 10), $code[$i]);
            $fontMetrics = $image->queryFontMetrics($draw, $code[$i]);
            $x += (int) $fontMetrics['textWidth'] + $imageSettings->offset;
        }

        $image->setImageFormat('png');
        
        return $image->getImageBlob();
    }

    /**
     * {@inheritdoc}
     */
    public function checkRequirements()
    {
        return $this->isAvailableImagick() && in_array('PNG', $this->getImagickFormats(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequirementsError()
    {
        return 'Not available ImageMagick  extension or ImageMagick extension without PNG support is required';
    }

    /**
     * @codeCoverageIgnore
     * @return string[]
     */
    protected function isAvailableImagick()
    {
        return extension_loaded('imagick');
    }

    /**
     * @codeCoverageIgnore
     * @return []
     */
    protected function getImagickFormats()
    {
        return (new \Imagick())->queryFormats('PNG');
    }
}
