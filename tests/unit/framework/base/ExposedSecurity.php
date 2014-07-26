<?php
namespace yiiunit\framework\base;


use yii\base\Security;

/**
 * ExposedSecurity exposes protected methods for direct testing
 */
class ExposedSecurity extends Security
{
    /**
     * @inheritdoc
     */
    public function hkdf($algo, $inputKey, $salt = null, $info = null, $length = 0)
    {
        return parent::hkdf($algo, $inputKey, $salt, $info, $length);
    }

    /**
     * @inheritdoc
     */
    public function pbkdf2($algo, $password, $salt, $iterations, $length = 0)
    {
        return parent::pbkdf2($algo, $password, $salt, $iterations, $length);
    }
} 