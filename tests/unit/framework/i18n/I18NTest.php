<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\i18n;

use yii\i18n\I18N;
use yii\i18n\MessageFormatter;
use yii\i18n\PhpMessageSource;
use yiiunit\TestCase;

/**
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 * @group i18n
 */
class I18NTest extends TestCase
{
	/**
	 * @var I18N
	 */
	public $i18n;

	protected function setUp()
	{
		parent::setUp();
		$this->mockApplication();
		$this->i18n = new I18N(array(
			'translations' => array(
				'test' => new PhpMessageSource(array(
					'basePath' => '@yiiunit/data/i18n/messages',
				))
			)
		));
	}

	public function testTranslate()
	{
		$msg = 'The dog runs fast.';
		$this->assertEquals('The dog runs fast.', $this->i18n->translate('test', $msg, array(), 'en_US'));
		$this->assertEquals('Der Hund rennt schnell.', $this->i18n->translate('test', $msg, array(), 'de_DE'));
	}

	public function testTranslateParams()
	{
		$msg = 'His speed is about {n} km/h.';
		$params = array(
			'n' => 42,
		);
		$this->assertEquals('His speed is about 42 km/h.', $this->i18n->translate('test', $msg, $params, 'en_US'));
		$this->assertEquals('Seine Geschwindigkeit beträgt 42 km/h.', $this->i18n->translate('test', $msg, $params, 'de_DE'));

		$msg = 'His name is {name} and his speed is about {n, number} km/h.';
		$params = array(
			'n' => 42,
			'name' => 'DA VINCI', // http://petrix.com/dognames/d.html
		);
		$this->assertEquals('His name is DA VINCI and his speed is about 42 km/h.', $this->i18n->translate('test', $msg, $params, 'en_US'));
		$this->assertEquals('Er heißt DA VINCI und ist 42 km/h schnell.', $this->i18n->translate('test', $msg, $params, 'de_DE'));
	}

}