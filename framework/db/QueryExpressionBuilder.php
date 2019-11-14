<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * 类 QueryExpressionBuilder 在内部用于使用统一的
 * [[QueryBuilder]] 表达式生成接口构建 [[Query]] 对象。
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class QueryExpressionBuilder implements ExpressionBuilderInterface
{
    use ExpressionBuilderTrait;


    /**
     * 通过 $expression 构建的原始 SQL，
     * 将不会被转译或引用。
     *
     * @param ExpressionInterface|Query $expression 要构建的表达式。
     * @param array $params 绑定参数。
     * @return string 不会被转义或引用的原始 SQL。
     */
    public function build(ExpressionInterface $expression, array &$params = [])
    {
        list($sql, $params) = $this->queryBuilder->build($expression, $params);

        return "($sql)";
    }
}
