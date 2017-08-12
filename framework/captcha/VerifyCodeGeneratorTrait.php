<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\captcha;

/**
 * VerifyCodeGeneratorTrait provides configurable implementation for [[DriverInterface::generateVerifyCode()]].
 * This trait should be used at the class, which implements [[DriverInterface]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
trait VerifyCodeGeneratorTrait
{
    /**
     * @var int the minimum length for randomly generated word. Defaults to 6.
     */
    public $minLength = 6;
    /**
     * @var int the maximum length for randomly generated word. Defaults to 7.
     */
    public $maxLength = 7;


    /**
     * Generates new CAPTCHA code.
     * @return string CAPTCHA code.
     */
    public function generateVerifyCode()
    {
        if ($this->minLength > $this->maxLength) {
            $this->maxLength = $this->minLength;
        }
        if ($this->minLength < 3) {
            $this->minLength = 3;
        }
        if ($this->maxLength > 20) {
            $this->maxLength = 20;
        }
        $length = mt_rand($this->minLength, $this->maxLength);

        $letters = 'bcdfghjklmnpqrstvwxyz';
        $vowels = 'aeiou';
        $code = '';
        for ($i = 0; $i < $length; ++$i) {
            if ($i % 2 && mt_rand(0, 10) > 2 || !($i % 2) && mt_rand(0, 10) > 9) {
                $code .= $vowels[mt_rand(0, 4)];
            } else {
                $code .= $letters[mt_rand(0, 20)];
            }
        }

        return $code;
    }
}