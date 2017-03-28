<?php

namespace yii\captcha\drivers;

use yii\base\Object;
use yii\base\InvalidConfigException;

/**
 *
 */
class DriverFactory extends Object
{
    const IMAGICK = 'imagick';

    const GD = 'gd';

    /**
     * Creates driver for captcha.
     * @param type $driverName
     * @return \yii\captcha\drivers\Driver
     * @throws InvalidConfigException
     */
    public function make($driverName = null)
    {
        if (!$driverName) {
            $driverName = $this->getAvailableDriverName();
        }
        
        $driver = null;
        
        switch ($driverName) {
            case self::IMAGICK:
                $driver = new ImagickDriver();
                break;
            
            case self::GD:
                $driver = new GdDriver();
                break;

            default:
                throw new InvalidConfigException("Defined library '{$driverName}' is not supported");
        }
        
        return $driver;
    }

    /**
     * Returned used image module.
     * @return string
     * @throws InvalidConfigException
     */
    protected function getAvailableDriverName()
    {
        $driverName = null;

        $extensions = $this->getLoadedExtensions();

        if (in_array(self::IMAGICK, $extensions)) {
            $driverName = self::IMAGICK;
        } elseif (in_array(self::GD, $extensions)) {
            $driverName = self::GD;
        }

        return $driverName;
    }

    /**
     * @codeCoverageIgnore
     * @return []
     */
    protected function getLoadedExtensions()
    {
        return get_loaded_extensions();
    }
}
