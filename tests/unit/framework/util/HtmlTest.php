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

		$this->assertEquals('<span disabled="disabled"></span>', Html::tag('span', '', array('disabled' => true)));
		Html::$showBooleanAttributeValues = false;
		$this->assertEquals('<span disabled></span>', Html::tag('span', '', array('disabled' => true)));
		Html::$showBooleanAttributeValues = true;
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
		$this->assertEquals('<input type="button" name="test" value="Button" />', Html::buttonInput('test'));
		$this->assertEquals('<input type="button" class="a" name="test" value="text" />', Html::buttonInput('test', 'text', array('class' => 'a')));
	}

	public function testSubmitInput()
	{
		$this->assertEquals('<input type="submit" value="Submit" />', Html::submitInput());
		$this->assertEquals('<input type="submit" class="a" name="test" value="text" />', Html::submitInput('test', 'text', array('class' => 'a')));
	}

	public function testResetInput()
	{
		$this->assertEquals('<input type="reset" value="Reset" />', Html::resetInput());
		$this->assertEquals('<input type="reset" class="a" name="test" value="text" />', Html::resetInput('test', 'text', array('class' => 'a')));
	}

	public function testTextInput()
	{
		$this->assertEquals('<input type="text" name="test" />', Html::textInput('test'));
		$this->assertEquals('<input type="text" class="t" name="test" value="value" />', Html::textInput('test', 'value', array('class' => 't')));
	}

	public function testHiddenInput()
	{
		$this->assertEquals('<input type="hidden" name="test" />', Html::hiddenInput('test'));
		$this->assertEquals('<input type="hidden" class="t" name="test" value="value" />', Html::hiddenInput('test', 'value', array('class' => 't')));
	}

	public function testPasswordInput()
	{
		$this->assertEquals('<input type="password" name="test" />', Html::passwordInput('test'));
		$this->assertEquals('<input type="password" class="t" name="test" value="value" />', Html::passwordInput('test', 'value', array('class' => 't')));
	}

	public function testFileInput()
	{
		$this->assertEquals('<input type="file" name="test" />', Html::fileInput('test'));
		$this->assertEquals('<input type="file" class="t" name="test" value="value" />', Html::fileInput('test', 'value', array('class' => 't')));
	}

	public function testTextarea()
	{
		$this->assertEquals('<textarea name="test"></textarea>', Html::textarea('test'));
		$this->assertEquals('<textarea class="t" name="test">value&lt;&gt;</textarea>', Html::textarea('test', 'value<>', array('class' => 't')));
	}

	public function testRadio()
	{
		$this->assertEquals('<input type="radio" name="test" value="1" />', Html::radio('test'));
		$this->assertEquals('<input type="radio" class="a" name="test" checked="checked" />', Html::radio('test', null, true, array('class' => 'a')));
		$this->assertEquals('<input type="hidden" name="test" value="0" /><input type="radio" class="a" name="test" checked="checked" />', Html::radio('test', null, true, array('class' => 'a' ,'uncheck' => '0')));
	}

	public function testCheckbox()
	{
		$this->assertEquals('<input type="checkbox" name="test" value="1" />', Html::checkbox('test'));
		$this->assertEquals('<input type="checkbox" class="a" name="test" checked="checked" />', Html::checkbox('test', null, true, array('class' => 'a')));
		$this->assertEquals('<input type="hidden" name="test" value="0" /><input type="checkbox" class="a" name="test" checked="checked" />', Html::checkbox('test', null, true, array('class' => 'a' ,'uncheck' => '0')));
	}

	public function testDropDownList()
	{
		$this->assertEquals("<select name=\"test\">\n\n</select>", Html::dropDownList('test'));
		$this->assertEquals("<select name=\"test\">\n<option value=\"value1\">text1</option>\n<option value=\"value2\">text2</option>\n</select>", Html::dropDownList('test', $this->getDataItems()));
		$this->assertEquals("<select name=\"test\">\n<option value=\"value1\">text1</option>\n<option value=\"value2\" selected=\"selected\">text2</option>\n</select>", Html::dropDownList('test', $this->getDataItems(), 'value2'));
	}

	public function testListBox()
	{
		$expected = <<<EOD
<select name="test" size="4">

</select>
EOD;
		$this->assertEquals($expected, Html::listBox('test'));
		$expected = <<<EOD
<select name="test" size="5">
<option value="value1">text1</option>
<option value="value2">text2</option>
</select>
EOD;
		$this->assertEquals($expected, Html::listBox('test', $this->getDataItems(), null, array('size' => 5)));
		$expected = <<<EOD
<select name="test" size="4">
<option value="value1&lt;&gt;">text1&lt;&gt;</option>
<option value="value  2">text&nbsp;&nbsp;2</option>
</select>
EOD;
		$this->assertEquals($expected, Html::listBox('test', $this->getDataItems2(), null));
		$expected = <<<EOD
<select name="test" size="4">
<option value="value1">text1</option>
<option value="value2" selected="selected">text2</option>
</select>
EOD;
		$this->assertEquals($expected, Html::listBox('test', $this->getDataItems(), 'value2'));
		$expected = <<<EOD
<select name="test" size="4">
<option value="value1" selected="selected">text1</option>
<option value="value2" selected="selected">text2</option>
</select>
EOD;
		$this->assertEquals($expected, Html::listBox('test', $this->getDataItems(), array('value1', 'value2')));

		$expected = <<<EOD
<select name="test[]" multiple="multiple" size="4">

</select>
EOD;
		$this->assertEquals($expected, Html::listBox('test',  array(), null, array('multiple' => true)));
		$expected = <<<EOD
<input type="hidden" name="test" value="0" /><select name="test" size="4">

</select>
EOD;
		$this->assertEquals($expected, Html::listBox('test', array(), '', array('unselect' => '0')));
	}

	public function testCheckboxList()
	{
		$this->assertEquals('', Html::checkboxList('test'));

		$expected = <<<EOD
<label><input type="checkbox" name="test[]" value="value1" /> text1</label>
<label><input type="checkbox" name="test[]" value="value2" checked="checked" /> text2</label>
EOD;
		$this->assertEquals($expected, Html::checkboxList('test', $this->getDataItems(), array('value2')));

		$expected = <<<EOD
<label><input type="checkbox" name="test[]" value="value1&lt;&gt;" /> text1<></label>
<label><input type="checkbox" name="test[]" value="value  2" /> text  2</label>
EOD;
		$this->assertEquals($expected, Html::checkboxList('test', $this->getDataItems2(), array('value2')));

		$expected = <<<EOD
<input type="hidden" name="test" value="0" /><label><input type="checkbox" name="test[]" value="value1" /> text1</label><br />
<label><input type="checkbox" name="test[]" value="value2" checked="checked" /> text2</label>
EOD;
		$this->assertEquals($expected, Html::checkboxList('test', $this->getDataItems(), array('value2'), array(
			'separator' => "<br />\n",
			'unselect' => '0',
		)));

		$expected = <<<EOD
<label>text1 <input type="checkbox" name="test[]" value="value1" /></label>
<label>text2 <input type="checkbox" name="test[]" value="value2" checked="checked" /></label>
EOD;
		$this->assertEquals($expected, Html::checkboxList('test', $this->getDataItems(), array('value2'), array(
			'item' => function ($index, $label, $name, $value, $checked) {
				return Html::label($label . ' ' . Html::checkbox($name, $value, $checked));
			}
		)));
	}

	public function testRadioList()
	{
		$this->assertEquals('', Html::radioList('test'));

		$expected = <<<EOD
<label><input type="radio" name="test" value="value1" /> text1</label>
<label><input type="radio" name="test" value="value2" checked="checked" /> text2</label>
EOD;
		$this->assertEquals($expected, Html::radioList('test', $this->getDataItems(), array('value2')));

		$expected = <<<EOD
<label><input type="radio" name="test" value="value1&lt;&gt;" /> text1<></label>
<label><input type="radio" name="test" value="value  2" /> text  2</label>
EOD;
		$this->assertEquals($expected, Html::radioList('test', $this->getDataItems2(), array('value2')));

		$expected = <<<EOD
<input type="hidden" name="test" value="0" /><label><input type="radio" name="test" value="value1" /> text1</label><br />
<label><input type="radio" name="test" value="value2" checked="checked" /> text2</label>
EOD;
		$this->assertEquals($expected, Html::radioList('test', $this->getDataItems(), array('value2'), array(
			'separator' => "<br />\n",
			'unselect' => '0',
		)));

		$expected = <<<EOD
<label>text1 <input type="radio" name="test" value="value1" /></label>
<label>text2 <input type="radio" name="test" value="value2" checked="checked" /></label>
EOD;
		$this->assertEquals($expected, Html::radioList('test', $this->getDataItems(), array('value2'), array(
			'item' => function ($index, $label, $name, $value, $checked) {
				return Html::label($label . ' ' . Html::radio($name, $value, $checked));
			}
		)));
	}

	public function testRenderOptions()
	{
		$this->assertEquals('', Html::renderOptions(array()));

		$data = array(
			'value1' => 'label1',
			'group1' => array(
				'value11' => 'label11',
				'group11' => array(
					'value111' => 'label111',
				),
				'group12' => array(),
			),
			'value2' => 'label2',
			'group2' => array(),
		);
		$expected = <<<EOD
<option value="">please&nbsp;select&lt;&gt;</option>
<option value="value1" selected="selected">label1</option>
<optgroup label="group1">
<option value="value11">label11</option>
<optgroup label="group11">
<option class="option" value="value111" selected="selected">label111</option>
</optgroup>
<optgroup class="group" label="group12">

</optgroup>
</optgroup>
<option value="value2">label2</option>
<optgroup label="group2">

</optgroup>
EOD;
		$attributes = array(
			'prompt' => 'please select<>',
			'options' => array(
				'value111' => array('class' => 'option'),
			),
			'groups' => array(
				'group12' => array('class' => 'group'),
			),
		);
		$this->assertEquals($expected, Html::renderOptions($data, array('value111', 'value1'), $attributes));
	}

	public function testRenderAttributes()
	{
		$this->assertEquals('', Html::renderAttributes(array()));
		$this->assertEquals(' name="test" value="1&lt;&gt;"', Html::renderAttributes(array('name' => 'test', 'empty' => null, 'value' => '1<>')));
		Html::$showBooleanAttributeValues = false;
		$this->assertEquals(' checked disabled', Html::renderAttributes(array('checked' => 'checked', 'disabled' => true, 'hidden' => false)));
		Html::$showBooleanAttributeValues = true;
	}

	protected function getDataItems()
	{
		return array(
			'value1' => 'text1',
			'value2' => 'text2',
		);
	}

	protected function getDataItems2()
	{
		return array(
			'value1<>' => 'text1<>',
			'value  2' => 'text  2',
		);
	}
}
