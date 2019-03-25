<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\conditions;

use yii\base\InvalidParamException;
use yii\db\ExpressionInterface;

/**
 * 接口 ConditionInterface 应该由表示框架的
 * DBAL 中的条件的类实现
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
interface ConditionInterface extends ExpressionInterface
{
    /**
     * 按照 [文档：查询构建器 - 操作符格式](guide:db-query-builder#operator-format) 文档中的描述，
     * 以数组定义创建对象。
     *
     * @param string $operator 操作符大写。
     * @param array $operands 相应操作数的数组。
     *
     * @return $this
     * @throws InvalidParamException 如果输入参数不匹配，则抛出 InvalidParamException 异常
     */
    public static function fromArrayDefinition($operator, $operands);
}
