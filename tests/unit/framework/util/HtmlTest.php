<?php

namespace yiiunit\framework\util;

use Yii;
use yii\util\Html;
use yii\web\Application;

class HtmlTest extends \yii\test\TestCase
{
	public function setUp()
	{
		new Application('test', '@yiiunit/runtime', array(
			'components' => array(
				'request' => array(
					'class' => 'yii\web\Request',
					'url' => '/test',
				),
			),
		));
	}

	public function tearDown()
	{
		Yii::$app = null;
	}

	public function testEncode()
	{
		$this->assertEquals("a&lt;&gt;&amp;&quot;&#039;", Html::encode("a<>&\"'"));
	}

	public function testDecode()
	{
		$this->assertEquals("a<>&\"'", Html::decode("a&lt;&gt;&amp;&quot;&#039;"));
	}

	public function testTag()
	{
		$this->assertEquals('<br />', Html::tag('br'));
		$this->assertEquals('<span></span>', Html::tag('span'));
		$this->assertEquals('<div>content</div>', Html::tag('div', 'content'));
		$this->assertEquals('<input type="text" name="test" value="&lt;&gt;" />', Html::tag('input', '', array('type' => 'text', 'name' => 'test', 'value' => '<>')));

		Html::$closeVoidElements = false;

		$this->assertEquals('<br>', Html::tag('br'));
		$this->assertEquals('<span></span>', Html::tag('span'));
		$this->assertEquals('<div>content</div>', Html::tag('div', 'content'));
		$this->assertEquals('<input type="text" name="test" value="&lt;&gt;">', Html::tag('input', '', array('type' => 'text', 'name' => 'test', 'value' => '<>')));

		Html::$closeVoidElements = true;
	}

	public function testBeginTag()
	{
		$this->assertEquals('<br>', Html::beginTag('br'));
		$this->assertEquals('<span id="test" class="title">', Html::beginTag('span', array('id' => 'test', 'class' => 'title')));
	}

	public function testEndTag()
	{
		$this->assertEquals('</br>', Html::endTag('br'));
		$this->assertEquals('</span>', Html::endTag('span'));
	}

	public function testCdata()
	{
		$data = 'test<>';
		$this->assertEquals('<![CDATA[' . $data . ']]>', Html::cdata($data));
	}

	public function testStyle()
	{
		$content = 'a <>';
		$this->assertEquals("<style type=\"text/css\">/*<![CDATA[*/\n{$content}\n/*]]>*/</style>", Html::style($content));
		$this->assertEquals("<style type=\"text/less\">/*<![CDATA[*/\n{$content}\n/*]]>*/</style>", Html::style($content, array('type' => 'text/less')));
	}

	public function testScript()
	{
		$content = 'a <>';
		$this->assertEquals("<script type=\"text/javascript\">/*<![CDATA[*/\n{$content}\n/*]]>*/</script>", Html::script($content));
		$this->assertEquals("<script type=\"text/js\">/*<![CDATA[*/\n{$content}\n/*]]>*/</script>", Html::script($content, array('type' => 'text/js')));
	}

	public function testCssFile()
	{
		$this->assertEquals('<link type="text/css" href="http://example.com" rel="stylesheet" />', Html::cssFile('http://example.com'));
		$this->assertEquals('<link type="text/css" href="/test" rel="stylesheet" />', Html::cssFile(''));
	}

	public function testJsFile()
	{
		$this->assertEquals('<script type="text/javascript" src="http://example.com"></script>', Html::jsFile('http://example.com'));
		$this->assertEquals('<script type="text/javascript" src="/test"></script>', Html::jsFile(''));
	}

	public function testBeginForm()
	{
		$this->assertEquals('<form action="/test" method="post">', Html::beginForm());
		$this->assertEquals('<form action="/example" method="get">', Html::beginForm('/example', 'get'));
		$hiddens = array(
			'<input type="hidden" name="id" value="1" />',
			'<input type="hidden" name="title" value="&lt;" />',
		);
		$this->assertEquals('<form action="/example" method="get">' . "\n" . implode("\n", $hiddens), Html::beginForm('/example?id=1&title=%3C', 'get'));
	}

	public function testEndForm()
	{
		$this->assertEquals('</form>', Html::endForm());
	}

	public function testA()
	{
		$this->assertEquals('<a>something<></a>', Html::a('something<>'));
		$this->assertEquals('<a href="/example">something</a>', Html::a('something', '/example'));
		$this->assertEquals('<a href="/test">something</a>', Html::a('something', ''));
	}

	public function testMailto()
	{
		$this->assertEquals('<a href="mailto:test&lt;&gt;">test<></a>', Html::mailto('test<>'));
		$this->assertEquals('<a href="mailto:test&gt;">test<></a>', Html::mailto('test<>', 'test>'));
	}

	public function testImg()
	{
		$this->assertEquals('<img src="/example" alt="" />', Html::img('/example'));
		$this->assertEquals('<img src="/test" alt="" />', Html::img(''));
		$this->assertEquals('<img src="/example" width="10" alt="something" />', Html::img('/example', array('alt' => 'something', 'width' => 10)));
	}

	public function testLabel()
	{
		$this->assertEquals('<label>something<></label>', Html::label('something<>'));
		$this->assertEquals('<label for="a">something<></label>', Html::label('something<>', 'a'));
		$this->assertEquals('<label class="test" for="a">something<></label>', Html::label('something<>', 'a', array('class' => 'test')));
	}

	public function testButton()
	{
		$this->assertEquals('<button type="button">Button</button>', Html::button());
		$this->assertEquals('<button type="button" name="test" value="value">content<></button>', Html::button('test', 'value', 'content<>'));
		$this->assertEquals('<button type="submit" class="t" name="test" value="value">content<></button>', Html::button('test', 'value', 'content<>', array('type' => 'submit', 'class' => "t")));
	}

	public function testSubmitButton()
	{
		$this->assertEquals('<button type="submit">Submit</button>', Html::submitButton());
		$this->assertEquals('<button type="submit" class="t" name="test" value="value">content<></button>', Html::submitButton('test', 'value', 'content<>', array('class' => 't')));
	}

	public function testResetButton()
	{
		$this->assertEquals('<button type="reset">Reset</button>', Html::resetButton());
		$this->assertEquals('<button type="reset" class="t" name="test" value="value">content<></button>', Html::resetButton('test', 'value', 'content<>', array('class' => 't')));
	}

	public function testInput()
	{
		$this->assertEquals('<input type="text" />', Html::input('text'));
		$this->assertEquals('<input type="text" class="t" name="test" value="value" />', Html::input('text', 'test', 'value', array('class' => 't')));
	}

	public function testButtonInput()
	{
	}

	public function testSubmitInput()
	{
	}

	public function testResetInput()
	{
	}

	public function testTextInput()
	{
	}

	public function testHiddenInput()
	{
	}

	public function testPasswordInput()
	{
	}

	public function testFileInput()
	{
	}

	public function testTextarea()
	{
	}

	public function testRadio()
	{
	}

	public function testCheckbox()
	{
	}

	public function testDropDownList()
	{
	}

	public function testListBox()
	{
	}

	public function testCheckboxList()
	{
	}

	public function testRadioList()
	{
	}

	public function testRenderOptions()
	{
	}

	public function testRenderAttributes()
	{
	}

	public function testUrl()
	{
	}
}
