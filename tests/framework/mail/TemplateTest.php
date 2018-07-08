<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\mail;

use Yii;
use yii\base\View;
use yii\helpers\FileHelper;
use yii\mail\BaseMessage;
use yii\mail\Template;
use yiiunit\data\mail\TestMessage;
use yiiunit\TestCase;

/**
 * @group mail
 */
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
        return new TestMessage();
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

    /**
     * @depends testRenderLayout
     */
    public function testCompose()
    {
        $template = $this->createTemplate();

        $template->htmlLayout = false;
        $template->textLayout = false;

        $htmlViewName = 'test_html_view';
        $htmlViewFileName = $this->getTestFilePath() . DIRECTORY_SEPARATOR . $htmlViewName . '.php';
        $htmlViewFileContent = 'HTML <b>view file</b> content';
        file_put_contents($htmlViewFileName, $htmlViewFileContent);

        $textViewName = 'test_text_view';
        $textViewFileName = $this->getTestFilePath() . DIRECTORY_SEPARATOR . $textViewName . '.php';
        $textViewFileContent = 'Plain text view file content';
        file_put_contents($textViewFileName, $textViewFileContent);

        $template->viewName = [
            'html' => $htmlViewName,
            'text' => $textViewName,
        ];
        $message = new TestMessage();
        $template->compose($message);
        $this->assertEquals($htmlViewFileContent, $message->_htmlBody, 'Unable to render html!');
        $this->assertEquals($textViewFileContent, $message->_textBody, 'Unable to render text!');

        $message = new TestMessage();
        $template->viewName = $htmlViewName;
        $template->compose($message);
        $this->assertEquals($htmlViewFileContent, $message->_htmlBody, 'Unable to render html by direct view!');
        $this->assertEquals(strip_tags($htmlViewFileContent), $message->_textBody, 'Unable to render text by direct view!');
    }

    public function htmlAndPlainProvider()
    {
        return [
            [
                'HTML <b>view file</b> content <a href="http://yiifresh.com/index.php?r=site%2Freset-password&amp;token=abcdef">http://yiifresh.com/index.php?r=site%2Freset-password&amp;token=abcdef</a>',
                'HTML view file content http://yiifresh.com/index.php?r=site%2Freset-password&token=abcdef',
            ],
            [
                <<<HTML
<html><head><style type="text/css">.content{color: #112345;}</style><title>TEST</title></head>
<body>
    <style type="text/css">.content{color: #112345;}</style>
    <p> First paragraph
    second line

     <a href="http://yiifresh.com/index.php?r=site%2Freset-password&amp;token=abcdef">http://yiifresh.com/index.php?r=site%2Freset-password&amp;token=abcdef</a>

     </p><script type="text/javascript">alert("hi")</script>

<p>Test Lorem ipsum...</p>
</body>
</html>
HTML
                ,<<<TEXT
First paragraph
second line

http://yiifresh.com/index.php?r=site%2Freset-password&token=abcdef

Test Lorem ipsum...
TEXT
            ],
        ];
    }

    /**
     * @dataProvider htmlAndPlainProvider
     * @depends testCompose
     *
     * @param string $htmlViewFileContent
     * @param string $expectedTextRendering
     */
    public function testComposePlainTextFallback($htmlViewFileContent, $expectedTextRendering)
    {
        $template = $this->createTemplate();

        $htmlViewName = 'test_html_view';
        $htmlViewFileName = $this->getTestFilePath() . DIRECTORY_SEPARATOR . $htmlViewName . '.php';
        file_put_contents($htmlViewFileName, $htmlViewFileContent);

        $template->viewName = $htmlViewName;

        $message = new TestMessage();
        $template->compose($message);
        $this->assertEqualsWithoutLE($htmlViewFileContent, $message->_htmlBody, 'Unable to render html!');
        $this->assertEqualsWithoutLE($expectedTextRendering, $message->_textBody, 'Unable to render text!');
    }
}