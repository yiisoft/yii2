<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mutex;

use yii\base\InvalidConfigException;
use yii\db\Connection;
use yii\di\Instance;

/**
 * DbMutex 是类的基类，它在实现互斥锁“锁定”机制时依赖于数据库。
 *
 * @see Mutex
 *
 * @author resurtm <resurtm@gmail.com>
 * @since 2.0
 */
abstract class DbMutex extends Mutex
{
    /**
     * @var Connection|array|string 数据库连接对象或数据库连接的应用程序组件ID。
     * 创建 Mutex 对象后，如果要更改此属性，
     * 则只应为其分配一个数据库连接对象。
     * 从版本 2.0.2 开始，这也可以是用于创建对象的配置数组。
     */
    public $db = 'db';


    /**
     * 初始化基于通用数据库表的互斥锁的实现。
     * @throws InvalidConfigException 如果 [[db]] 无效。
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
    }
}
