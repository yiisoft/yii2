<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * 类 ExpressionBuilder 生成 [[yii\db\Expression]] 类的对象。
 *
 * @author Dmitry Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class ExpressionBuilder implements ExpressionBuilderInterface
{
    use ExpressionBuilderTrait;


    /**
     * {@inheritdoc}
     * @param Expression|ExpressionInterface $expression 构建的表达式
     */
    public function build(ExpressionInterface $expression, array &$params = [])
    {
        $params = array_merge($params, $expression->params);
        return $expression->__toString();
    }
}
