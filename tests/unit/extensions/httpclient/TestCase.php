<?php

namespace yiiunit\extensions\httpclient;

use Yii;

/**
 * TestCase for "httpclient" extension.
 */
class TestCase extends \yiiunit\TestCase
{
    /**
     * Adds sphinx extension files to [[Yii::$classPath]],
     * avoiding the necessity of usage Composer autoloader.
     */
    public static function loadClassMap()
    {
        $baseNameSpace = 'yii/httpclient';
        $basePath = realpath(__DIR__. '/../../../../extensions/httpclient');

        $alias = '@' . $baseNameSpace;
        if (!in_array($alias, Yii::$aliases)) {
            Yii::setAlias($alias, $basePath);
        }
    }
}

TestCase::loadClassMap();
