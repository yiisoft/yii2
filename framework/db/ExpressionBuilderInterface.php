<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * 接口 ExpressionBuilderInterface 旨在从实现
 * [[ExpressionInterface]] 的特定表达式对象构建原始 SQL。
 *
 * @author Dmitry Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
interface ExpressionBuilderInterface
{
    /**
     * 通过 $expression 构建原始 SQL 的方法，
     * 表达式将不会额外的转义或引用。
     *
     * @param ExpressionInterface $expression 构建的表达式。
     * @param array $params 绑定参数。
     * @return string 不会被额外转义或引用的原始 SQL。
     */
    public function build(ExpressionInterface $expression, array &$params = []);
}
