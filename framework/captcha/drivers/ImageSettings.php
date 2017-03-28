<?php

namespace yii\captcha\drivers;

use yii\base\Object;

/**
 * Params image for generate captcha.
 */
class ImageSettings extends Object
{
    /**
     * @var int the width of the generated CAPTCHA image. Defaults to 120.
     */
    public $width = 120;

    /**
     * @var int the height of the generated CAPTCHA image. Defaults to 50.
     */
    public $height = 50;

    /**
     * @var int padding around the text. Defaults to 2.
     */
    public $padding = 2;

    /**
     * @var int the background color. For example, 0x55FF00.
     * Defaults to 0xFFFFFF, meaning white color.
     */
    public $backColor = 0xFFFFFF;

    /**
     * @var int the font color. For example, 0x55FF00. Defaults to 0x2040A0 (blue color).
     */
    public $foreColor = 0x2040A0;

    /**
     * @var bool whether to use transparent background. Defaults to false.
     */
    public $transparent = false;

    /**
     * @var int the offset between characters. Defaults to -2. You can adjust this property
     * in order to decrease or increase the readability of the captcha.
     */
    public $offset = -2;

    /**
     * @var string the TrueType font file. This can be either a file path or path alias.
     */
    public $fontFile = '@yii/captcha/SpicyRice.ttf';

    public function init()
    {
        $this->fontFile = \Yii::getAlias($this->fontFile);

        if (!is_readable($this->fontFile)) {
            throw new InvalidConfigException("The font file does not exist: {$this->fontFile}");
        }
    }
}
