<?php

namespace yiiunit\framework\i18n;

use yii\i18n\GettextMoFile;
use yiiunit\TestCase;

/**
 * @group i18n
 */
class GettextMoFileTest extends TestCase
{
	public function testLoad()
	{
		$moFile = new GettextMoFile();
		$moFilePath = __DIR__ . '/../../data/i18n/test.mo';
		$context1 = $moFile->load($moFilePath, 'context1');
		$context2 = $moFile->load($moFilePath, 'context2');

		// item count
		$this->assertCount(3, $context1);
		$this->assertCount(2, $context2);

		// original messages
		$this->assertArrayNotHasKey("Missing\n\r\t\"translation.", $context1);
		$this->assertArrayHasKey("Aliquam tempus elit vel purus molestie placerat. In sollicitudin tincidunt\naliquet. Integer tincidunt gravida tempor. In convallis blandit dui vel malesuada.\nNunc vel sapien nunc, a pretium nulla.", $context1);
		$this->assertArrayHasKey("String number two.", $context1);
		$this->assertArrayHasKey("Nunc vel sapien nunc, a pretium nulla.\nPellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.", $context1);

		$this->assertArrayHasKey("The other\n\ncontext.\n", $context2);
		$this->assertArrayHasKey("test1\\ntest2\n\\\ntest3", $context2);

		// translated messages
		$this->assertFalse(in_array("", $context1));
		$this->assertTrue(in_array("Олицетворение однократно. Представленный лексико-семантический анализ является\nпсихолингвистическим в своей основе, но механизм сочленений полидисперсен. Впечатление\nоднократно. Различное расположение выбирает сюжетный механизм сочленений.", $context1));
		$this->assertTrue(in_array('Строка номер два.', $context1));
		$this->assertTrue(in_array('Короткий перевод.', $context1));

		$this->assertTrue(in_array("Другой\n\nконтекст.\n", $context2));
		$this->assertTrue(in_array("тест1\\nтест2\n\\\nтест3", $context2));
	}

	public function testSave()
	{
		// initial data
		$s = chr(4);
		$messages = array(
			'Hello!' => 'Привет!',
			"context1{$s}Hello?" => 'Привет?',
			'Hello!?' => '',
			"context1{$s}Hello!?!" => '',
			"context2{$s}\"Quotes\"" => '"Кавычки"',
			"context2{$s}\nNew lines\n" => "\nПереносы строк\n",
			"context2{$s}\tTabs\t" => "\tТабы\t",
			"context2{$s}\rCarriage returns\r" => "\rВозвраты кареток\r",
		);

		// create temporary directory and dump messages
		$poFileDirectory = __DIR__ . '/../../runtime/i18n';
		if (!is_dir($poFileDirectory)) {
			mkdir($poFileDirectory);
		}
		if (is_file($poFileDirectory . '/test.mo')) {
			unlink($poFileDirectory . '/test.mo');
		}

		$moFile = new GettextMoFile();
		$moFile->save($poFileDirectory . '/test.mo', $messages);

		// load messages
		$context1 = $moFile->load($poFileDirectory . '/test.mo', 'context1');
		$context2 = $moFile->load($poFileDirectory . '/test.mo', 'context2');

		// context1
		$this->assertCount(2, $context1);

		$this->assertArrayHasKey('Hello?', $context1);
		$this->assertTrue(in_array('Привет?', $context1));

		$this->assertArrayHasKey('Hello!?!', $context1);
		$this->assertTrue(in_array('', $context1));

		// context2
		$this->assertCount(4, $context2);

		$this->assertArrayHasKey("\"Quotes\"", $context2);
		$this->assertTrue(in_array('"Кавычки"', $context2));

		$this->assertArrayHasKey("\nNew lines\n", $context2);
		$this->assertTrue(in_array("\nПереносы строк\n", $context2));

		$this->assertArrayHasKey("\tTabs\t", $context2);
		$this->assertTrue(in_array("\tТабы\t", $context2));

		$this->assertArrayHasKey("\rCarriage returns\r", $context2);
		$this->assertTrue(in_array("\rВозвраты кареток\r", $context2));
	}
}
