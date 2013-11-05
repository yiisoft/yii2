<?php

namespace yiiunit\framework\mail;

use Yii;
use yii\base\View;
use yii\mail\BaseMailer;
use yii\mail\BaseMessage;
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
				'mail' => $this->createTestMailComponent(),
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
	protected function createTestMailComponent()
	{
		$component = new Mailer();
		$component->viewPath = $this->getTestFilePath();
		return $component;
	}

	/**
	 * @return Mailer mailer instance
	 */
	protected function getTestMailComponent()
	{
		return Yii::$app->getComponent('mail');
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

	public function testComposeMessage()
	{
		$mailer = new Mailer();
		$message = $mailer->compose();
		$this->assertTrue(is_object($message), 'Unable to create message instance!');
		$this->assertEquals($mailer->messageClass, get_class($message), 'Invalid message class!');

		$messageConfig = array(
			'id' => 'test-id',
			'encoding' => 'test-encoding',
		);
		$message = $mailer->compose($messageConfig);

		foreach ($messageConfig as $name => $value) {
			$this->assertEquals($value, $message->$name, 'Unable to apply message config!');
		}
	}

	/**
	 * @depends testComposeMessage
	 */
	public function testDefaultMessageConfig()
	{
		$mailer = new Mailer();

		$notPropertyConfig = [
			'from' => 'from@domain.com',
			'to' => 'to@domain.com',
			'cc' => 'cc@domain.com',
			'bcc' => 'bcc@domain.com',
			'subject' => 'Test subject',
			'text' => 'Test text body',
			'html' => 'Test HTML body',
		];
		$propertyConfig = [
			'id' => 'test-id',
			'encoding' => 'test-encoding',
		];
		$messageConfig = array_merge($notPropertyConfig, $propertyConfig);
		$mailer->messageConfig = $messageConfig;

		$message = $mailer->compose();

		foreach ($notPropertyConfig as $name => $value) {
			$this->assertEquals($value, $message->{'_' . $name});
		}
		foreach ($propertyConfig as $name => $value) {
			$this->assertEquals($value, $message->$name);
		}
	}



	/**
	 * @depends testGetDefaultView
	 */
	public function testRender()
	{
		$mailer = $this->getTestMailComponent();

		$viewName = 'test_view';
		$viewFileName = $this->getTestFilePath() . DIRECTORY_SEPARATOR . $viewName . '.php';
		$viewFileContent = '<?php echo $testParam; ?>';
		file_put_contents($viewFileName, $viewFileContent);

		$params = [
			'testParam' => 'test output'
		];
		$renderResult = $mailer->render($viewName, $params);
		$this->assertEquals($params['testParam'], $renderResult);
	}

	/**
	 * @depends testComposeMessage
	 * @depends testRender
	 */
	public function testComposeSetupMethods()
	{
		$mailer = $this->getTestMailComponent();
		$mailer->textLayout = false;

		$viewName = 'test_view';
		$viewFileName = $this->getTestFilePath() . DIRECTORY_SEPARATOR . $viewName . '.php';
		$viewFileContent = 'view file content';
		file_put_contents($viewFileName, $viewFileContent);

		$messageConfig = array(
			'renderText' => [$viewName],
		);
		$message = $mailer->compose($messageConfig);

		$this->assertEquals($viewFileContent, $message->_text);
	}

	/**
	 * @depends testRender
	 */
	public function testRenderLayout()
	{
		$mailer = $this->getTestMailComponent();

		$filePath = $this->getTestFilePath();

		$viewName = 'test_view';
		$viewFileName = $filePath . DIRECTORY_SEPARATOR . $viewName . '.php';
		$viewFileContent = 'view file content';
		file_put_contents($viewFileName, $viewFileContent);

		$layoutName = 'test_layout';
		$layoutFileName = $filePath . DIRECTORY_SEPARATOR . $layoutName . '.php';
		$layoutFileContent = 'Begin Layout <?php echo $content; ?> End Layout';
		file_put_contents($layoutFileName, $layoutFileContent);

		$renderResult = $mailer->render($viewName, [], $layoutName);
		$this->assertEquals('Begin Layout ' . $viewFileContent . ' End Layout', $renderResult);
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
	public $_from;
	public $_to;
	public $_cc;
	public $_bcc;
	public $_subject;
	public $_text;
	public $_html;

	public function setCharset($charset) {}

	public function from($from) {
		$this->_from = $from;
		return $this;
	}

	public function to($to) {
		$this->_to = $to;
		return $this;
	}

	public function cc($cc) {
		$this->_cc = $cc;
		return $this;
	}

	public function bcc($bcc) {
		$this->_bcc = $bcc;
		return $this;
	}

	public function subject($subject) {
		$this->_subject = $subject;
		return $this;
	}

	public function text($text) {
		$this->_text = $text;
		return $this;
	}

	public function html($html) {
		$this->_html = $html;
		return $this;
	}

	public function attachContent($content, array $options = []) {}

	public function attachFile($fileName, array $options = []) {}

	public function embedFile($fileName, array $options = []) {}

	public function embedContent($content, array $options = []) {}

	public function __toString()
	{
		return get_class($this);
	}
}