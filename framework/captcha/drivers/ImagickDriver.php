<?php

namespace yii\captcha\drivers;

/**
 * Renders the CAPTCHA image based on the code using ImageMagick library.
 */
class ImagickDriver extends Driver
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
    public function getName()
    {
        return DriverFactory::IMAGICK;
    }

    public function checkRequirements()
    {
        $imagickFormats = $this->getImagickFormats();

        $result = in_array('PNG', $imagickFormats, true);

        if (!$result) {
            $this->addError('ImageMagick PHP extension with PNG support is required');
        }

        return $result;
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
