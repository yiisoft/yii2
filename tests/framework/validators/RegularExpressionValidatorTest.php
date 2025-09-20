<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

use yii\validators\RegularExpressionValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group validators
 */
class RegularExpressionValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockWebApplication();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->destroyApplication();
    }

    public function testValidateValue(): void
    {
        $val = new RegularExpressionValidator(['pattern' => '/^[a-zA-Z0-9](\.)?([^\/]*)$/m']);
        $this->assertTrue($val->validate('b.4'));
        $this->assertFalse($val->validate('b./'));
        $this->assertFalse($val->validate(['a', 'b']));
        $val->not = true;
        $this->assertFalse($val->validate('b.4'));
        $this->assertTrue($val->validate('b./'));
        $this->assertFalse($val->validate(['a', 'b']));
    }

    public function testValidateAttribute(): void
    {
        $val = new RegularExpressionValidator(['pattern' => '/^[a-zA-Z0-9](\.)?([^\/]*)$/m']);
        $m = FakedValidationModel::createWithAttributes(['attr_reg1' => 'b.4']);
        $val->validateAttribute($m, 'attr_reg1');
        $this->assertFalse($m->hasErrors('attr_reg1'));
        $m->attr_reg1 = 'b./';
        $val->validateAttribute($m, 'attr_reg1');
        $this->assertTrue($m->hasErrors('attr_reg1'));
    }

    public function testMessageSetOnInit(): void
    {
        $val = new RegularExpressionValidator(['pattern' => '/^[a-zA-Z0-9](\.)?([^\/]*)$/m']);
        $this->assertIsString($val->message);
    }

    public function testInitException(): void
    {
        $this->expectException('yii\base\InvalidConfigException');
        $val = new RegularExpressionValidator();
        $val->validate('abc');
    }
}
