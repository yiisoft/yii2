<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\cache;

use yii\caching\Dependency;

/**
 * Class MockDependency.
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
