<?php

namespace yii\captcha;

use yii\base\InvalidConfigException;
use yii\base\Object;

/**
 * Image library name for generate captcha.
 */
class ImageExtentionModel extends Object
{
    const IMAGICK = 'imagick';
    
    const GD = 'gd';

    /**
     * Returned used
     * @return string
     * @throws InvalidConfigException
     */
    public function getImageExtension()
    {
        $extensions = $this->getLoadedExtensions();

        if (in_array(self::IMAGICK, $extensions) && $this->existsPNGImagickFormats()) {
            return self::IMAGICK;
        }

        if (in_array(self::GD, $extensions) && $this->isFreeTypeSupportGD()) {
            return self::GD;
        }

        throw new InvalidConfigException('Either GD PHP extension with FreeType support or ImageMagick PHP extension with PNG support is required.');
    }

    /**
     * Is supported png in Imagick.
     * @return bool
     */
    protected function existsPNGImagickFormats()
    {
        $imagickFormats = $this->getImagickFormats();

        return in_array('PNG', $imagickFormats, true);
    }

    /**
     * Is supported FreeType in GD.
     * @return bool
     */
    protected function isFreeTypeSupportGD()
    {
        $gdInfo = $this->getGDInfo();

        return in_array('FreeType Support', $gdInfo);
    }

    /**
     * @codeCoverageIgnore
     * @return []
     */
    protected function getImagickFormats()
    {
        return (new \Imagick())->queryFormats('PNG');
    }

    /**
     * @codeCoverageIgnore
     * @return []
     */
    protected function getLoadedExtensions()
    {
        return get_loaded_extensions();
    }

    /**
     * @codeCoverageIgnore
     * @return []
     */
    protected function getGDInfo()
    {
        return gd_info();
    }
}
