<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\provider;

/**
 * Data provider for {@see \yiiunit\framework\base\ComponentTest} test cases.
 *
 * Provides representative input/output pairs for property accessibility checks.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class ComponentProvider
{
    /**
     * @return array<string, array{string, bool, bool}>
     */
    public static function hasProperty(): array
    {
        return self::basePropertyMatrix();
    }

    /**
     * @return array<string, array{string, bool, bool}>
     */
    public static function canGetProperty(): array
    {
        return self::basePropertyMatrix();
    }

    /**
     * @return array<string, array{string, bool, bool}>
     */
    public static function canSetProperty(): array
    {
        return [
            ...self::basePropertyMatrix(),
            'read-only property' => [
                'Object',
                true,
                false,
            ],
        ];
    }

    /**
     * @return array<string, array{string, bool, bool}>
     */
    private static function basePropertyMatrix(): array
    {
        return [
            'behavior property (PascalCase, case-sensitive)' => [
                'Content',
                true,
                false,
            ],
            'behavior property with behaviors' => [
                'content',
                true,
                true,
            ],
            'behavior property without behaviors' => [
                'content',
                false,
                false,
            ],
            'non-existent property' => [
                'Caption',
                true,
                false,
            ],
            'public property (camelCase)' => [
                'text',
                true,
                true,
            ],
            'public property (PascalCase)' => [
                'Text',
                true,
                true,
            ],
        ];
    }
}
