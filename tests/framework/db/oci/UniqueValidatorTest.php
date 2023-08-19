<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use yii\validators\UniqueValidator;
use yiiunit\data\validators\models\ValidatorTestMainModel;
use yiiunit\data\validators\models\ValidatorTestRefModel;

/**
 * @group db
 * @group oci
 * @group validators
 */
class UniqueValidatorTest extends \yiiunit\framework\validators\UniqueValidatorTest
{
    public $driverName = 'oci';

    public function testValidateEmptyAttributeInStringField()
    {
        ValidatorTestMainModel::deleteAll();

        $val = new UniqueValidator();

        $m = new ValidatorTestMainModel(['id' => 5, 'field1' => ' ']);

        $val->validateAttribute($m, 'field1');
        $this->assertFalse($m->hasErrors('field1'));
        $m->save(false);

        $m = new ValidatorTestMainModel(['field1' => ' ']);
        $val->validateAttribute($m, 'field1');
        $this->assertTrue($m->hasErrors('field1'));
    }
}
