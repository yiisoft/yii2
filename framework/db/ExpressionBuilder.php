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
            static function ($key) use ($params) {
                $keyWithoutColon = ltrim((string)$key, ':');
                $keyWithColon = ':' . $keyWithoutColon;
                // the key could already exist with or without the leading colon, so look for both
                return (array_key_exists($keyWithoutColon, $params))
                    || (array_key_exists($keyWithColon, $params));
            },
            ARRAY_FILTER_USE_KEY
        );
        foreach (array_keys($duplicateKeys) as $duplicateKey) {
            $duplicateKeyWithoutColon = ltrim((string)$duplicateKey, ':');
            $duplicateKeyWithColon = ':' . $duplicateKeyWithoutColon;
            // use an arbitrary key to avoid clashing
            $suffix = count($params);
            do {
                $newKeyWithoutColon = $duplicateKeyWithoutColon . $suffix++;
                $newKey = ':' . $newKeyWithoutColon;
            } while (
                array_key_exists($newKey, $params)
                || array_key_exists($newKey, $newParams)
                || array_key_exists($newKeyWithoutColon, $params)
                || array_key_exists($newKeyWithoutColon, $newParams)
            );
            $newParams[$newKey] = $newParams[$duplicateKey];
            $newSql = preg_replace('/' . preg_quote($duplicateKeyWithColon, '/') . '\b/', $newKey, $newSql);
            unset($newParams[$duplicateKey]);
        }
        $params = array_merge($params, $newParams);
        return $newSql;
    }
}
