<?php

namespace yii\captcha\drivers;

interface DriverInterface
{
    /**
     * Renders the CAPTCHA image.
     * @param string $code Captcha code
     * @param ImageSettings $imageSettings Image generate settings.
     */
    public function renderCaptcha($code, ImageSettings $imageSettings);

    /**
     * Checks if there is graphic extension available to generate CAPTCHA images.
     * @return bool
     */
    public function checkRequirements();

    /**
     * @return string|null
     */
    public function getError();
}
