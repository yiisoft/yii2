<?php

namespace yiiunit\framework\email\swift;

use Yii;
use yii\email\swift\Mailer;
use yii\email\swift\Message;
use yiiunit\TestCase;

/**
 * @group email
 * @group swiftmailer
 */
class MessageTest extends TestCase
{
	public function setUp()
	{
		$this->mockApplication(array(
			'vendorPath' => Yii::getAlias('@yiiunit/vendor')
		));
		Yii::$app->setComponent('email', $this->createTestEmailComponent());
	}

	/**
	 * @return Mailer test email component instance.
	 */
	protected function createTestEmailComponent()
	{
		$component = new Mailer();
		return $component;
	}

	// Tests :

	public function testGetSwiftMessage()
	{
		$message = new Message();
		$this->assertTrue(is_object($message->getSwiftMessage()), 'Unable to get Swift message!');
	}
}