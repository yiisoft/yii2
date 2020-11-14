<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use yii\validators\UniqueValidator;
use yiiunit\data\ar\Document;

/**
 * @group db
 * @group oci
 * @group validators
 */
class UniqueValidatorTest extends \yiiunit\framework\validators\UniqueValidatorTest
{
    public $driverName = 'oci';

    /**
     * Test expression in targetAttribute.
     * @see https://github.com/yiisoft/yii2/issues/14304
     */
    public function testExpressionInAttributeColumnName()
    {
        $validator = new UniqueValidator([
            'targetAttribute' => [
                'title' => 'LOWER([[title]])',
            ],
        ]);
        $model = new Document();
        $model->title = 'Test';
        $model->content = 'test';
        $model->version = 1;
        $model->save(false);
        $validator->validateAttribute($model, 'title');
        $this->assertFalse($model->hasErrors(), 'There were errors: ' . json_encode($model->getErrors()));
    }
}
