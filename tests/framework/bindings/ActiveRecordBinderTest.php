<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings;

use Yii;
use yii\bindings\ActionParameterBinder;
use yii\bindings\binders\ActiveRecordBinder;
use yii\bindings\BindingContext;
use yii\bindings\ModelBinderInterface;
use yiiunit\TestCase;

class Post extends \yii\db\ActiveRecord
{
    public $findOneCalled = false;
    public $setAttributesCalled = false;
    public $arguments = null;

    public static function findOne($condition)
    {
        $instance =  new static();
        $instance->findOneCalled = true;
        $instance->arguments = [
            'condition' => $condition
        ];
        return $instance;
    }

    public function setAttributes($values, $safeOnly = true)
    {
        $this->setAttributesCalled = true;
        $this->arguments = [
            'values' => $values,
            'safeOnly' => $safeOnly
        ];
    }
}

class ActiveRecordBinderTest extends TestCase
{
    /**
     * @var ActionParameterBinder
     */
    private $parameterBinder;

    /**
     * @var ModelBinderInterface
     */
    private $modelBinder;

    /**
     * @var BindingContext
     */
    private $context = null;

    protected function setUp()
    {
        parent::setUp();
        $this->parameterBinder = new ActionParameterBinder();
        $this->modelBinder = new ActiveRecordBinder();

        $this->mockWebApplication([
            'components' => [
            ],
        ]);
    }

    public function testActiveRecordBinder()
    {
        $target = TypeReflector::getBindingParameter(Post::class, "model", null);
        $context = new BindingContext(Yii::$app->request, $this->modelBinder, null, ["id" => 100]);

        $result = $this->modelBinder->bindModel($target, $context);

    }
}
