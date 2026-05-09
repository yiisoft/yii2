<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * Class PdoValueBuilder builds object of the [[PdoValue]] expression class.
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class PdoValueBuilder implements ExpressionBuilderInterface
{
    public const PARAM_PREFIX = ':pv';
    /**
     * {@inheritdoc}
     */
    public function build(ExpressionInterface $expression, array &$params = [])
    {
        $placeholder = static::PARAM_PREFIX . count($params);
        $params[$placeholder] = $expression;

        return $placeholder;
    }
}
