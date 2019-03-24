<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\conditions;

/**
 * 条件基于列值对。
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class HashCondition implements ConditionInterface
{
    /**
     * @var array|null 条件规范。
     */
    private $hash;


    /**
     * HashCondition 构造函数。
     *
     * @param array|null $hash
     */
    public function __construct($hash)
    {
        $this->hash = $hash;
    }

    /**
     * @return array|null
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArrayDefinition($operator, $operands)
    {
        return new static($operands);
    }
}
