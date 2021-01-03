<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings;

use yii\bindings\binders\ContainerBinder;
use yii\bindings\BindingContext;
use yii\bindings\ModelBinderInterface;
use yiiunit\TestCase;

class ContainerBinderTest extends TestCase
{
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
        $this->modelBinder = new ContainerBinder();

        $this->mockWebApplication([
            'components' => [
            ],
        ]);
    }
}
