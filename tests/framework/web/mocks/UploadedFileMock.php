<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\mocks;

class UploadedFileMock extends \yii\web\UploadedFile
{
    /**
     * @inheritDoc
     */
    protected function isUploadedFile($file)
    {
        return is_file($file); // is_uploaded_file() won't work in test
    }
}
