<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * Class ExpressionBuilder builds objects of [[yii\db\Expression]] class.
 *
 * @author Dmitry Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class ExpressionBuilder implements ExpressionBuilderInterface
{
    use ExpressionBuilderTrait;


    /**
     * {@inheritdoc}
     * @param Expression|ExpressionInterface $expression the expression to be built
     */
    public function build(ExpressionInterface $expression, array &$params = [])
    {
        $newParams = $expression->params;
        $newSql = $expression->__toString();
        $duplicateKeys = array_filter(
            $newParams,
            static fn($value, $key) => array_key_exists($key, $params) && $value !== $params[$key],
            ARRAY_FILTER_USE_BOTH
        );
        foreach (array_keys($duplicateKeys) as $duplicateKey) {
            $existingKey = array_search($newParams[$duplicateKey], $params, true);
            if ($existingKey !== false) {
                // we already have this value in our params, so just re-use it to avoid wasted space
                $newKey = $existingKey;
            } else {
                // use an arbitrary key to avoid clashing
                $suffix = count($params);
                do {
                    $newKey = $duplicateKey . $suffix++;
                } while (array_key_exists($newKey, $params) || array_key_exists($newKey, $newParams));
                $newParams[$newKey] = $newParams[$duplicateKey];
            }
            $newSql = preg_replace('/' . preg_quote($duplicateKey, '/') . '\b/', $newKey, $newSql);
            unset($newParams[$duplicateKey]);
        }
        $params = array_merge($params, $newParams);
        return $newSql;
    }
}
