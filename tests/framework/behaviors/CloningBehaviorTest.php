<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\behaviors;

use yii\base\Behavior;
use yii\base\Model;
use yiiunit\TestCase;

/**
 * Unit test for cloning behaviors when objects are cloned.
 *
 * @group behaviors
 */
class CloningBehaviorTest extends TestCase
{
    public function testCloningObjectsClonesBehaviors()
    {
        $model1 = new ModelWithBehavior();
        $model1->myBehaviorProperty = 'foo';
        $model2 = clone $model1;

        $this->assertEquals($model1->myBehaviorProperty, $model2->myBehaviorProperty);
    }
}

/**
 * Test Model class with a behavior attached.
 *
 * @mixin BehaviorWithProperty
 */
class ModelWithBehavior extends Model
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'myBehavior' => BehaviorWithProperty::class
        ];
    }
}

/**
 * Test Behavior class with property.
 *
 */
class BehaviorWithProperty extends Behavior
{
    public $myBehaviorProperty;
}

