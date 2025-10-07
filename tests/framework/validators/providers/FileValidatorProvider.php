<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\validators\providers;

/**
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 *
 * @since 2.2.0
 */
final class FileValidatorProvider
{
    /**
     * Provides invalid MIME type test cases for file validation.
     *
     * This provider supplies test cases with filenames, MIME type masks, and allowed extensions that are expected to
     * fail validation. It is used to ensure that the FileValidator correctly rejects files with mismatched MIME types
     * or extensions.
     *
     * @return array test data with filename, MIME type mask, and allowed extensions.
     *
     * @phpstan-return array<array-key, array{string, string, string}>
     */
    public static function invalidMimeTypes(): array
    {
        return [
            [
                'test.txt',
                'image/*',
                'png, jpg',
            ],
            [
                'test.odt',
                'text/*',
                'txt',
            ],
            [
                'test.xml',
                '*/svg+xml',
                'svg',
            ],
            [
                'test.png',
                'image/x-iso9660-image',
                'bmp',
            ],
            [
                'test.svg',
                'application/*',
                'jpg',
            ],
        ];
    }

    /**
     * Provides test cases for validating case-insensitive MIME type matching.
     *
     * This provider supplies pairs of MIME type masks and file MIME types, along with the expected validation result.
     * It is used to ensure that the FileValidator correctly handles MIME type comparisons regardless of case,
     * in accordance with RFC 2045 and RFC 2046, which specify that MIME types are case-insensitive.
     *
     * @return array test data with mask, file MIME type, and expected result.
     *
     * @phpstan-return array<array-key, array{string, string, bool}>
     */
    public static function mimeTypeCaseInsensitive(): array
    {
        return [
            [
                'Image/*',
                'image/jp2',
                true,
            ],
            [
                'image/*',
                'Image/jp2',
                true,
            ],
            [
                'application/vnd.ms-word.document.macroEnabled.12',
                'application/vnd.ms-word.document.macroenabled.12',
                true,
            ],
            [
                'image/jxra',
                'image/jxrA',
                true,
            ],
        ];
    }

    /**
     * Provides valid MIME type test cases for file validation.
     *
     * This provider supplies test cases with filenames, MIME type masks, and allowed extensions that are expected to
     * pass validation. It is used to ensure that the FileValidator correctly accepts files with matching MIME types
     * and extensions. Includes a conditional fix for a bundled libmagic bug affecting certain PHP versions.
     *
     * @return array test data with filename, MIME type mask, and allowed extensions.
     *
     * @phpstan-return array<array-key, array{string, string, string}>
     */
    public static function validMimeTypes(): array
    {
        $validMimeTypes = [
                [
                    'test.svg',
                    'image/*',
                    'svg',
                ],
                [
                    'test.jpg',
                    'image/*',
                    'jpg',
                ],
                [
                    'test.png',
                    'image/*',
                    'png',
                ],
                [
                    'test.png',
                    'IMAGE/*',
                    'png',
                ],
                [
                    'test.txt',
                    'text/*',
                    'txt',
                ],
                [
                    'test.xml',
                    '*/xml',
                    'xml',
                ],
                [
                    'test.odt',
                    'application/vnd*',
                    'odt',
                ],
                [
                    'test.tar.xz',
                    'application/x-xz',
                    'tar.xz',
                ],
        ];

        # fix for bundled libmagic bug, see also https://github.com/yiisoft/yii2/issues/19925
        if (PHP_VERSION_ID < 80122 || (PHP_VERSION_ID >= 80200 && PHP_VERSION_ID < 80209)) {
            $v81_zx = [
                'test.tar.xz',
                'application/octet-stream',
                'tar.xz',
            ];

            array_pop($validMimeTypes);

            $validMimeTypes[] = $v81_zx;
        }

        return $validMimeTypes;
    }
}
