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