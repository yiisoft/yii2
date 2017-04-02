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
     * @return string|null
     */
    public function getError();
}
