<?php

namespace yiiunit\framework\mail;

use Yii;
use yii\base\View;
use yii\mail\BaseMailer;
use yii\mail\BaseMessage;
use yii\mail\ViewResolver;
use yii\helpers\FileHelper;
use yiiunit\TestCase;

/**
 * @group mail
 */
class BaseMailerTest extends TestCase
{
	public function setUp()
	{
		$this->mockApplication([
			'components' => [
				'mail' => $this->createTestEmailComponent()
			]
		]);
		$filePath = $this->getTestFilePath();
		if (!file_exists($filePath)) {
			FileHelper::createDirectory($filePath);
		}
	}

	public function tearDown()
	{
		$filePath = $this->getTestFilePath();
		if (file_exists($filePath)) {
			FileHelper::removeDirectory($filePath);
		}
	}

	/**
	 * @return string test file path.
	 */
	protected function getTestFilePath()
	{
		return Yii::getAlias('@yiiunit/runtime') . DIRECTORY_SEPARATOR . basename(get_class($this)) . '_' . getmypid();
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

	public function testSetupView()
	{
		$mailer = new Mailer();

		$view = new View();
		$mailer->setView($view);
		$this->assertEquals($view, $mailer->getView(), 'Unable to setup view!');

		$viewConfig = [
			'params' => [
				'param1' => 'value1',
				'param2' => 'value2',
			]
		];
		$mailer->setView($viewConfig);
		$view = $mailer->getView();
		$this->assertTrue(is_object($view), 'Unable to setup view via config!');
		$this->assertEquals($viewConfig['params'], $view->params, 'Unable to configure view via config array!');
	}

	/**
	 * @depends testSetupView
	 */
	public function testGetDefaultView()
	{
		$mailer = new Mailer();
		$view = $mailer->getView();
		$this->assertTrue(is_object($view), 'Unable to get default view!');
	}

	public function testSetupViewResolver()
	{
		$mailer = new Mailer();

		$viewResolver = new ViewResolver();
		$mailer->setViewResolver($viewResolver);
		$this->assertEquals($viewResolver, $mailer->getViewResolver(), 'Unable to setup view resolver!');

		$viewResolverConfig = [
			'viewPath' => '/test/view/path',
		];
		$mailer->setViewResolver($viewResolverConfig);
		$viewResolver = $mailer->getViewResolver();
		$this->assertTrue(is_object($viewResolver), 'Unable to setup view resolver via config!');
		$this->assertEquals($viewResolverConfig['viewPath'], $viewResolver->viewPath, 'Unable to configure view resolver via config array!');
	}

	/**
	 * @depends testSetupViewResolver
	 */
	public function testGetDefaultViewResolver()
	{
		$mailer = new Mailer();
		$viewResolver = $mailer->getViewResolver();
		$this->assertTrue(is_object($viewResolver), 'Unable to get default view resolver!');
	}

	public function testCreateMessage()
	{
		$mailer = new Mailer();
		$message = $mailer->createMessage();
		$this->assertTrue(is_object($message), 'Unable to create message instance!');
		$this->assertEquals($mailer->messageClass, get_class($message), 'Invalid message class!');

		$messageConfig = array(
			'id' => 'test-id',
			'encoding' => 'test-encoding',
		);
		$message = $mailer->createMessage($messageConfig);

		foreach ($messageConfig as $name => $value) {
			$this->assertEquals($value, $message->$name, 'Unable to apply message config!');
		}
	}

	/**
	 * @depends testCreateMessage
	 */
	public function testDefaultMessageConfig()
	{
		$mailer = new Mailer();

		$messageConfig = array(
			'id' => 'test-id',
			'encoding' => 'test-encoding',
		);
		$mailer->messageConfig = $messageConfig;

		$message = $mailer->createMessage();

		foreach ($messageConfig as $name => $value) {
			$this->assertEquals($value, $message->$name);
		}
	}

	/**
	 * @depends testGetDefaultView
	 * @depends testGetDefaultViewResolver
	 */
	public function testRender()
	{
		$mailer = new Mailer();

		$filePath = $this->getTestFilePath();
		$mailer->getViewResolver()->viewPath = $filePath;

		$viewName = 'test_view';
		$fileName = $filePath . DIRECTORY_SEPARATOR . $viewName . '.php';
		$fileContent = '<?php echo $testParam; ?>';
		file_put_contents($fileName, $fileContent);

		$params = [
			'testParam' => 'test output'
		];
		$renderResult = $mailer->render($viewName, $params);
		$this->assertEquals($params['testParam'], $renderResult);
	}
}

/**
 * Test Mailer class
 */
class Mailer extends BaseMailer
{
	public $messageClass = 'yiiunit\framework\mail\Message';
	public $sentMessages = array();

	public function send($message)
	{
		$this->sentMessages[] = $message;
	}
}

/**
 * Test Message class
 */
class Message extends BaseMessage
{
	public $id;
	public $encoding;

	public function setFrom($from) {}

	public function setTo($to) {}

	public function setCc($cc) {}

	public function setBcc($bcc) {}

	public function setSubject($subject) {}

	public function setText($text) {}

	public function setHtml($html) {}

	public function addText($text) {}

	public function addHtml($html) {}

	public function attachContentAsFile($content, $fileName, $contentType = 'application/octet-stream') {}

	public function __toString()
	{
		return get_class($this);
	}
}