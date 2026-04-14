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
 * The `*CheckVars` datasets exercise the `$checkVars` branch against a bare {@see \yiiunit\data\base\NewComponent}
 * (no behaviors attached), while the `*CheckBehaviors` datasets exercise the `$checkBehaviors` branch against
 * {@see \yiiunit\data\base\ComponentWithBehaviors} (with {@see \yiiunit\data\base\NewBehavior} attached).
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class ComponentProvider
{
    /**
     * @return array<string, array{string, bool, bool}>
     */
    public static function hasPropertyCheckVars(): array
    {
        return self::checkVarsMatrix();
    }

    /**
     * @return array<string, array{string, bool, bool}>
     */
    public static function canGetPropertyCheckVars(): array
    {
        return self::checkVarsMatrix();
    }

    /**
     * @return array<string, array{string, bool, bool}>
     */
    public static function canSetPropertyCheckVars(): array
    {
        return [
            ...self::checkVarsMatrix(),
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
    public static function hasPropertyCheckBehaviors(): array
    {
        return self::checkBehaviorsMatrix();
    }

    /**
     * @return array<string, array{string, bool, bool}>
     */
    public static function canGetPropertyCheckBehaviors(): array
    {
        return self::checkBehaviorsMatrix();
    }

    /**
     * @return array<string, array{string, bool, bool}>
     */
    public static function canSetPropertyCheckBehaviors(): array
    {
        return self::checkBehaviorsMatrix();
    }

    /**
     * @return array<string, array{string, bool, bool}>
     */
    private static function checkVarsMatrix(): array
    {
        return [
            'public property (PascalCase, case-sensitive miss)' => [
                'Content',
                true,
                false,
            ],
            'public property (lower-case, checkVars on)' => [
                'content',
                true,
                true,
            ],
            'public property (lower-case, checkVars off)' => [
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

    /**
     * @return array<string, array{string, bool, bool}>
     */
    private static function checkBehaviorsMatrix(): array
    {
        return [
            'behavior public property (checkBehaviors on)' => [
                'p',
                true,
                true,
            ],
            'behavior public property (checkBehaviors off)' => [
                'p',
                false,
                false,
            ],
            'behavior getter/setter property (checkBehaviors on)' => [
                'p2',
                true,
                true,
            ],
            'behavior getter/setter property (checkBehaviors off)' => [
                'p2',
                false,
                false,
            ],
            'non-existent property (checkBehaviors on)' => [
                'missing',
                true,
                false,
            ],
        ];
    }
}
