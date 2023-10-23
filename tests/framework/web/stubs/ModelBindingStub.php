<?php

namespace yiiunit\framework\web\stubs;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;

class ModelBindingStub extends ActiveRecord {
    /**
     * @return self;
     * @throw NotFoundHttpException
     */
    public static function build(): never {
        throw new NotFoundHttpException("Not Found Item.");
    }
}