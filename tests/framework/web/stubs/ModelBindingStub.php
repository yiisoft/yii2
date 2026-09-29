<?php

namespace yiiunit\framework\web\stubs;

use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;

class ModelBindingStub extends ActiveRecord
{
    /**
     * @return self
     * @throws NotFoundHttpException
     */
    public static function build()
    {
        throw new NotFoundHttpException('Not Found Item.');
    }
}
