<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\validators\providers;

/**
 * Data provider for {@see \yiiunit\framework\validators\ImageValidatorTest} test cases.
 */
final class ImageValidatorProvider
{
    /**
     * Provides validator configurations with one or more dimension limits, the expected result and the error message.
     *
     * @phpstan-return array<string, array{array<string, int>, bool, string|null}>
     */
    public static function dimensionLimits(): array
    {
        return [
            'width below minWidth' => [
                ['minWidth' => 4],
                false,
                'The image "rectangle.png" is too small. The width cannot be smaller than 4 pixels.',
            ],
            'width above maxWidth' => [
                ['maxWidth' => 2],
                false,
                'The image "rectangle.png" is too large. The width cannot be larger than 2 pixels.',
            ],
            'height below minHeight' => [
                ['minHeight' => 3],
                false,
                'The image "rectangle.png" is too small. The height cannot be smaller than 3 pixels.',
            ],
            'height above maxHeight' => [
                ['maxHeight' => 1],
                false,
                'The image "rectangle.png" is too large. The height cannot be larger than 1 pixel.',
            ],
            'dimensions on the exact bounds' => [
                ['minWidth' => 3, 'maxWidth' => 3, 'minHeight' => 2, 'maxHeight' => 2],
                true,
                null,
            ],
        ];
    }
}
