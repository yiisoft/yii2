<?php

namespace yiiunit\framework\filters\stubs;

use yii\rbac\PhpManager;

class MockAuthManager extends PhpManager {

    /**
     * This mock does not persist
     * @inheritdoc
     */
    protected function saveToFile($data, $file) {
    }

}
