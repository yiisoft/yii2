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

    public function testCountAndIterator(): void
    {
        $first = new Cookie(['name' => 'first', 'value' => '1']);
        $second = new Cookie(['name' => 'second', 'value' => '2']);

        $cookieCollection = new CookieCollection(['first' => $first, 'second' => $second]);

        $this->assertSame(2, count($cookieCollection), 'Countable must report the number of cookies.');
        $this->assertSame(
            ['first' => $first, 'second' => $second],
            iterator_to_array($cookieCollection),
            'Iterator must traverse the cookies indexed by their names.',
        );
    }

    public function testArrayAccess(): void
    {
        $first = new Cookie(['name' => 'first', 'value' => '1']);
        $second = new Cookie(['name' => 'second', 'value' => '2']);

        $cookieCollection = new CookieCollection(['first' => $first]);

        $this->assertTrue(isset($cookieCollection['first']), 'Existing cookie must be reported by isset().');
        $this->assertFalse(isset($cookieCollection['missing']), 'Missing cookie must not be reported by isset().');
        $this->assertSame($first, $cookieCollection['first'], 'Reading by offset must return the cookie.');
        $this->assertNull($cookieCollection['missing'], 'Reading a missing offset must return null.');

        $cookieCollection['second'] = $second;

        $this->assertSame($second, $cookieCollection->get('second'), 'Writing by offset must add the cookie.');

        unset($cookieCollection['second']);

        $this->assertFalse(isset($cookieCollection['second']), 'Unset cookie must not be reported by isset().');
        $this->assertSame(
            2,
            count($cookieCollection),
            'Unset cookie must stay in the collection as expired so that it is removed from the browser.',
        );
        $this->assertSame('', $cookieCollection['second']->value, 'Unset cookie must have an empty value.');
        $this->assertSame(1, $cookieCollection['second']->expire, 'Unset cookie must be expired.');
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
