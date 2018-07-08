<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\i18n;

use yii\i18n\MessageFormatter;
use yiiunit\TestCase;

/**
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 * @group i18n
 */
class MessageFormatterTest extends TestCase
{
    const N = 'n';
    const N_VALUE = 42;
    const F = 'f';
    const F_VALUE = 2e+8;
    const F_VALUE_FORMATTED = '200,000,000';
    const D = 'd';
    const D_VALUE = 200000000.101;
    const D_VALUE_FORMATTED = '200,000,000.101';
    const D_VALUE_FORMATTED_INTEGER = '200,000,000';
    const SUBJECT = 'сабж';
    const SUBJECT_VALUE = 'Answer to the Ultimate Question of Life, the Universe, and Everything';

    public function patterns()
    {
        return [
            [
                '{' . self::SUBJECT . '} is {' . self::N . ', number}', // pattern
                self::SUBJECT_VALUE . ' is ' . self::N_VALUE, // expected
                [ // params
                    self::N => self::N_VALUE,
                    self::SUBJECT => self::SUBJECT_VALUE,
                ],
            ],

            [
                '{' . self::SUBJECT . '} is {' . self::N . ', number, integer}', // pattern
                self::SUBJECT_VALUE . ' is ' . self::N_VALUE, // expected
                [ // params
                    self::N => self::N_VALUE,
                    self::SUBJECT => self::SUBJECT_VALUE,
                ],
            ],

            [
                'Here is a big number: {' . self::F . ', number}', // pattern
                'Here is a big number: ' . self::F_VALUE_FORMATTED, // expected
                [ // params
                    self::F => self::F_VALUE,
                ],
            ],


            [
                'Here is a big number: {' . self::F . ', number, integer}', // pattern
                'Here is a big number: ' . self::F_VALUE_FORMATTED, // expected
                [ // params
                    self::F => self::F_VALUE,
                ],
            ],

            [
                'Here is a big number: {' . self::D . ', number}', // pattern
                'Here is a big number: ' . self::D_VALUE_FORMATTED, // expected
                [ // params
                    self::D => self::D_VALUE,
                ],
            ],

            [
                'Here is a big number: {' . self::D . ', number, integer}', // pattern
                'Here is a big number: ' . self::D_VALUE_FORMATTED_INTEGER, // expected
                [ // params
                    self::D => self::D_VALUE,
                ],
            ],

            // This one was provided by Aura.Intl. Thanks!
            [<<<'_MSG_'
{gender_of_host, select,
  female {{num_guests, plural, offset:1
      =0 {{host} does not give a party.}
      =1 {{host} invites {guest} to her party.}
      =2 {{host} invites {guest} and one other person to her party.}
     other {{host} invites {guest} and # other people to her party.}}}
  male {{num_guests, plural, offset:1
      =0 {{host} does not give a party.}
      =1 {{host} invites {guest} to his party.}
      =2 {{host} invites {guest} and one other person to his party.}
     other {{host} invites {guest} and # other people to his party.}}}
  other {{num_guests, plural, offset:1
      =0 {{host} does not give a party.}
      =1 {{host} invites {guest} to their party.}
      =2 {{host} invites {guest} and one other person to their party.}
      other {{host} invites {guest} and # other people to their party.}}}}
_MSG_
                ,
                'ralph invites beep and 3 other people to his party.',
                [
                    'gender_of_host' => 'male',
                    'num_guests' => 4,
                    'host' => 'ralph',
                    'guest' => 'beep',
                ],
                defined('INTL_ICU_VERSION') && version_compare(INTL_ICU_VERSION, '4.8', '<'),
                'select format is available in ICU > 4.4 and plural format with =X selector is avilable since 4.8',
            ],

            [
                '{name} is {gender} and {gender, select, female{she} male{he} other{it}} loves Yii!',
                'Alexander is male and he loves Yii!',
                [
                    'name' => 'Alexander',
                    'gender' => 'male',
                ],
                defined('INTL_ICU_VERSION') && version_compare(INTL_ICU_VERSION, '4.4.2', '<'),
                'select format is available in ICU > 4.4',
            ],

            // verify pattern in select does not get replaced
            [
                '{name} is {gender} and {gender, select, female{she} male{he} other{it}} loves Yii!',
                'Alexander is male and he loves Yii!',
                [
                    'name' => 'Alexander',
                    'gender' => 'male',
                     // following should not be replaced
                    'he' => 'wtf',
                    'she' => 'wtf',
                    'it' => 'wtf',
                ],
                defined('INTL_ICU_VERSION') && version_compare(INTL_ICU_VERSION, '4.4.2', '<'),
                'select format is available in ICU > 4.4',
            ],

            // verify pattern in select message gets replaced
            [
                '{name} is {gender} and {gender, select, female{she} male{{he}} other{it}} loves Yii!',
                'Alexander is male and wtf loves Yii!',
                [
                    'name' => 'Alexander',
                    'gender' => 'male',
                    'he' => 'wtf',
                    'she' => 'wtf',
                ],
                defined('INTL_ICU_VERSION') && version_compare(INTL_ICU_VERSION, '4.8', '<'),
                'parameters in select format do not seem to work in ICU < 4.8',
            ],

            // some parser specific verifications
            [
                '{gender} and {gender, select, female{she} male{{he}} other{it}} loves {nr, number} is {gender}!',
                'male and wtf loves 42 is male!',
                [
                    'nr' => 42,
                    'gender' => 'male',
                    'he' => 'wtf',
                    'she' => 'wtf',
                ],
                defined('INTL_ICU_VERSION') && version_compare(INTL_ICU_VERSION, '4.4.2', '<'),
                'select format is available in ICU > 4.4',
            ],

            // formatting a message that contains params but they are not provided.
            [
                'Incorrect password (length must be from {min, number} to {max, number} symbols).',
                'Incorrect password (length must be from {min} to {max} symbols).',
                ['attribute' => 'password'],
            ],

            // test ICU version compatibility
            [
                'Showing <b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b> {totalCount, plural, one{item} other{items}}.',
                'Showing <b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b> {totalCount, plural, one{item} other{items}}.',
                [],
            ],
            [
                'Showing <b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b> {totalCount, plural, one{item} other{items}}.',
                'Showing <b>1-10</b> of <b>12</b> items.',
                [// A
                    'begin' => 1,
                    'end' => 10,
                    'count' => 10,
                    'totalCount' => 12,
                    'page' => 1,
                    'pageCount' => 2,
                ],
            ],
            [
                'Showing <b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b> {totalCount, plural, one{item} other{items}}.',
                'Showing <b>1-1</b> of <b>1</b> item.',
                [// B
                    'begin' => 1,
                    'end' => 1,
                    'count' => 1,
                    'totalCount' => 1,
                    'page' => 1,
                    'pageCount' => 1,
                ],
            ],
            [
                'Showing <b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b> {totalCount, plural, one{item} other{items}}.',
                'Showing <b>0-0</b> of <b>0</b> items.',
                [// C
                    'begin' => 0,
                    'end' => 0,
                    'count' => 0,
                    'totalCount' => 0,
                    'page' => 1,
                    'pageCount' => 1,
                ],
            ],
            [
                'Total <b>{count, number}</b> {count, plural, one{item} other{items}}.',
                'Total <b>{count, number}</b> {count, plural, one{item} other{items}}.',
                [],
            ],
            [
                'Total <b>{count, number}</b> {count, plural, one{item} other{items}}.',
                'Total <b>1</b> item.',
                [
                    'count' => 1,
                ],
            ],
            [
                'Total <b>{count, number}</b> {count, plural, one{item} other{items}}.',
                'Total <b>1</b> item.',
                [
                    'begin' => 5,
                    'count' => 1,
                    'end' => 10,
                ],
            ],
            [
                '{0, plural, one {offer} other {offers}}',
                '{0, plural, one {offer} other {offers}}',
                [],
            ],
            [
                '{0, plural, one {offer} other {offers}}',
                'offers',
                [0],
            ],
            [
                '{0, plural, one {offer} other {offers}}',
                'offer',
                [1],
            ],
            [
                '{0, plural, one {offer} other {offers}}',
                'offers',
                [13],
            ],
            [
                'Message without {closing} {brace',
                false, // Message pattern is invalid
                ['closing brace and with'],
            ],
            [
                '{gender, select, female{Уважаемая} other{Уважаемый}} {firstname},',
                'Уважаемый Vadim,',
                [
                    'gender' => null,
                    'firstname' => 'Vadim'
                ],
            ],
        ];
    }

    /**
     * @dataProvider patterns
     * @param string $pattern
     * @param string $expected
     * @param array $args
     * @param bool $skip
     * @param string $skipMessage
     */
    public function testNamedArguments($pattern, $expected, $args, $skip = false, $skipMessage = '')
    {
        if ($skip) {
            $this->markTestSkipped($skipMessage);
        }
        $formatter = new MessageFormatter();
        $result = $formatter->format($pattern, $args, 'en-US');
        $this->assertEquals($expected, $result, $formatter->getErrorMessage());
    }

    public function testInsufficientArguments()
    {
        $expected = '{' . self::SUBJECT . '} is ' . self::N_VALUE;

        $formatter = new MessageFormatter();
        $result = $formatter->format('{' . self::SUBJECT . '} is {' . self::N . ', number}', [
            self::N => self::N_VALUE,
        ], 'en-US');

        $this->assertEquals($expected, $result, $formatter->getErrorMessage());
    }

    public function testNoParams()
    {
        $pattern = '{' . self::SUBJECT . '} is ' . self::N;
        $formatter = new MessageFormatter();
        $result = $formatter->format($pattern, [], 'en-US');
        $this->assertEquals($pattern, $result, $formatter->getErrorMessage());
    }

    public function testMalformedFormatter()
    {
        $formatter = new MessageFormatter();
        $result = $formatter->format('{word,umber}', ['word' => 'test'], 'en-US'); // typo is intentional, message pattern should be invalid
        $this->assertFalse($result);
        $this->assertNotEmpty($formatter->getErrorMessage());
    }
}
