<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\i18n;

use Yii;
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

    /**
     * @dataProvider serbianMessageProvider
     */
    public function testSerbianPluralTranslations(string $message, array $params, string $expected): void
    {
        if (!extension_loaded('intl')) {
            $this->markTestSkipped('intl not installed. Skipping.');
        }

        $this->mockApplication();

        $this->assertSame($expected, Yii::t('yii', $message, $params, 'sr'));
    }

    public static function serbianMessageProvider(): array
    {
        return [
            'pagination uses one for 21 items' => [
                'Showing <b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b> '
                    . '{totalCount, plural, one{item} other{items}}.',
                ['begin' => 1, 'end' => 10, 'totalCount' => 21],
                'Приказано <b>1-10</b> од <b>21</b> ставке.',
            ],
            'upload limit uses few for two files' => [
                'You can upload at most {limit, number} {limit, plural, one{file} other{files}}.',
                ['limit' => 2],
                'Можете поставити највише 2 фајла.',
            ],
            'relative time uses few for two seconds' => [
                'in {delta, plural, =1{a second} other{# seconds}}',
                ['delta' => 2],
                'за 2 секунде',
            ],
        ];
    }
}
