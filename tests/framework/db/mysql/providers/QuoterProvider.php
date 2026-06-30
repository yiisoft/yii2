<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql\providers;

/**
 * Data provider for {@see \yiiunit\framework\db\mysql\QuoterTest} test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class QuoterProvider
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function escapeLiteralValue(): array
    {
        return [
            'all specials combined' => ["\\'\"\n\r\x00\x1a", "\\\\\\'\\\"\\n\\r\\0\\Z"],
            'backslash' => ["C:\\dir\\file", "C:\\\\dir\\\\file"],
            'backslash before quote' => ["\\'", "\\\\\\'"],
            'carriage return' => ["a\rb", "a\\rb"],
            'ctrl-z' => ["end\x1amark", 'end\Zmark'],
            'double quote' => ['say "hi"', 'say \"hi\"'],
            'empty string' => ['', ''],
            'multiple single quotes' => ["a'b'c", "a\\'b\\'c"],
            'newline' => ["first\nsecond", "first\\nsecond"],
            'no special characters' => ['plain value', 'plain value'],
            'null byte' => ["a\x00b", "a\\0b"],
            'single quote' => ["O'Brien", "O\\'Brien"],
            'unicode content preserved' => ['déjà 🚀 vu', 'déjà 🚀 vu'],
        ];
    }
}
