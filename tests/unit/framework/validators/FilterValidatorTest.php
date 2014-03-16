<?php

namespace yiiunit\framework\validators;

use yii\validators\FilterValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

class FilterValidatorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testAssureExceptionOnInit()
    {
        $this->setExpectedException('yii\base\InvalidConfigException');
        new FilterValidator();
    }

    public function testValidateAttribute()
    {
        $m = FakedValidationModel::createWithAttributes([
                'attr_one' => '  to be trimmed  ',
                'attr_two' => 'set this to null',
                'attr_empty1' => '',
                'attr_empty2' => null
        ]);
        $val = new FilterValidator(['filter' => 'trim']);
        $val->validateAttribute($m, 'attr_one');
        $this->assertSame('to be trimmed', $m->attr_one);
        $val->filter = function ($value) {
            return null;
        };
        $val->validateAttribute($m, 'attr_two');
        $this->assertNull($m->attr_two);
        $val->filter = [$this, 'notToBeNull'];
        $val->validateAttribute($m, 'attr_empty1');
        $this->assertSame($this->notToBeNull(''), $m->attr_empty1);
        $val->skipOnEmpty = true;
        $val->validateAttribute($m, 'attr_empty2');
        $this->assertNotNull($m->attr_empty2);
    }

    public function notToBeNull($value)
    {
        return 'not null';
    }
}
