<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\oci;

use yii\db\ExpressionBuilderInterface;
use yii\db\ExpressionInterface;

use function array_key_exists;
use function count;

/**
 * Builds the SQL value expression for an {@see LobValue}.
 *
 * Registers the payload under a fresh locator placeholder and renders `EMPTY_BLOB()`.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class LobValueBuilder implements ExpressionBuilderInterface
{
    public const string PARAM_PREFIX = ':lob';

    /**
     * {@inheritdoc}
     *
     * @param LobValue $expression LOB value to build.
     */
    public function build(ExpressionInterface $expression, array &$params = [])
    {
        [$sql] = self::bind($expression, $params);

        return $sql;
    }

    /**
     * Registers the payload under a fresh locator placeholder.
     *
     * Returns the placeholder explicitly so callers building a `RETURNING` clause never infer it from `$params`.
     *
     * @param LobValue $expression LOB value to register.
     * @param array $params The parameters to be bound to the SQL statement. This parameter is passed by reference and
     * will be modified by this method.
     *
     * @return array{0: string, 1: string} SQL value expression and locator placeholder.
     */
    public static function bind(LobValue $expression, array &$params): array
    {
        $placeholder = self::PARAM_PREFIX . count($params);

        while (array_key_exists($placeholder, $params)) {
            $placeholder .= '_';
        }

        $params[$placeholder] = $expression;

        return ['EMPTY_BLOB()', $placeholder];
    }
}
