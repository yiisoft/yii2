<?php

namespace yiiunit\data\cache;

use yii\caching\Dependency;

/**
 * Class MockDependency
 * @package tests\data\cache
 *
 * @author Boudewijn Vahrmeijer <vahrmeijer@gmail.com>
 * @since 2.0.11
 */
class MockDependency extends Dependency
{
    protected function generateDependencyData($cache)
    {
        return $this->data;
    }
}