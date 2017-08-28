<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\captcha;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Driver is the base class for CAPTCHA rendering driver classes.
 *
 * By configuring the properties of Driver, you may customize the appearance of
 * the generated CAPTCHA images, such as the font color, the background color, etc.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
abstract class Driver extends Component implements DriverInterface
{
    use VerifyCodeGeneratorTrait;

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
     * @var int the offset between characters. Defaults to -2. You can adjust this property
     * in order to decrease or increase the readability of the captcha.
     */
    public $offset = -2;
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
     * @var string the TrueType font file. This can be either a file path or [path alias](guide:concept-aliases).
     */
    public $fontFile = '@yii/captcha/SpicyRice.ttf';


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        $this->fontFile = Yii::getAlias($this->fontFile);
        if (!is_file($this->fontFile)) {
            throw new InvalidConfigException("The font file does not exist: {$this->fontFile}");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getImageMimeType()
    {
        return 'image/png';
    }
}