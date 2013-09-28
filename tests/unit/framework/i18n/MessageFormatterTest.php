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

	public function testNamedArguments()
	{
		$expected = self::SUBJECT_VALUE.' is '.self::N_VALUE;

		$result = MessageFormatter::formatMessage('en_US', '{'.self::SUBJECT.'} is {'.self::N.', number}', array(
			self::N => self::N_VALUE,
			self::SUBJECT => self::SUBJECT_VALUE,
		));

		$this->assertEquals($expected, $result);

		$pattern = <<<_MSG_
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
_MSG_;
		$result = MessageFormatter::formatMessage('en_US', $pattern, array(
			'gender_of_host' => 'male',
			'num_guests' => 4,
			'host' => 'ralph',
			'guest' => 'beep'
		));
		$this->assertEquals('ralph invites beep and 3 other people to his party.', $result);

		$pattern = '{name} is {gender} and {gender, select, female{she} male{he} other{it}} loves Yii!';
		$result = MessageFormatter::formatMessage('en_US', $pattern, array(
			'name' => 'Alexander',
			'gender' => 'male',
		));
		$this->assertEquals('Alexander is male and he loves Yii!', $result);
	}

	public function testInsufficientArguments()
	{
		$expected = '{'.self::SUBJECT.'} is '.self::N_VALUE;

		$result = MessageFormatter::formatMessage('en_US', '{'.self::SUBJECT.'} is {'.self::N.', number}', array(
			self::N => self::N_VALUE,
		));

		$this->assertEquals($expected, $result);
	}
}