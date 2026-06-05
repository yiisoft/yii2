<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\helpers;

use yii\base\DynamicModel;
use yii\helpers\Html;
use yiiunit\framework\validators\stubs\IntStatus;
use yiiunit\framework\validators\stubs\StringStatus;
use yiiunit\framework\validators\stubs\Suit;
use yiiunit\TestCase;

/**
 * @group helpers
 * @requires PHP >= 8.1
 */
class HtmlEnumTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../validators/stubs/EnumStubs.php';
        $this->destroyApplication();
    }

    public function testGetAttributeValueWithStringBackedEnum(): void
    {
        $model = new DynamicModel(['status' => StringStatus::Active]);
        $this->assertSame('active', Html::getAttributeValue($model, 'status'));
    }

    public function testGetAttributeValueWithIntBackedEnum(): void
    {
        $model = new DynamicModel(['status' => IntStatus::On]);
        $this->assertSame(1, Html::getAttributeValue($model, 'status'));
    }

    public function testGetAttributeValueWithUnitEnum(): void
    {
        $model = new DynamicModel(['suit' => Suit::Hearts]);
        $this->assertSame('Hearts', Html::getAttributeValue($model, 'suit'));
    }

    public function testGetAttributeValueWithArrayOfEnums(): void
    {
        $model = new DynamicModel(['statuses' => [StringStatus::Active, StringStatus::Inactive]]);
        $this->assertSame(['active', 'inactive'], Html::getAttributeValue($model, 'statuses'));
    }

    public function testGetAttributeValueWithArrayOfUnitEnums(): void
    {
        $model = new DynamicModel(['suits' => [Suit::Hearts, Suit::Spades]]);
        $this->assertSame(['Hearts', 'Spades'], Html::getAttributeValue($model, 'suits'));
    }

    public function testGetAttributeValueWithMixedArray(): void
    {
        $model = new DynamicModel(['items' => [StringStatus::Active, 'plain', 42]]);
        $result = Html::getAttributeValue($model, 'items');
        $this->assertSame(['active', 'plain', 42], $result);
    }
}
