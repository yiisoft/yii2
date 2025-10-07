<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\widgets\providers;

/**
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 *
 * @since 2.2.0
 */
final class ActiveFieldProvider
{
    /**
     * Provides test data for hint field rendering validation.
     *
     * This provider supplies test cases for validating hint content rendering in ActiveField widgets, including
     * scenarios with false values, null values, and custom hint content.
     *
     * @return array test data with hint configurations and expected HTML output.
     *
     * @phpstan-return array<array{bool|string|null, string}>
     */
    public static function hintDataProvider(): array
    {
        return [
            [
                false,
                '',
            ],
            [
                null,
                '<div class="hint-block">Hint for attributeName attribute</div>',
            ],
            [
                'Hint Content',
                '<div class="hint-block">Hint Content</div>',
            ],
        ];
    }
}
