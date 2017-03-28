<?php

namespace yii\captcha\drivers;

/**
 * @property drivers\ImageSettings $imageSettings
 */
trait ImageSettingsTrait
{
    public function setWidth($width)
    {
        $this->imageSettings->width = (int) $width;
    }

    public function setHeight($height)
    {
        $this->imageSettings->height = (int) $height;
    }

    public function setPadding($padding)
    {
        $this->imageSettings->padding = (int) $padding;
    }

    public function setBackColor($backColor)
    {
        $this->imageSettings->backColor = (int) $backColor;
    }

    public function setForeColor($foreColor)
    {
        $this->imageSettings->foreColor = (int) $foreColor;
    }

    public function setOffset($offset)
    {
        $this->imageSettings->offset = (int) $offset;
    }

    public function setFontFile($fontFile)
    {
        $this->imageSettings->fontFile = $fontFile;
    }
}
