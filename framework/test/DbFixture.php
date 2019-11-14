<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\test;

use yii\base\BaseObject;
use yii\db\Connection;
use yii\di\Instance;

/**
 * DbFixture 是数据库相关的夹具基类。
 *
 * DbFixture 提供数据库连接对象 [[db]] 用于数据库相关的夹具。
 *
 * 关于 DbFixture 更多细节和使用信息，参阅 [guide article on fixtures](guide:test-fixtures)。
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class DbFixture extends Fixture
{
    /**
     * @var Connection|array|string 数据库对象，或者Yii应用数据库连接组件ID。
     * 在 DbFixture 创建之后，如果你想改变这个属性，你应该将一个 DB 连接对象赋值给它。
     * 从 2.0.2 开始，这个属性同样可以是一个可用于创建对象的配置数组。
     */
    public $db = 'db';


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, BaseObject::className());
    }
}
