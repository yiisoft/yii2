<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\i18n;

use yiiunit\TestCase;

/**
 * Checks the translations in `framework/messages`.
 *
 * @group i18n
 */
final class FrameworkMessagesTest extends TestCase
{
    public function testWordsDoNotMixAlphabets(): void
    {
        $invalid = [];

        foreach (glob(__DIR__ . '/../../../framework/messages/*/yii.php') as $file) {
            $language = basename(dirname($file));

            foreach (require $file as $translation) {
                foreach (preg_split('/[^\p{L}\p{M}]+/u', $translation, -1, PREG_SPLIT_NO_EMPTY) as $word) {
                    $alphabets = preg_match('/(?![\p{Cyrillic}\p{Greek}])\p{Latin}/u', $word)
                        + preg_match('/(?![\p{Latin}\p{Greek}])\p{Cyrillic}/u', $word)
                        + preg_match('/(?![\p{Latin}\p{Cyrillic}])\p{Greek}/u', $word);

                    if ($alphabets > 1) {
                        $invalid[] = "$language: $word";
                    }
                }
            }
        }

        $this->assertSame([], array_values(array_unique($invalid)));
    }
}
