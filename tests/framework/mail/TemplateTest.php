<?php
/**
 * @link http://quartsoft.com/
 * @copyright Copyright &copy; 2015 QuartSoft ltd.
 * @license http://www.quartsoft.com/license/
 */

namespace yiiunit\framework\mail;

use Yii;
use yii\base\View;
use yii\helpers\FileHelper;
use yii\mail\BaseMessage;
use yii\mail\Template;
use yiiunit\TestCase;

class TemplateTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mockApplication();

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

        parent::tearDown();
    }

    /**
     * @return BaseMessage test mail message instance.
     */
    protected function createMessage()
    {
        return $this->getMock(BaseMessage::class, [
            'getCharset',
            'setCharset',
            'getFrom',
            'setFrom',
            'getTo',
            'setTo',
            'getReplyTo',
            'setReplyTo',
            'getCc',
            'setCc',
            'getBcc',
            'setBcc',
            'getSubject',
            'setSubject',
            'setTextBody',
            'setHtmlBody',
            'attach',
            'attachContent',
            'embed',
            'embedContent',
            'addHeader',
            'setHeader',
            'getHeader',
            'setHeaders',
        ]);
    }

    /**
     * @return View test view instance.
     */
    protected function createView()
    {
        return Yii::createObject(View::class);
    }

    /**
     * @return Template new mail template instance
     */
    protected function createTemplate()
    {
        $template = new Template();
        $template->view = $this->createView();
        $template->viewPath = $this->getTestFilePath();
        return $template;
    }

    /**
     * @return string test file path.
     */
    protected function getTestFilePath()
    {
        return Yii::getAlias('@yiiunit/runtime') . DIRECTORY_SEPARATOR . basename(get_class($this)) . '_' . getmypid();
    }

    // Tests :

    public function testRender()
    {
        $template = $this->createTemplate();

        $viewName = 'test_view';
        $viewFileName = $this->getTestFilePath() . DIRECTORY_SEPARATOR . $viewName . '.php';
        $viewFileContent = '<?php echo $testParam; ?>';
        file_put_contents($viewFileName, $viewFileContent);

        $params = [
            'testParam' => 'test output'
        ];
        $renderResult = $template->render($viewName, $params);
        $this->assertEquals($params['testParam'], $renderResult);
    }

    /**
     * @depends testRender
     */
    public function testRenderLayout()
    {
        $template = $this->createTemplate();

        $filePath = $this->getTestFilePath();

        $viewName = 'test_view2';
        $viewFileName = $filePath . DIRECTORY_SEPARATOR . $viewName . '.php';
        $viewFileContent = 'view file content';
        file_put_contents($viewFileName, $viewFileContent);

        $layoutName = 'test_layout';
        $layoutFileName = $filePath . DIRECTORY_SEPARATOR . $layoutName . '.php';
        $layoutFileContent = 'Begin Layout <?php echo $content; ?> End Layout';
        file_put_contents($layoutFileName, $layoutFileContent);

        $renderResult = $template->render($viewName, [], $layoutName);
        $this->assertEquals('Begin Layout ' . $viewFileContent . ' End Layout', $renderResult);
    }
}