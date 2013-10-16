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
		return array(
			array(
				'{'.self::SUBJECT.'} is {'.self::N.', number}', // pattern
				self::SUBJECT_VALUE.' is '.self::N_VALUE, // expected
				array( // params
					self::N => self::N_VALUE,
					self::SUBJECT => self::SUBJECT_VALUE,
				)
			),

			array(<<<_MSG_
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
				array(
					'gender_of_host' => 'male',
					'num_guests' => 4,
					'host' => 'ralph',
					'guest' => 'beep'
				)
			),

			array(
				'{name} is {gender} and {gender, select, female{she} male{he} other{it}} loves Yii!',
				'Alexander is male and he loves Yii!',
				array(
					'name' => 'Alexander',
					'gender' => 'male',
				),
			),

			// verify pattern in select does not get replaced
			array(
				'{name} is {gender} and {gender, select, female{she} male{he} other{it}} loves Yii!',
				'Alexander is male and he loves Yii!',
				array(
					'name' => 'Alexander',
					'gender' => 'male',
					 // following should not be replaced
					'he' => 'wtf',
					'she' => 'wtf',
					'it' => 'wtf',
				)
			),

			// verify pattern in select message gets replaced
			array(
				'{name} is {gender} and {gender, select, female{she} male{{he}} other{it}} loves Yii!',
				'Alexander is male and wtf loves Yii!',
				array(
					'name' => 'Alexander',
					'gender' => 'male',
					'he' => 'wtf',
					'she' => 'wtf',
				),
			),

			// some parser specific verifications
			array(
				'{gender} and {gender, select, female{she} male{{he}} other{it}} loves {nr, number} is {gender}!',
				'male and wtf loves 42 is male!',
				array(
					'nr' => 42,
					'gender' => 'male',
					'he' => 'wtf',
					'she' => 'wtf',
				),
			),
		);
	}

	/**
	 * @dataProvider patterns
	 */
	public function testNamedArgumentsStatic($pattern, $expected, $args)
	{
		$result = MessageFormatter::formatMessage('en_US', $pattern, $args);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @dataProvider patterns
	 */
	public function testNamedArgumentsObject($pattern, $expected, $args)
	{
		$formatter = new MessageFormatter('en_US', $pattern);
		$result = $formatter->format($args);
		$this->assertEquals($expected, $result);
	}

	public function testInsufficientArguments()
	{
		$expected = '{'.self::SUBJECT.'} is '.self::N_VALUE;

		$result = MessageFormatter::formatMessage('en_US', '{'.self::SUBJECT.'} is {'.self::N.', number}', array(
			self::N => self::N_VALUE,
		));

		$this->assertEquals($expected, $result);
	}

	/**
	 * when instantiating a MessageFormatter with invalid pattern it should be null
	 */
	public function testNullConstructor()
	{
		$this->assertNull(new MessageFormatter('en_US', ''));
	}

	public function testNoParams()
	{
		$pattern = '{'.self::SUBJECT.'} is '.self::N;
		$result = MessageFormatter::formatMessage('en_US', $pattern, array());
		$this->assertEquals($pattern, $result);

		$formatter = new MessageFormatter('en_US', $pattern);
		$result = $formatter->format(array());
		$this->assertEquals($pattern, $result);
	}
}