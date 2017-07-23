<?php

namespace yii\db;

use Yii;

/**
 * Class YiiConnectionLogger
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.13
 */
class YiiConnectionLogger implements ConnectionLoggerInterface
{
    public function log($message, $category)
    {
        Yii::info($message, $category);
    }

    public function error($message, $category)
    {
        Yii::error($message, $category);
    }

    public function trace($message, $category)
    {
        Yii::trace($message, $category);
    }

    public function warning($message, $category)
    {
        Yii::warning($message, $category);
    }
}
