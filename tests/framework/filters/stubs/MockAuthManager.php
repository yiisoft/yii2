<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\filters\stubs;

use yii\rbac\PhpManager;

class MockAuthManager extends PhpManager
{
    /**
     * This mock does not persist.
     * {@inheritdoc}
     */
    protected function saveToFile($data, $file)
    {
    }
}
