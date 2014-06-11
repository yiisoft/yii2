<?php

namespace yiiunit\framework\helpers;

use Yii;
use yii\base\DynamicModel;
use yii\helpers\Html;
use yiiunit\TestCase;

/**
 * @group helpers
 */
class HtmlTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication([
            'components' => [
                'request' => [
                    'class' => 'yii\web\Request',
                    'url' => '/test',
                    'enableCsrfValidation' => false,
                ],
                'response' => [
                    'class' => 'yii\web\Response',
                ],
            ],
        ]);
    }

    public function assertEqualsWithoutLE($expected, $actual)
    {
        $expected = str_replace("\r\n", "\n", $expected);
        $actual = str_replace("\r\n", "\n", $actual);

        $this->assertEquals($expected, $actual);
    }

    public function testEncode()
    {
        $this->assertEquals("a&lt;&gt;&amp;&quot;&#039;�", Html::encode("a<>&\"'\x80"));
    }

    public function testDecode()
    {
        $this->assertEquals("a<>&\"'", Html::decode("a&lt;&gt;&amp;&quot;&#039;"));
    }

    public function testTag()
    {
        $this->assertEquals('<br>', Html::tag('br'));
        $this->assertEquals('<span></span>', Html::tag('span'));
        $this->assertEquals('<div>content</div>', Html::tag('div', 'content'));
        $this->assertEquals('<input type="text" name="test" value="&lt;&gt;">', Html::tag('input', '', ['type' => 'text', 'name' => 'test', 'value' => '<>']));
        $this->assertEquals('<span disabled></span>', Html::tag('span', '', ['disabled' => true]));
    }

    public function testBeginTag()
    {
        $this->assertEquals('<br>', Html::beginTag('br'));
        $this->assertEquals('<span id="test" class="title">', Html::beginTag('span', ['id' => 'test', 'class' => 'title']));
    }

    public function testEndTag()
    {
        $this->assertEquals('</br>', Html::endTag('br'));
        $this->assertEquals('</span>', Html::endTag('span'));
    }

    public function testStyle()
    {
        $content = 'a <>';
        $this->assertEquals("<style>$content</style>", Html::style($content));
        $this->assertEquals("<style type=\"text/less\">$content</style>", Html::style($content, ['type' => 'text/less']));
    }

    public function testScript()
    {
        $content = 'a <>';
        $this->assertEquals("<script>$content</script>", Html::script($content));
        $this->assertEquals("<script type=\"text/js\">$content</script>", Html::script($content, ['type' => 'text/js']));
    }

    public function testCssFile()
    {
        $this->assertEquals('<link href="http://example.com" rel="stylesheet">', Html::cssFile('http://example.com'));
        $this->assertEquals('<link href="/test" rel="stylesheet">', Html::cssFile(''));
        $this->assertEquals("<!--[if IE 9]>\n" . '<link href="http://example.com" rel="stylesheet">' . "\n<![endif]-->", Html::cssFile('http://example.com', ['condition' => 'IE 9']));
    }

    public function testJsFile()
    {
        $this->assertEquals('<script src="http://example.com"></script>', Html::jsFile('http://example.com'));
        $this->assertEquals('<script src="/test"></script>', Html::jsFile(''));
        $this->assertEquals("<!--[if IE 9]>\n" . '<script src="http://example.com"></script>' . "\n<![endif]-->", Html::jsFile('http://example.com', ['condition' => 'IE 9']));
    }

    public function testBeginForm()
    {
        $this->assertEquals('<form action="/test" method="post">', Html::beginForm());
        $this->assertEquals('<form action="/example" method="get">', Html::beginForm('/example', 'get'));
        $hiddens = [
            '<input type="hidden" name="id" value="1">',
            '<input type="hidden" name="title" value="&lt;">',
        ];
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
        $this->assertEquals('<img src="/example" alt="">', Html::img('/example'));
        $this->assertEquals('<img src="/test" alt="">', Html::img(''));
        $this->assertEquals('<img src="/example" width="10" alt="something">', Html::img('/example', ['alt' => 'something', 'width' => 10]));
    }

    public function testLabel()
    {
        $this->assertEquals('<label>something<></label>', Html::label('something<>'));
        $this->assertEquals('<label for="a">something<></label>', Html::label('something<>', 'a'));
        $this->assertEquals('<label class="test" for="a">something<></label>', Html::label('something<>', 'a', ['class' => 'test']));
    }

    public function testButton()
    {
        $this->assertEquals('<button>Button</button>', Html::button());
        $this->assertEquals('<button name="test" value="value">content<></button>', Html::button('content<>', ['name' => 'test', 'value' => 'value']));
        $this->assertEquals('<button type="submit" class="t" name="test" value="value">content<></button>', Html::button('content<>', ['type' => 'submit', 'name' => 'test', 'value' => 'value', 'class' => "t"]));
    }

    public function testSubmitButton()
    {
        $this->assertEquals('<button type="submit">Submit</button>', Html::submitButton());
        $this->assertEquals('<button type="submit" class="t" name="test" value="value">content<></button>', Html::submitButton('content<>', ['name' => 'test', 'value' => 'value', 'class' => 't']));
    }

    public function testResetButton()
    {
        $this->assertEquals('<button type="reset">Reset</button>', Html::resetButton());
        $this->assertEquals('<button type="reset" class="t" name="test" value="value">content<></button>', Html::resetButton('content<>', ['name' => 'test', 'value' => 'value', 'class' => 't']));
    }

    public function testInputId()
    {
        $model = new DynamicModel(['test', 'relation.name']);
        $this->assertEquals('<input type="text" id="dynamicmodel-test" name="DynamicModel[test]">', Html::activeTextInput($model, 'test'));
        $this->assertEquals('<input type="text" id="dynamicmodel-relation-name" name="DynamicModel[relation.name]">', Html::activeTextInput($model, 'relation.name'));
    }

    public function testInput()
    {
        $this->assertEquals('<input type="text">', Html::input('text'));
        $this->assertEquals('<input type="text" class="t" name="test" value="value">', Html::input('text', 'test', 'value', ['class' => 't']));
    }

    public function testButtonInput()
    {
        $this->assertEquals('<input type="button" value="Button">', Html::buttonInput());
        $this->assertEquals('<input type="button" class="a" name="test" value="text">', Html::buttonInput('text', ['name' => 'test', 'class' => 'a']));
    }

    public function testSubmitInput()
    {
        $this->assertEquals('<input type="submit" value="Submit">', Html::submitInput());
        $this->assertEquals('<input type="submit" class="a" name="test" value="text">', Html::submitInput('text', ['name' => 'test', 'class' => 'a']));
    }

    public function testResetInput()
    {
        $this->assertEquals('<input type="reset" value="Reset">', Html::resetInput());
        $this->assertEquals('<input type="reset" class="a" name="test" value="text">', Html::resetInput('text', ['name' => 'test', 'class' => 'a']));
    }

    public function testTextInput()
    {
        $this->assertEquals('<input type="text" name="test">', Html::textInput('test'));
        $this->assertEquals('<input type="text" class="t" name="test" value="value">', Html::textInput('test', 'value', ['class' => 't']));
    }

    public function testHiddenInput()
    {
        $this->assertEquals('<input type="hidden" name="test">', Html::hiddenInput('test'));
        $this->assertEquals('<input type="hidden" class="t" name="test" value="value">', Html::hiddenInput('test', 'value', ['class' => 't']));
    }

    public function testPasswordInput()
    {
        $this->assertEquals('<input type="password" name="test">', Html::passwordInput('test'));
        $this->assertEquals('<input type="password" class="t" name="test" value="value">', Html::passwordInput('test', 'value', ['class' => 't']));
    }

    public function testFileInput()
    {
        $this->assertEquals('<input type="file" name="test">', Html::fileInput('test'));
        $this->assertEquals('<input type="file" class="t" name="test" value="value">', Html::fileInput('test', 'value', ['class' => 't']));
    }

    public function testTextarea()
    {
        $this->assertEquals('<textarea name="test"></textarea>', Html::textarea('test'));
        $this->assertEquals('<textarea class="t" name="test">value&lt;&gt;</textarea>', Html::textarea('test', 'value<>', ['class' => 't']));
    }

    public function testRadio()
    {
        $this->assertEquals('<input type="radio" name="test" value="1">', Html::radio('test'));
        $this->assertEquals('<input type="radio" class="a" name="test" checked>', Html::radio('test', true, ['class' => 'a', 'value' => null]));
        $this->assertEquals('<input type="hidden" name="test" value="0"><input type="radio" class="a" name="test" value="2" checked>', Html::radio('test', true, ['class' => 'a', 'uncheck' => '0', 'value' => 2]));

        $this->assertEquals('<div class="radio"><label class="bbb"><input type="radio" class="a" name="test" checked> ccc</label></div>', Html::radio('test', true, [
            'class' => 'a',
            'value' => null,
            'label' => 'ccc',
            'labelOptions' => ['class' =>'bbb'],
        ]));
        $this->assertEquals('<input type="hidden" name="test" value="0"><div class="radio"><label><input type="radio" class="a" name="test" value="2" checked> ccc</label></div>', Html::radio('test', true, [
            'class' => 'a',
            'uncheck' => '0',
            'label' => 'ccc',
            'value' => 2,
        ]));
    }

    public function testCheckbox()
    {
        $this->assertEquals('<input type="checkbox" name="test" value="1">', Html::checkbox('test'));
        $this->assertEquals('<input type="checkbox" class="a" name="test" checked>', Html::checkbox('test', true, ['class' => 'a', 'value' => null]));
        $this->assertEquals('<input type="hidden" name="test" value="0"><input type="checkbox" class="a" name="test" value="2" checked>', Html::checkbox('test', true, ['class' => 'a', 'uncheck' => '0', 'value' => 2]));

        $this->assertEquals('<div class="checkbox"><label class="bbb"><input type="checkbox" class="a" name="test" checked> ccc</label></div>', Html::checkbox('test', true, [
            'class' => 'a',
            'value' => null,
            'label' => 'ccc',
            'labelOptions' => ['class' =>'bbb'],
        ]));
        $this->assertEquals('<input type="hidden" name="test" value="0"><div class="checkbox"><label><input type="checkbox" class="a" name="test" value="2" checked> ccc</label></div>', Html::checkbox('test', true, [
            'class' => 'a',
            'uncheck' => '0',
            'label' => 'ccc',
            'value' => 2,
        ]));
    }

    public function testDropDownList()
    {
        $expected = <<<EOD
<select name="test">

</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::dropDownList('test'));
        $expected = <<<EOD
<select name="test">
<option value="value1">text1</option>
<option value="value2">text2</option>
</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::dropDownList('test', null, $this->getDataItems()));
        $expected = <<<EOD
<select name="test">
<option value="value1">text1</option>
<option value="value2" selected>text2</option>
</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::dropDownList('test', 'value2', $this->getDataItems()));
    }

    public function testListBox()
    {
        $expected = <<<EOD
<select name="test" size="4">

</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::listBox('test'));
        $expected = <<<EOD
<select name="test" size="5">
<option value="value1">text1</option>
<option value="value2">text2</option>
</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::listBox('test', null, $this->getDataItems(), ['size' => 5]));
        $expected = <<<EOD
<select name="test" size="4">
<option value="value1&lt;&gt;">text1&lt;&gt;</option>
<option value="value  2">text  2</option>
</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::listBox('test', null, $this->getDataItems2()));
        $expected = <<<EOD
<select name="test" size="4">
<option value="value1&lt;&gt;">text1&lt;&gt;</option>
<option value="value  2">text&nbsp;&nbsp;2</option>
</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::listBox('test', null, $this->getDataItems2(), ['encodeSpaces' => true]));
        $expected = <<<EOD
<select name="test" size="4">
<option value="value1">text1</option>
<option value="value2" selected>text2</option>
</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::listBox('test', 'value2', $this->getDataItems()));
        $expected = <<<EOD
<select name="test" size="4">
<option value="value1" selected>text1</option>
<option value="value2" selected>text2</option>
</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::listBox('test', ['value1', 'value2'], $this->getDataItems()));

        $expected = <<<EOD
<select name="test[]" multiple size="4">

</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::listBox('test', null, [], ['multiple' => true]));
        $expected = <<<EOD
<input type="hidden" name="test" value="0"><select name="test" size="4">

</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::listBox('test', '', [], ['unselect' => '0']));
    }

    public function testCheckboxList()
    {
        $this->assertEquals('<div></div>', Html::checkboxList('test'));

        $expected = <<<EOD
<div><div class="checkbox"><label><input type="checkbox" name="test[]" value="value1"> text1</label></div>
<div class="checkbox"><label><input type="checkbox" name="test[]" value="value2" checked> text2</label></div></div>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::checkboxList('test', ['value2'], $this->getDataItems()));

        $expected = <<<EOD
<div><div class="checkbox"><label><input type="checkbox" name="test[]" value="value1&lt;&gt;"> text1&lt;&gt;</label></div>
<div class="checkbox"><label><input type="checkbox" name="test[]" value="value  2"> text  2</label></div></div>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::checkboxList('test', ['value2'], $this->getDataItems2()));

        $expected = <<<EOD
<input type="hidden" name="test" value="0"><div><div class="checkbox"><label><input type="checkbox" name="test[]" value="value1"> text1</label></div><br>
<div class="checkbox"><label><input type="checkbox" name="test[]" value="value2" checked> text2</label></div></div>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::checkboxList('test', ['value2'], $this->getDataItems(), [
            'separator' => "<br>\n",
            'unselect' => '0',
        ]));

        $expected = <<<EOD
<div>0<label>text1 <input type="checkbox" name="test[]" value="value1"></label>
1<label>text2 <input type="checkbox" name="test[]" value="value2" checked></label></div>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::checkboxList('test', ['value2'], $this->getDataItems(), [
            'item' => function ($index, $label, $name, $checked, $value) {
                return $index . Html::label($label . ' ' . Html::checkbox($name, $checked, ['value' => $value]));
            }
        ]));
    }

    public function testRadioList()
    {
        $this->assertEquals('<div></div>', Html::radioList('test'));

        $expected = <<<EOD
<div><div class="radio"><label><input type="radio" name="test" value="value1"> text1</label></div>
<div class="radio"><label><input type="radio" name="test" value="value2" checked> text2</label></div></div>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::radioList('test', ['value2'], $this->getDataItems()));

        $expected = <<<EOD
<div><div class="radio"><label><input type="radio" name="test" value="value1&lt;&gt;"> text1&lt;&gt;</label></div>
<div class="radio"><label><input type="radio" name="test" value="value  2"> text  2</label></div></div>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::radioList('test', ['value2'], $this->getDataItems2()));

        $expected = <<<EOD
<input type="hidden" name="test" value="0"><div><div class="radio"><label><input type="radio" name="test" value="value1"> text1</label></div><br>
<div class="radio"><label><input type="radio" name="test" value="value2" checked> text2</label></div></div>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::radioList('test', ['value2'], $this->getDataItems(), [
            'separator' => "<br>\n",
            'unselect' => '0',
        ]));

        $expected = <<<EOD
<div>0<label>text1 <input type="radio" name="test" value="value1"></label>
1<label>text2 <input type="radio" name="test" value="value2" checked></label></div>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::radioList('test', ['value2'], $this->getDataItems(), [
            'item' => function ($index, $label, $name, $checked, $value) {
                return $index . Html::label($label . ' ' . Html::radio($name, $checked, ['value' => $value]));
            }
        ]));
    }

    public function testUl()
    {
        $data = [
            1, 'abc', '<>',
        ];
        $expected = <<<EOD
<ul>
<li>1</li>
<li>abc</li>
<li>&lt;&gt;</li>
</ul>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::ul($data));
        $expected = <<<EOD
<ul class="test">
<li class="item-0">1</li>
<li class="item-1">abc</li>
<li class="item-2"><></li>
</ul>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::ul($data, [
            'class' => 'test',
            'item' => function ($item, $index) {
                return "<li class=\"item-$index\">$item</li>";
            }
        ]));
    }

    public function testOl()
    {
        $data = [
            1, 'abc', '<>',
        ];
        $expected = <<<EOD
<ol>
<li class="ti">1</li>
<li class="ti">abc</li>
<li class="ti">&lt;&gt;</li>
</ol>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::ol($data, [
            'itemOptions' => ['class' => 'ti'],
        ]));
        $expected = <<<EOD
<ol class="test">
<li class="item-0">1</li>
<li class="item-1">abc</li>
<li class="item-2"><></li>
</ol>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::ol($data, [
            'class' => 'test',
            'item' => function ($item, $index) {
                return "<li class=\"item-$index\">$item</li>";
            }
        ]));
    }

    public function testRenderOptions()
    {
        $data = [
            'value1' => 'label1',
            'group1' => [
                'value11' => 'label11',
                'group11' => [
                    'value111' => 'label111',
                ],
                'group12' => [],
            ],
            'value2' => 'label2',
            'group2' => [],
        ];
        $expected = <<<EOD
<option value="">please&nbsp;select&lt;&gt;</option>
<option value="value1" selected>label1</option>
<optgroup label="group1">
<option value="value11">label11</option>
<optgroup label="group11">
<option class="option" value="value111" selected>label111</option>
</optgroup>
<optgroup class="group" label="group12">

</optgroup>
</optgroup>
<option value="value2">label2</option>
<optgroup label="group2">

</optgroup>
EOD;
        $attributes = [
            'prompt' => 'please select<>',
            'options' => [
                'value111' => ['class' => 'option'],
            ],
            'groups' => [
                'group12' => ['class' => 'group'],
            ],
            'encodeSpaces' => true,
        ];
        $this->assertEqualsWithoutLE($expected, Html::renderSelectOptions(['value111', 'value1'], $data, $attributes));

        $attributes = [
            'prompt' => 'please select<>',
            'options' => [
                'value111' => ['class' => 'option'],
            ],
            'groups' => [
                'group12' => ['class' => 'group'],
            ],
        ];
        $this->assertEqualsWithoutLE(str_replace('&nbsp;', ' ', $expected), Html::renderSelectOptions(['value111', 'value1'], $data, $attributes));
    }

    public function testRenderAttributes()
    {
        $this->assertEquals('', Html::renderTagAttributes([]));
        $this->assertEquals(' name="test" value="1&lt;&gt;"', Html::renderTagAttributes(['name' => 'test', 'empty' => null, 'value' => '1<>']));
        $this->assertEquals(' checked disabled', Html::renderTagAttributes(['checked' => true, 'disabled' => true, 'hidden' => false]));
    }

    public function testAddCssClass()
    {
        $options = [];
        Html::addCssClass($options, 'test');
        $this->assertEquals(['class' => 'test'], $options);
        Html::addCssClass($options, 'test');
        $this->assertEquals(['class' => 'test'], $options);
        Html::addCssClass($options, 'test2');
        $this->assertEquals(['class' => 'test test2'], $options);
        Html::addCssClass($options, 'test');
        $this->assertEquals(['class' => 'test test2'], $options);
        Html::addCssClass($options, 'test2');
        $this->assertEquals(['class' => 'test test2'], $options);
        Html::addCssClass($options, 'test3');
        $this->assertEquals(['class' => 'test test2 test3'], $options);
        Html::addCssClass($options, 'test2');
        $this->assertEquals(['class' => 'test test2 test3'], $options);
    }

    public function testRemoveCssClass()
    {
        $options = ['class' => 'test test2 test3'];
        Html::removeCssClass($options, 'test2');
        $this->assertEquals(['class' => 'test test3'], $options);
        Html::removeCssClass($options, 'test2');
        $this->assertEquals(['class' => 'test test3'], $options);
        Html::removeCssClass($options, 'test');
        $this->assertEquals(['class' => 'test3'], $options);
        Html::removeCssClass($options, 'test3');
        $this->assertEquals([], $options);
    }

    public function testCssStyleFromArray()
    {
        $this->assertEquals('width: 100px; height: 200px;', Html::cssStyleFromArray([
            'width' => '100px',
            'height' => '200px',
        ]));
        $this->assertNull(Html::cssStyleFromArray([]));
    }

    public function testCssStyleToArray()
    {
        $this->assertEquals([
            'width' => '100px',
            'height' => '200px',
        ], Html::cssStyleToArray('width: 100px; height: 200px;'));
        $this->assertEquals([], Html::cssStyleToArray('  '));
    }

    public function testAddCssStyle()
    {
        $options = ['style' => 'width: 100px; height: 200px;'];
        Html::addCssStyle($options, 'width: 110px; color: red;');
        $this->assertEquals('width: 110px; height: 200px; color: red;', $options['style']);

        $options = ['style' => 'width: 100px; height: 200px;'];
        Html::addCssStyle($options, ['width' => '110px', 'color' => 'red']);
        $this->assertEquals('width: 110px; height: 200px; color: red;', $options['style']);

        $options = ['style' => 'width: 100px; height: 200px;'];
        Html::addCssStyle($options, 'width: 110px; color: red;', false);
        $this->assertEquals('width: 100px; height: 200px; color: red;', $options['style']);

        $options = [];
        Html::addCssStyle($options, 'width: 110px; color: red;');
        $this->assertEquals('width: 110px; color: red;', $options['style']);

        $options = [];
        Html::addCssStyle($options, 'width: 110px; color: red;', false);
        $this->assertEquals('width: 110px; color: red;', $options['style']);
    }

    public function testRemoveCssStyle()
    {
        $options = ['style' => 'width: 110px; height: 200px; color: red;'];
        Html::removeCssStyle($options, 'width');
        $this->assertEquals('height: 200px; color: red;', $options['style']);
        Html::removeCssStyle($options, ['height']);
        $this->assertEquals('color: red;', $options['style']);
        Html::removeCssStyle($options, ['color', 'background']);
        $this->assertNull($options['style']);

        $options = [];
        Html::removeCssStyle($options, ['color', 'background']);
        $this->assertTrue(!array_key_exists('style', $options));
    }

    public function testBooleanAttributes()
    {
        $this->assertEquals('<input type="email" name="mail">', Html::input('email', 'mail', null, ['required' => false]));
        $this->assertEquals('<input type="email" name="mail" required>', Html::input('email', 'mail', null, ['required' => true]));
        $this->assertEquals('<input type="email" name="mail" required="hi">', Html::input('email', 'mail', null, ['required' => 'hi']));
    }

    protected function getDataItems()
    {
        return [
            'value1' => 'text1',
            'value2' => 'text2',
        ];
    }

    protected function getDataItems2()
    {
        return [
            'value1<>' => 'text1<>',
            'value  2' => 'text  2',
        ];
    }
}
