<?php

namespace yii\db;

/**
 * Interface ConnectionLoggerInterface
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.13
 */
interface ConnectionLoggerInterface
{
    public function log($message, $category);

    public function error($message, $category);

    public function trace($message, $category);

    public function warning($message, $category);
}
