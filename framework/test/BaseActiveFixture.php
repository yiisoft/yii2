<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\test;

use yii\base\ArrayAccessTrait;
use yii\base\InvalidConfigException;

/**
 * BaseActiveFixture 是夹具基类用于支持以 ActiveRecord 对象的方式访问夹具数据。
 *
 * 更多关于 BaseActiveFixture 的使用信息，参考 [guide article on fixtures](guide:test-fixtures)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class BaseActiveFixture extends DbFixture implements \IteratorAggregate, \ArrayAccess, \Countable
{
    use ArrayAccessTrait;
    use FileFixtureTrait;

    /**
     * @var string 夹具关联的 AR 模型类
     */
    public $modelClass;
    /**
     * @var array 数据行。每个数组元素代表一行数据（形如：列名 => 列值）
     */
    public $data = [];

    /**
     * @var \yii\db\ActiveRecord[] 加载的 AR 模型
     */
    private $_models = [];


    /**
     * 根据模型名称返回 AR 模型对象
     * 一个模型名称是关联数组 [[data]] 的键。
     * @param string $name 模型名。
     * @return null|\yii\db\ActiveRecord AR 模型，如果数据库中不存在，返回 null。
     * @throws \yii\base\InvalidConfigException 如果 [[modelClass]] 不存在。
     */
    public function getModel($name)
    {
        if (!isset($this->data[$name])) {
            return null;
        }
        if (array_key_exists($name, $this->_models)) {
            return $this->_models[$name];
        }

        if ($this->modelClass === null) {
            throw new InvalidConfigException('The "modelClass" property must be set.');
        }
        $row = $this->data[$name];
        /* @var $modelClass \yii\db\ActiveRecord */
        $modelClass = $this->modelClass;
        $keys = [];
        foreach ($modelClass::primaryKey() as $key) {
            $keys[$key] = isset($row[$key]) ? $row[$key] : null;
        }

        return $this->_models[$name] = $modelClass::findOne($keys);
    }

    /**
     * 加载夹具。
     *
     * 这个方法的默认实现是简单的将 [[getData()]] 返回的数据存储在 [[data]] 属性中。
     * 你通常需要重写这个方法来把数据存入底层数据库。
     */
    public function load()
    {
        $this->data = $this->getData();
    }

    /**
     * 返回夹具数据。
     *
     * @return array 将要被填入数据库的数据。
     * @throws InvalidConfigException 指定的数据文件不存在。
     * @see [[loadData]]
     */
    protected function getData()
    {
        return $this->loadData($this->dataFile);
    }

    /**
     * {@inheritdoc}
     */
    public function unload()
    {
        parent::unload();
        $this->data = [];
        $this->_models = [];
    }
}
