<?php

namespace yii\captcha\drivers;

use Yii;
use yii\base\Object;
use yii\base\InvalidConfigException;

class DriverFactory extends Object
{
    /**
     * Drivers list implement.
     * Driver implement DriverInterface.
     * @var array
     */
    public $drivers = [
        self::IMAGICK => 'yii\captcha\drivers\ImagickDriver',
        self::GD      => 'yii\captcha\drivers\GdDriver',
    ];

    const IMAGICK = 'imagick';
    const GD = 'gd';

    /**
     * Creates driver for captcha.
     * @param string $driverName
     * @return \yii\captcha\drivers\DriverInterface
     * @throws InvalidConfigException
     */
    public function make($driverName = null)
    {
        $drivers = $this->drivers;

        if (isset($drivers[$driverName])) {
            return $this->buildDriver($drivers[$driverName]);
        } elseif ($driverName) {
            throw new InvalidConfigException("Defined library '{$driverName}' is not supported");
        }

        $errors = [];
        foreach ($drivers as $driverClass) {
            try {
                return $this->buildDriver($driverClass);
            }
            catch (InvalidConfigException $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        throw new InvalidConfigException('Unable to use Captcha: ' . implode(', ', $errors));
    }

    private function buildDriver($driverClass)
    {
        /* @var $driver DriverInterface */
        $driver = Yii::createObject($driverClass);

        if (!$driver->checkRequirements()) {
            $error = $driver->getError();
            throw new InvalidConfigException($error);
        }

        return $driver;
    }
}
