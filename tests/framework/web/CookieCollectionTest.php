<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use DateTime;
use DateTimeImmutable;
use yii\web\Cookie;
use yii\web\CookieCollection;
use yiiunit\TestCase;

/**
 * @group web
 */
class CookieCollectionTest extends TestCase
{
    /**
     * @dataProvider provideHasData
     */
    public function testHas(string $name, array $cookies, bool $expectedResult): void
    {
        $cookieCollection = new CookieCollection($cookies);
        $result = $cookieCollection->has($name);

        $this->assertEquals($expectedResult, $result);
    }

    public static function provideHasData(): array
    {
        /**
         * Use absolute timestamps so the data set is not sensitive to the time elapsed between the data provider
         * evaluation and the actual test execution. Using `time() +/- 100` makes the "future" entries flip to "past" on
         * long-running suites (for example, full coverage runs that exceed `100` seconds) and thus cause random test
         * failures.
         */
        $pastTime = strtotime('2020-01-01 UTC');
        $futureTime = strtotime('2100-01-01 UTC');

        return [
            [
                'test',
                [],
                false,
            ],
            [
                'test',
                [
                    'abc' => new Cookie([
                        'value' => 'abc',
                    ]),
                ],
                false,
            ],
            [
                'test',
                [
                    'test' => new Cookie(),
                ],
                false,
            ],
            [
                'test',
                [
                    'test' => new Cookie([
                        'value' => 'test',
                        'expire' => null,
                    ]),
                ],
                true,
            ],
            [
                'test',
                [
                    'test' => new Cookie([
                        'value' => 'test',
                    ]),
                ],
                true,
            ],
            [
                'test',
                [
                    'test' => new Cookie([
                        'value' => 'test',
                    ]),
                ],
                true,
            ],
            [
                'test',
                [
                    'test' => new Cookie([
                        'value' => 'test',
                        'expire' => (string) $futureTime,
                    ]),
                ],
                true,
            ],
            [
                'test',
                [
                    'test' => new Cookie([
                        'value' => 'test',
                        'expire' => (string) $pastTime,
                    ]),
                ],
                false,
            ],
            [
                'test',
                [
                    'test' => new Cookie([
                        'value' => 'test',
                        'expire' => $futureTime,
                    ]),
                ],
                true,
            ],
            [
                'test',
                [
                    'test' => new Cookie([
                        'value' => 'test',
                        'expire' => $pastTime,
                    ]),
                ],
                false,
            ],
            [
                'test',
                [
                    'test' => new Cookie([
                        'value' => 'test',
                        'expire' => date('Y-m-d H:i:s', $futureTime),
                    ]),
                ],
                true,
            ],
            [
                'test',
                [
                    'test' => new Cookie([
                        'value' => 'test',
                        'expire' => date('Y-m-d', $pastTime),
                    ]),
                ],
                false,
            ],
            [
                'test',
                [
                    'test' => new Cookie([
                        'value' => 'test',
                        'expire' => (new DateTimeImmutable())->setTimestamp($futureTime),
                    ]),
                ],
                true,
            ],
            [
                'test',
                [
                    'test' => new Cookie([
                        'value' => 'test',
                        'expire' => (new DateTimeImmutable())->setTimestamp($pastTime),
                    ]),
                ],
                false,
            ],
            [
                'test',
                [
                    'test' => new Cookie([
                        'value' => 'test',
                        'expire' => (new DateTime())->setTimestamp($futureTime),
                    ]),
                ],
                true,
            ],
            [
                'test',
                [
                    'test' => new Cookie([
                        'value' => 'test',
                        'expire' => (new DateTime())->setTimestamp($pastTime),
                    ]),
                ],
                false,
            ],
        ];
    }
}
