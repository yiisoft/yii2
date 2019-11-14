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
 * ArrayFixture 代表任意一个可以通过PHP文件加载的夹具。
 *
 * 有关 ArrayFixture 更多细节和使用信息，参阅 [guide article on fixtures](guide:test-fixtures)
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
class ArrayFixture extends Fixture implements \IteratorAggregate, \ArrayAccess, \Countable
{
    use ArrayAccessTrait;
    use FileFixtureTrait;

    /**
     * @var array 数据行。数组的每个元素代表数据的一行（形如：列名 => 列值）。
     */
    public $data = [];


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
     * 这个方法的默认实现是尝试返回通过 [[dataFile]] 指定的文件中包含的外部夹具数据。
     * 这个外部文件需要返回数据数组，这个数组在插入数据库后，将被存储在 [[data]] 属性中。
     *
     * @return array 将要被插入数据库的数据。
     * @throws InvalidConfigException 如果指定的数据文件不存在。
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
    }
}
