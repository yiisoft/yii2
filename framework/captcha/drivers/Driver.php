<?php

namespace yii\captcha\drivers;

use yii\base\Object;

/**
 * Base class for Drivers.
 */
abstract class Driver extends Object
{
    /**
     *
     * @var string[]
     */
    private $errors = [];

    /**
     * Renders the CAPTCHA image.
     * @param string $code Captcha code
     * @param ImageSettings $imageSettings Image generate settings.
     */
    abstract public function renderCaptcha($code, ImageSettings $imageSettings);

    /**
     * @return bool
     */
    abstract public function checkRequirements();

    /**
     * Name driver.
     * @return string
     */
    abstract public function getName();

    /**
     * @return string[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param string $error
     */
    public function addError($error)
    {
        $this->errors[] = $error;
    }
}
