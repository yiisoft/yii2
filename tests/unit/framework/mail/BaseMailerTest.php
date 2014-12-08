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
                'mailer' => $this->createTestMailComponent(),
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
        return Yii::$app->get('mailer');
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

    public function testCreateMessage()
    {
        $mailer = new Mailer();
        $message = $mailer->compose();
        $this->assertTrue(is_object($message), 'Unable to create message instance!');
        $this->assertEquals($mailer->messageClass, get_class($message), 'Invalid message class!');
    }

    /**
     * @depends testCreateMessage
     */
    public function testDefaultMessageConfig()
    {
        $mailer = new Mailer();

        $notPropertyConfig = [
            'charset' => 'utf-16',
            'from' => 'from@domain.com',
            'to' => 'to@domain.com',
            'cc' => 'cc@domain.com',
            'bcc' => 'bcc@domain.com',
            'subject' => 'Test subject',
            'textBody' => 'Test text body',
            'htmlBody' => 'Test HTML body',
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
     * @depends testRender
     */
    public function testRenderLayout()
    {
        $mailer = $this->getTestMailComponent();

        $filePath = $this->getTestFilePath();

        $viewName = 'test_view2';
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

    /**
     * @depends testCreateMessage
     * @depends testRender
     */
    public function testCompose()
    {
        $mailer = $this->getTestMailComponent();
        $mailer->htmlLayout = false;
        $mailer->textLayout = false;

        $htmlViewName = 'test_html_view';
        $htmlViewFileName = $this->getTestFilePath() . DIRECTORY_SEPARATOR . $htmlViewName . '.php';
        $htmlViewFileContent = 'HTML <b>view file</b> content';
        file_put_contents($htmlViewFileName, $htmlViewFileContent);

        $textViewName = 'test_text_view';
        $textViewFileName = $this->getTestFilePath() . DIRECTORY_SEPARATOR . $textViewName . '.php';
        $textViewFileContent = 'Plain text view file content';
        file_put_contents($textViewFileName, $textViewFileContent);

        $message = $mailer->compose([
            'html' => $htmlViewName,
            'text' => $textViewName,
        ]);
        $this->assertEquals($htmlViewFileContent, $message->_htmlBody, 'Unable to render html!');
        $this->assertEquals($textViewFileContent, $message->_textBody, 'Unable to render text!');

        $message = $mailer->compose($htmlViewName);
        $this->assertEquals($htmlViewFileContent, $message->_htmlBody, 'Unable to render html by direct view!');
        $this->assertEquals(strip_tags($htmlViewFileContent), $message->_textBody, 'Unable to render text by direct view!');
    }

    public function testUseFileTransport()
    {
        $mailer = new Mailer();
        $this->assertFalse($mailer->useFileTransport);
        $this->assertEquals('@runtime/mail', $mailer->fileTransportPath);

        $mailer->fileTransportPath = '@yiiunit/runtime/mail';
        $mailer->useFileTransport = true;
        $mailer->fileTransportCallback = function () {
            return 'message.txt';
        };
        $message = $mailer->compose()
            ->setTo('to@example.com')
            ->setFrom('from@example.com')
            ->setSubject('test subject')
            ->setTextBody('text body' . microtime(true));
        $this->assertTrue($mailer->send($message));
        $file = Yii::getAlias($mailer->fileTransportPath) . '/message.txt';
        $this->assertTrue(is_file($file));
        $this->assertEquals($message->toString(), file_get_contents($file));
    }

    public function testBeforeSendEvent()
    {
        $message = new Message();

        $mailerMock = $this->getMockBuilder('yiiunit\framework\mail\Mailer')->setMethods(['beforeSend', 'afterSend'])->getMock();
        $mailerMock->expects($this->once())->method('beforeSend')->with($message)->will($this->returnValue(true));
        $mailerMock->expects($this->once())->method('afterSend')->with($message, true);
        $mailerMock->send($message);
    }
}

/**
 * Test Mailer class
 */
class Mailer extends BaseMailer
{
    public $messageClass = 'yiiunit\framework\mail\Message';
    public $sentMessages = [];

    protected function sendMessage($message)
    {
        $this->sentMessages[] = $message;

        return true;
    }
}

/**
 * Test Message class
 */
class Message extends BaseMessage
{
    public $id;
    public $encoding;
    public $_charset;
    public $_from;
    public $_replyTo;
    public $_to;
    public $_cc;
    public $_bcc;
    public $_subject;
    public $_textBody;
    public $_htmlBody;

    public function getCharset()
    {
        return $this->_charset;
    }

    public function setCharset($charset)
    {
        $this->_charset = $charset;

        return $this;
    }

    public function getFrom()
    {
        return $this->_from;
    }

    public function setFrom($from)
    {
        $this->_from = $from;

        return $this;
    }

    public function getTo()
    {
        return $this->_to;
    }

    public function setTo($to)
    {
        $this->_to = $to;

        return $this;
    }

    public function getCc()
    {
        return $this->_cc;
    }

    public function setCc($cc)
    {
        $this->_cc = $cc;

        return $this;
    }

    public function getBcc()
    {
        return $this->_bcc;
    }

    public function setBcc($bcc)
    {
        $this->_bcc = $bcc;

        return $this;
    }

    public function getSubject()
    {
        return $this->_subject;
    }

    public function setSubject($subject)
    {
        $this->_subject = $subject;

        return $this;
    }

    public function getReplyTo()
    {
        return $this->_replyTo;
    }

    public function setReplyTo($replyTo)
    {
        $this->_replyTo = $replyTo;

        return $this;
    }

    public function setTextBody($text)
    {
        $this->_textBody = $text;

        return $this;
    }

    public function setHtmlBody($html)
    {
        $this->_htmlBody = $html;

        return $this;
    }

    public function attachContent($content, array $options = []) {}

    public function attach($fileName, array $options = []) {}

    public function embed($fileName, array $options = []) {}

    public function embedContent($content, array $options = []) {}

    public function toString()
    {
        $mailer = $this->mailer;
        $this->mailer = null;
        $s = var_export($this, true);
        $this->mailer = $mailer;
        return $s;
    }
}
