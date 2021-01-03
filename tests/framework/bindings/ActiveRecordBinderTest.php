<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings;

use yii\bindings\ActionParameterBinder;
use yii\bindings\binders\ActiveRecordBinder;
use yii\bindings\BindingContext;
use yii\bindings\ModelBinderInterface;
use yiiunit\TestCase;

class ActiveRecordBinderTest extends TestCase
{
    /**
     * @var ActionParameterBinder
     */
    private $parameterBinder;

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
}
