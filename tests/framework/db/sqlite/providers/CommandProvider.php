<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\sqlite\providers;

use yii\db\Expression;
use yii\db\Query;

/**
 * Data provider for {@see \yiiunit\framework\db\sqlite\CommandTest} test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class CommandProvider
{
    /**
     * @return array<
     *   string,
     *   array{
     *     string,
     *     array<string, mixed>|Query,
     *     array<string, mixed>|Query,
     *     array<string, mixed>|bool,
     *     array<string, mixed>
     *   }
     * >
     */
    public static function upsert(): array
    {
        return [
            'no columns to update' => [
                'T_upsert_1',
                ['a' => 1],
                ['a' => 1],
                true,
                ['a' => 1],
            ],
            'query' => [
                'T_upsert',
                (new Query())
                    ->select(
                        [
                            'email',
                            'address',
                            'status' => new Expression('1'),
                        ],
                    )
                    ->from('customer')
                    ->where(['name' => 'user1'])
                    ->limit(1),
                (new Query())
                    ->select(
                        [
                            'email',
                            'address',
                            'status' => new Expression('2'),
                        ],
                    )
                    ->from('customer')
                    ->where(['name' => 'user1'])
                    ->limit(1),
                true,
                [
                    'email' => 'user1@example.com',
                    'address' => 'address1',
                    'status' => 2,
                ],
            ],
            'query with update part' => [
                'T_upsert',
                (new Query())
                    ->select(
                        [
                            'email',
                            'address',
                            'status' => new Expression('1'),
                        ],
                    )
                    ->from('customer')
                    ->where(['name' => 'user1'])
                    ->limit(1),
                (new Query())
                    ->select(
                        [
                            'email',
                            'address',
                            'status' => new Expression('3'),
                        ],
                    )
                    ->from('customer')
                    ->where(['name' => 'user1'])
                    ->limit(1),
                [
                    'address' => 'Moon',
                    'status' => 2,
                ],
                [
                    'email' => 'user1@example.com',
                    'address' => 'Moon',
                    'status' => 2,
                ],
            ],
            'query without update part' => [
                'T_upsert',
                (new Query())
                    ->select(
                        [
                            'email',
                            'address',
                            'status' => new Expression('1'),
                        ],
                    )
                    ->from('customer')
                    ->where(['name' => 'user1'])
                    ->limit(1),
                (new Query())
                    ->select(
                        [
                            'email',
                            'address',
                            'status' => new Expression('2'),
                        ],
                    )
                    ->from('customer')
                    ->where(['name' => 'user1'])
                    ->limit(1),
                false,
                [
                    'email' => 'user1@example.com',
                    'address' => 'address1',
                    'status' => 1,
                ],
            ],
            'regular values' => [
                'T_upsert',
                [
                    'email' => 'foo@example.com',
                    'address' => 'Earth',
                    'status' => 3,
                ],
                [
                    'email' => 'foo@example.com',
                    'address' => 'Universe',
                    'status' => 1,
                ],
                true,
                [
                    'email' => 'foo@example.com',
                    'address' => 'Universe',
                    'status' => 1,
                ],
            ],
            'regular values with update part' => [
                'T_upsert',
                [
                    'email' => 'foo@example.com',
                    'address' => 'Earth',
                    'status' => 3,
                ],
                [
                    'email' => 'foo@example.com',
                    'address' => 'Universe',
                    'status' => 1,
                ],
                [
                    'address' => 'Moon',
                    'status' => 2,
                ],
                [
                    'email' => 'foo@example.com',
                    'address' => 'Moon',
                    'status' => 2,
                ],
            ],
            'regular values without update part' => [
                'T_upsert',
                [
                    'email' => 'foo@example.com',
                    'address' => 'Earth',
                    'status' => 3,
                ],
                [
                    'email' => 'foo@example.com',
                    'address' => 'Universe',
                    'status' => 1,
                ],
                false,
                [
                    'email' => 'foo@example.com',
                    'address' => 'Earth',
                    'status' => 3,
                ],
            ],
        ];
    }
}
