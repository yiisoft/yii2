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
	const SUBJECT = 'сабж';
	const SUBJECT_VALUE = 'Answer to the Ultimate Question of Life, the Universe, and Everything';

	public function patterns()
	{
		return [
			[
				'{'.self::SUBJECT.'} is {'.self::N.', number}', // pattern
				self::SUBJECT_VALUE.' is '.self::N_VALUE, // expected
				[ // params
					self::N => self::N_VALUE,
					self::SUBJECT => self::SUBJECT_VALUE,
				]
			],

			[
				'{'.self::SUBJECT.'} is {'.self::N.', number, integer}', // pattern
				self::SUBJECT_VALUE.' is '.self::N_VALUE, // expected
				[ // params
					self::N => self::N_VALUE,
					self::SUBJECT => self::SUBJECT_VALUE,
				]
			],

			// This one was provided by Aura.Intl. Thanks!
			[<<<_MSG_
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
					'guest' => 'beep'
				],
				defined('INTL_ICU_VERSION') && version_compare(INTL_ICU_VERSION, '4.8', '<'),
				'select format is available in ICU > 4.4 and plural format with =X selector is avilable since 4.8'
			],

			[
				'{name} is {gender} and {gender, select, female{she} male{he} other{it}} loves Yii!',
				'Alexander is male and he loves Yii!',
				[
					'name' => 'Alexander',
					'gender' => 'male',
				],
				defined('INTL_ICU_VERSION') && version_compare(INTL_ICU_VERSION, '4.4.2', '<'),
				'select format is available in ICU > 4.4'
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
				'select format is available in ICU > 4.4'
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
				'parameters in select format do not seem to work in ICU < 4.8'
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
				'select format is available in ICU > 4.4'
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
				]
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
				]
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
				]
			],
			[
				'Total <b>{count, number}</b> {count, plural, one{item} other{items}}.',
				'Total <b>{count, number}</b> {count, plural, one{item} other{items}}.',
				[]
			],
			[
				'Total <b>{count, number}</b> {count, plural, one{item} other{items}}.',
				'Total <b>1</b> item.',
				[
					'count' => 1,
				]
			],
			[
				'Total <b>{count, number}</b> {count, plural, one{item} other{items}}.',
				'Total <b>1</b> item.',
				[
					'begin' => 5,
					'count' => 1,
					'end' => 10,
				]
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
		];
	}

	public function parsePatterns()
	{
		return [
			[
				self::SUBJECT_VALUE.' is {0, number}', // pattern
				self::SUBJECT_VALUE.' is '.self::N_VALUE, // expected
				[ // params
					0 => self::N_VALUE,
				]
			],

			[
				self::SUBJECT_VALUE.' is {'.self::N.', number}', // pattern
				self::SUBJECT_VALUE.' is '.self::N_VALUE, // expected
				[ // params
					self::N => self::N_VALUE,
				]
			],

			[
				self::SUBJECT_VALUE.' is {'.self::N.', number, integer}', // pattern
				self::SUBJECT_VALUE.' is '.self::N_VALUE, // expected
				[ // params
					self::N => self::N_VALUE,
				]
			],

			[
				"{0,number,integer} monkeys on {1,number,integer} trees make {2,number} monkeys per tree",
				"4,560 monkeys on 123 trees make 37.073 monkeys per tree",
				[
					0 => 4560,
					1 => 123,
					2 => 37.073
				],
				'en-US'
			],

			[
				"{0,number,integer} Affen auf {1,number,integer} Bäumen sind {2,number} Affen pro Baum",
				"4.560 Affen auf 123 Bäumen sind 37,073 Affen pro Baum",
				[
					0 => 4560,
					1 => 123,
					2 => 37.073
				],
				'de',
			],

			[
				"{monkeyCount,number,integer} monkeys on {trees,number,integer} trees make {monkeysPerTree,number} monkeys per tree",
				"4,560 monkeys on 123 trees make 37.073 monkeys per tree",
				[
					'monkeyCount' => 4560,
					'trees' => 123,
					'monkeysPerTree' => 37.073
				],
				'en-US'
			],

			[
				"{monkeyCount,number,integer} Affen auf {trees,number,integer} Bäumen sind {monkeysPerTree,number} Affen pro Baum",
				"4.560 Affen auf 123 Bäumen sind 37,073 Affen pro Baum",
				[
					'monkeyCount' => 4560,
					'trees' => 123,
					'monkeysPerTree' => 37.073
				],
				'de',
			],
		];
	}

	/**
	 * @dataProvider patterns
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

	/**
	 * @dataProvider parsePatterns
	 */
	public function testParseNamedArguments($pattern, $expected, $args, $locale = 'en-US')
	{
		if (!extension_loaded("intl")) {
			$this->markTestSkipped("intl not installed. Skipping.");
		}

		$formatter = new MessageFormatter();
		$result = $formatter->parse($pattern, $expected, $locale);
		$this->assertEquals($args, $result, $formatter->getErrorMessage() . ' Pattern: ' . $pattern);
	}

	public function testInsufficientArguments()
	{
		$expected = '{'.self::SUBJECT.'} is '.self::N_VALUE;

		$formatter = new MessageFormatter();
		$result = $formatter->format('{'.self::SUBJECT.'} is {'.self::N.', number}', [
			self::N => self::N_VALUE,
		], 'en-US');

		$this->assertEquals($expected, $result, $formatter->getErrorMessage());
	}

	public function testNoParams()
	{
		$pattern = '{'.self::SUBJECT.'} is '.self::N;
		$formatter = new MessageFormatter();
		$result = $formatter->format($pattern, [], 'en-US');
		$this->assertEquals($pattern, $result, $formatter->getErrorMessage());
	}
}
