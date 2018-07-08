<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\helpers;

use Yii;
use yii\base\DynamicModel;
use yii\helpers\Html;
use yii\helpers\Url;
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
                    '__class' => \yii\web\Request::class,
                    'url' => '/test',
                    'scriptUrl' => '/index.php',
                    'hostInfo' => 'http://www.example.com',
                    'enableCsrfValidation' => false,
                ],
                'response' => [
                    '__class' => \yii\web\Response::class,
                ],
            ],
        ]);
    }

    public function testEncode()
    {
        $this->assertEquals('a&lt;&gt;&amp;&quot;&#039;�', Html::encode("a<>&\"'\x80"));
        $this->assertEquals('Sam &amp; Dark', Html::encode('Sam & Dark'));
    }

    public function testDecode()
    {
        $this->assertEquals("a<>&\"'", Html::decode('a&lt;&gt;&amp;&quot;&#039;'));
    }

    public function testTag()
    {
        $this->assertEquals('<br>', Html::tag('br'));
        $this->assertEquals('<span></span>', Html::tag('span'));
        $this->assertEquals('<div>content</div>', Html::tag('div', 'content'));
        $this->assertEquals('<input type="text" name="test" value="&lt;&gt;">', Html::tag('input', '', ['type' => 'text', 'name' => 'test', 'value' => '<>']));
        $this->assertEquals('<span disabled></span>', Html::tag('span', '', ['disabled' => true]));
        $this->assertEquals('test', Html::tag(false, 'test'));
        $this->assertEquals('test', Html::tag(null, 'test'));
    }

    public function testBeginTag()
    {
        $this->assertEquals('<br>', Html::beginTag('br'));
        $this->assertEquals('<span id="test" class="title">', Html::beginTag('span', ['id' => 'test', 'class' => 'title']));
        $this->assertEquals('', Html::beginTag(null));
        $this->assertEquals('', Html::beginTag(false));
    }

    public function testEndTag()
    {
        $this->assertEquals('</br>', Html::endTag('br'));
        $this->assertEquals('</span>', Html::endTag('span'));
        $this->assertEquals('', Html::endTag(null));
        $this->assertEquals('', Html::endTag(false));
    }

    public function testStyle()
    {
        $content = 'a <>';
        $this->assertEquals("<style>{$content}</style>", Html::style($content));
        $this->assertEquals("<style type=\"text/less\">{$content}</style>", Html::style($content, ['type' => 'text/less']));
    }

    public function testScript()
    {
        $content = 'a <>';
        $this->assertEquals("<script>{$content}</script>", Html::script($content));
        $this->assertEquals("<script type=\"text/js\">{$content}</script>", Html::script($content, ['type' => 'text/js']));
    }

    public function testCssFile()
    {
        $this->assertEquals('<link href="http://example.com" rel="stylesheet">', Html::cssFile('http://example.com'));
        $this->assertEquals('<link href="/test" rel="stylesheet">', Html::cssFile(''));
        $this->assertEquals("<!--[if IE 9]>\n" . '<link href="http://example.com" rel="stylesheet">' . "\n<![endif]-->", Html::cssFile('http://example.com', ['condition' => 'IE 9']));
        $this->assertEquals("<!--[if (gte IE 9)|(!IE)]><!-->\n" . '<link href="http://example.com" rel="stylesheet">' . "\n<!--<![endif]-->", Html::cssFile('http://example.com', ['condition' => '(gte IE 9)|(!IE)']));
        $this->assertEquals('<noscript><link href="http://example.com" rel="stylesheet"></noscript>', Html::cssFile('http://example.com', ['noscript' => true]));
    }

    public function testJsFile()
    {
        $this->assertEquals('<script src="http://example.com"></script>', Html::jsFile('http://example.com'));
        $this->assertEquals('<script src="/test"></script>', Html::jsFile(''));
        $this->assertEquals("<!--[if IE 9]>\n" . '<script src="http://example.com"></script>' . "\n<![endif]-->", Html::jsFile('http://example.com', ['condition' => 'IE 9']));
        $this->assertEquals("<!--[if (gte IE 9)|(!IE)]><!-->\n" . '<script src="http://example.com"></script>' . "\n<!--<![endif]-->", Html::jsFile('http://example.com', ['condition' => '(gte IE 9)|(!IE)']));
    }

    public function testCsrfMetaTagsDisableCsrfValidation()
    {
        $this->mockApplication([
            'components' => [
                'request' => [
                    '__class' => \yii\web\Request::class,
                    'enableCsrfValidation' => false,
                ],
            ],
        ]);
        $this->assertEquals('', Html::csrfMetaTags());
    }

    public function testCsrfMetaTagsEnableCsrfValidation()
    {
        $this->mockApplication([
            'components' => [
                'request' => [
                    '__class' => \yii\web\Request::class,
                    'enableCsrfValidation' => true,
                    'cookieValidationKey' => 'key',
                ],
                'response' => [
                    '__class' => \yii\web\Response::class,
                ],
            ],
        ]);
        $pattern = '<meta name="csrf-param" content="_csrf">%A<meta name="csrf-token" content="%s">';
        $actual = Html::csrfMetaTags();
        $this->assertStringMatchesFormat($pattern, $actual);
    }

    public function testCsrfMetaTagsEnableCsrfValidationWithoutCookieValidationKey()
    {
        $this->mockApplication([
            'components' => [
                'request' => [
                    '__class' => \yii\web\Request::class,
                    'enableCsrfValidation' => true,
                ],
            ],
        ]);
        $this->expectException(\yii\base\InvalidConfigException::class);
        $this->expectExceptionMessage('yii\web\Request::$cookieValidationKey must be configured with a secret key.');
        Html::csrfMetaTags();
    }

    /**
     * @dataProvider dataProviderBeginFormSimulateViaPost
     *
     * @param string $expected
     * @param string $method
     */
    public function testBeginFormSimulateViaPost($expected, $method)
    {
        $actual = Html::beginForm('/foo', $method);
        $this->assertStringMatchesFormat($expected, $actual);
    }

    /**
     * Data provider for [[testBeginFormSimulateViaPost()]].
     * @return array test data
     */
    public function dataProviderBeginFormSimulateViaPost()
    {
        return [
          ['<form action="/foo" method="GET">', 'GET'],
          ['<form action="/foo" method="POST">', 'POST'],
          ['<form action="/foo" method="post">%A<input type="hidden" name="_method" value="DELETE">', 'DELETE'],
          ['<form action="/foo" method="post">%A<input type="hidden" name="_method" value="GETFOO">', 'GETFOO'],
          ['<form action="/foo" method="post">%A<input type="hidden" name="_method" value="POSTFOO">', 'POSTFOO'],
          ['<form action="/foo" method="post">%A<input type="hidden" name="_method" value="POSTFOOPOST">', 'POSTFOOPOST'],
        ];
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

        $expected = '<form action="/foo" method="GET">%A<input type="hidden" name="p" value="">';
        $actual = Html::beginForm('/foo?p', 'GET');
        $this->assertStringMatchesFormat($expected, $actual);
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
        $this->assertEquals('<a href="http://www.быстроном.рф">http://www.быстроном.рф</a>', Html::a('http://www.быстроном.рф', 'http://www.быстроном.рф'));
        $this->assertEquals('<a href="https://www.example.com/index.php?r=site%2Ftest">Test page</a>', Html::a('Test page', Url::to(['/site/test'], 'https')));
    }

    public function testMailto()
    {
        $this->assertEquals('<a href="mailto:test&lt;&gt;">test<></a>', Html::mailto('test<>'));
        $this->assertEquals('<a href="mailto:test&gt;">test<></a>', Html::mailto('test<>', 'test>'));
    }

    /**
     * @return array
     */
    public function imgDataProvider()
    {
        return [
            [
                '<img src="/example" alt="">',
                '/example',
                [],
            ],
            [
                '<img src="/test" alt="">',
                '',
                [],
            ],
            [
                '<img src="/example" width="10" alt="something">',
                '/example',
                [
                    'alt' => 'something',
                    'width' => 10,
                ],
            ],
            [
                '<img src="/base-url" srcset="" alt="">',
                '/base-url',
                [
                    'srcset' => [
                    ],
                ],
            ],
            [
                '<img src="/base-url" srcset="/example-9001w 9001w" alt="">',
                '/base-url',
                [
                    'srcset' => [
                        '9001w' => '/example-9001w',
                    ],
                ],
            ],
            [
                '<img src="/base-url" srcset="/example-100w 100w,/example-500w 500w,/example-1500w 1500w" alt="">',
                '/base-url',
                [
                    'srcset' => [
                        '100w' => '/example-100w',
                        '500w' => '/example-500w',
                        '1500w' => '/example-1500w',
                    ],
                ],
            ],
            [
                '<img src="/base-url" srcset="/example-1x 1x,/example-2x 2x,/example-3x 3x,/example-4x 4x,/example-5x 5x" alt="">',
                '/base-url',
                [
                    'srcset' => [
                        '1x' => '/example-1x',
                        '2x' => '/example-2x',
                        '3x' => '/example-3x',
                        '4x' => '/example-4x',
                        '5x' => '/example-5x',
                    ],
                ],
            ],
            [
                '<img src="/base-url" srcset="/example-1.42x 1.42x,/example-2.0x 2.0x,/example-3.99999x 3.99999x" alt="">',
                '/base-url',
                [
                    'srcset' => [
                        '1.42x' => '/example-1.42x',
                        '2.0x' => '/example-2.0x',
                        '3.99999x' => '/example-3.99999x',
                    ],
                ],
            ],
            [
                '<img src="/base-url" srcset="/example-1x 1x,/example-2x 2x,/example-3x 3x" alt="">',
                '/base-url',
                [
                    'srcset' => '/example-1x 1x,/example-2x 2x,/example-3x 3x',
                ],
            ],
        ];
    }

    /**
     * @dataProvider imgDataProvider
     * @param string $expected
     * @param string $src
     * @param array $options
     */
    public function testImg($expected, $src, $options)
    {
        $this->assertEquals($expected, Html::img($src, $options));
    }

    public function testLabel()
    {
        $this->assertEquals('<label>something<></label>', Html::label('something<>'));
        $this->assertEquals('<label for="a">something<></label>', Html::label('something<>', 'a'));
        $this->assertEquals('<label class="test" for="a">something<></label>', Html::label('something<>', 'a', ['class' => 'test']));
    }

    public function testButton()
    {
        $this->assertEquals('<button type="button">Button</button>', Html::button());
        $this->assertEquals('<button type="button" name="test" value="value">content<></button>', Html::button('content<>', ['name' => 'test', 'value' => 'value']));
        $this->assertEquals('<button type="submit" class="t" name="test" value="value">content<></button>', Html::button('content<>', ['type' => 'submit', 'name' => 'test', 'value' => 'value', 'class' => 't']));
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

    /**
     * @return array
     */
    public function textareaDataProvider()
    {
        return [
            [
                '<textarea name="test"></textarea>',
                'test',
                null,
                [],
            ],
            [
                '<textarea class="t" name="test">value&lt;&gt;</textarea>',
                'test',
                'value<>',
                ['class' => 't'],
            ],
            [
                '<textarea name="test">value&amp;lt;&amp;gt;</textarea>',
                'test',
                'value&lt;&gt;',
                [],
            ],
            [
                '<textarea name="test">value&lt;&gt;</textarea>',
                'test',
                'value&lt;&gt;',
                ['doubleEncode' => false],
            ],
        ];
    }

    /**
     * @dataProvider textareaDataProvider
     * @param string $expected
     * @param string $name
     * @param string $value
     * @param array $options
     */
    public function testTextarea($expected, $name, $value, $options)
    {
        $this->assertEquals($expected, Html::textarea($name, $value, $options));
    }

    public function testRadio()
    {
        $this->assertEquals('<input type="radio" name="test" value="1">', Html::radio('test'));
        $this->assertEquals('<input type="radio" class="a" name="test" checked>', Html::radio('test', true, ['class' => 'a', 'value' => null]));
        $this->assertEquals('<input type="hidden" name="test" value="0"><input type="radio" class="a" name="test" value="2" checked>', Html::radio('test', true, ['class' => 'a', 'uncheck' => '0', 'value' => 2]));

        $this->assertEquals('<label class="bbb"><input type="radio" class="a" name="test" checked> ccc</label>', Html::radio('test', true, [
            'class' => 'a',
            'value' => null,
            'label' => 'ccc',
            'labelOptions' => ['class' => 'bbb'],
        ]));
        $this->assertEquals('<input type="hidden" name="test" value="0"><label><input type="radio" class="a" name="test" value="2" checked> ccc</label>', Html::radio('test', true, [
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

        $this->assertEquals('<label class="bbb"><input type="checkbox" class="a" name="test" checked> ccc</label>', Html::checkbox('test', true, [
            'class' => 'a',
            'value' => null,
            'label' => 'ccc',
            'labelOptions' => ['class' => 'bbb'],
        ]));
        $this->assertEquals('<input type="hidden" name="test" value="0"><label><input type="checkbox" class="a" name="test" value="2" checked> ccc</label>', Html::checkbox('test', true, [
            'class' => 'a',
            'uncheck' => '0',
            'label' => 'ccc',
            'value' => 2,
        ]));
        $this->assertEquals('<input type="hidden" name="test" value="0" form="test-form"><label><input type="checkbox" class="a" name="test" value="2" form="test-form" checked> ccc</label>', Html::checkbox('test', true, [
            'class' => 'a',
            'uncheck' => '0',
            'label' => 'ccc',
            'value' => 2,
            'form' => 'test-form',
        ]));
    }

    public function testDropDownList()
    {
        $expected = <<<'EOD'
<select name="test">

</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::dropDownList('test'));
        $expected = <<<'EOD'
<select name="test">
<option value="value1">text1</option>
<option value="value2">text2</option>
</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::dropDownList('test', null, $this->getDataItems()));
        $expected = <<<'EOD'
<select name="test">
<option value="value1">text1</option>
<option value="value2" selected>text2</option>
</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::dropDownList('test', 'value2', $this->getDataItems()));

        $expected = <<<'EOD'
<select name="test">
<option value="value1">text1</option>
<option value="value2" selected>text2</option>
</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::dropDownList('test', null, $this->getDataItems(), [
            'options' => [
                'value2' => ['selected' => true],
            ],
        ]));

        $expected = <<<'EOD'
<select name="test[]" multiple="true" size="4">

</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::dropDownList('test', null, [], ['multiple' => 'true']));

        $expected = <<<'EOD'
<select name="test[]" multiple="true" size="4">
<option value="0" selected>zero</option>
<option value="1">one</option>
<option value="value3">text3</option>
</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::dropDownList('test', [0], $this->getDataItems3(), ['multiple' => 'true']));
        $this->assertEqualsWithoutLE($expected, Html::dropDownList('test', new \ArrayObject([0]), $this->getDataItems3(), ['multiple' => 'true']));

        $expected = <<<'EOD'
<select name="test[]" multiple="true" size="4">
<option value="0">zero</option>
<option value="1" selected>one</option>
<option value="value3" selected>text3</option>
</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::dropDownList('test', ['1', 'value3'], $this->getDataItems3(), ['multiple' => 'true']));
        $this->assertEqualsWithoutLE($expected, Html::dropDownList('test', new \ArrayObject(['1', 'value3']), $this->getDataItems3(), ['multiple' => 'true']));
    }

    public function testListBox()
    {
        $expected = <<<'EOD'
<select name="test" size="4">

</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::listBox('test'));
        $expected = <<<'EOD'
<select name="test" size="5">
<option value="value1">text1</option>
<option value="value2">text2</option>
</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::listBox('test', null, $this->getDataItems(), ['size' => 5]));
        $expected = <<<'EOD'
<select name="test" size="4">
<option value="value1&lt;&gt;">text1&lt;&gt;</option>
<option value="value  2">text  2</option>
</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::listBox('test', null, $this->getDataItems2()));
        $expected = <<<'EOD'
<select name="test" size="4">
<option value="value1&lt;&gt;">text1&lt;&gt;</option>
<option value="value  2">text&nbsp;&nbsp;2</option>
</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::listBox('test', null, $this->getDataItems2(), ['encodeSpaces' => true]));
        $expected = <<<'EOD'
<select name="test" size="4">
<option value="value1&lt;&gt;">text1<></option>
<option value="value  2">text  2</option>
</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::listBox('test', null, $this->getDataItems2(), ['encode' => false]));
        $expected = <<<'EOD'
<select name="test" size="4">
<option value="value1&lt;&gt;">text1<></option>
<option value="value  2">text&nbsp;&nbsp;2</option>
</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::listBox('test', null, $this->getDataItems2(), ['encodeSpaces' => true, 'encode' => false]));
        $expected = <<<'EOD'
<select name="test" size="4">
<option value="value1">text1</option>
<option value="value2" selected>text2</option>
</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::listBox('test', 'value2', $this->getDataItems()));
        $expected = <<<'EOD'
<select name="test" size="4">
<option value="value1" selected>text1</option>
<option value="value2" selected>text2</option>
</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::listBox('test', ['value1', 'value2'], $this->getDataItems()));

        $expected = <<<'EOD'
<select name="test[]" multiple size="4">

</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::listBox('test', null, [], ['multiple' => true]));
        $this->assertEqualsWithoutLE($expected, Html::listBox('test[]', null, [], ['multiple' => true]));

        $expected = <<<'EOD'
<input type="hidden" name="test" value="0"><select name="test" size="4">

</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::listBox('test', '', [], ['unselect' => '0']));

        $expected = <<<'EOD'
<select name="test" size="4">
<option value="value1" selected>text1</option>
<option value="value2" selected>text2</option>
</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::listBox('test', new \ArrayObject(['value1', 'value2']), $this->getDataItems()));

        $expected = <<<'EOD'
<select name="test" size="4">
<option value="0" selected>zero</option>
<option value="1">one</option>
<option value="value3">text3</option>
</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::listBox('test', [0], $this->getDataItems3()));
        $this->assertEqualsWithoutLE($expected, Html::listBox('test', new \ArrayObject([0]), $this->getDataItems3()));

        $expected = <<<'EOD'
<select name="test" size="4">
<option value="0">zero</option>
<option value="1" selected>one</option>
<option value="value3" selected>text3</option>
</select>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::listBox('test', ['1', 'value3'], $this->getDataItems3()));
        $this->assertEqualsWithoutLE($expected, Html::listBox('test', new \ArrayObject(['1', 'value3']), $this->getDataItems3()));
    }

    public function testCheckboxList()
    {
        $this->assertEquals('<div></div>', Html::checkboxList('test'));

        $expected = <<<'EOD'
<div><label><input type="checkbox" name="test[]" value="value1"> text1</label>
<label><input type="checkbox" name="test[]" value="value2" checked> text2</label></div>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::checkboxList('test', ['value2'], $this->getDataItems()));
        $this->assertEqualsWithoutLE($expected, Html::checkboxList('test[]', ['value2'], $this->getDataItems()));

        $expected = <<<'EOD'
<div><label><input type="checkbox" name="test[]" value="value1&lt;&gt;"> text1&lt;&gt;</label>
<label><input type="checkbox" name="test[]" value="value  2"> text  2</label></div>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::checkboxList('test', ['value2'], $this->getDataItems2()));

        $expected = <<<'EOD'
<input type="hidden" name="test" value="0"><div><label><input type="checkbox" name="test[]" value="value1"> text1</label><br>
<label><input type="checkbox" name="test[]" value="value2" checked> text2</label></div>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::checkboxList('test', ['value2'], $this->getDataItems(), [
            'separator' => "<br>\n",
            'unselect' => '0',
        ]));

        $expected = <<<'EOD'
<div>0<label>text1 <input type="checkbox" name="test[]" value="value1"></label>
1<label>text2 <input type="checkbox" name="test[]" value="value2" checked></label></div>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::checkboxList('test', ['value2'], $this->getDataItems(), [
            'item' => function ($index, $label, $name, $checked, $value) {
                return $index . Html::label($label . ' ' . Html::checkbox($name, $checked, ['value' => $value]));
            },
        ]));

        $expected = <<<'EOD'
0<label>text1 <input type="checkbox" name="test[]" value="value1"></label>
1<label>text2 <input type="checkbox" name="test[]" value="value2" checked></label>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::checkboxList('test', ['value2'], $this->getDataItems(), [
            'item' => function ($index, $label, $name, $checked, $value) {
                return $index . Html::label($label . ' ' . Html::checkbox($name, $checked, ['value' => $value]));
            },
            'tag' => false,
        ]));


        $this->assertEqualsWithoutLE($expected, Html::checkboxList('test', new \ArrayObject(['value2']), $this->getDataItems(), [
            'item' => function ($index, $label, $name, $checked, $value) {
                return $index . Html::label($label . ' ' . Html::checkbox($name, $checked, ['value' => $value]));
            },
            'tag' => false,
        ]));

        $expected = <<<'EOD'
<div><label><input type="checkbox" name="test[]" value="0" checked> zero</label>
<label><input type="checkbox" name="test[]" value="1"> one</label>
<label><input type="checkbox" name="test[]" value="value3"> text3</label></div>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::checkboxList('test', [0], $this->getDataItems3()));
        $this->assertEqualsWithoutLE($expected, Html::checkboxList('test', new \ArrayObject([0]), $this->getDataItems3()));

        $expected = <<<'EOD'
<div><label><input type="checkbox" name="test[]" value="0"> zero</label>
<label><input type="checkbox" name="test[]" value="1" checked> one</label>
<label><input type="checkbox" name="test[]" value="value3" checked> text3</label></div>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::checkboxList('test', ['1', 'value3'], $this->getDataItems3()));
        $this->assertEqualsWithoutLE($expected, Html::checkboxList('test', new \ArrayObject(['1', 'value3']), $this->getDataItems3()));
    }

    public function testRadioList()
    {
        $this->assertEquals('<div></div>', Html::radioList('test'));

        $expected = <<<'EOD'
<div><label><input type="radio" name="test" value="value1"> text1</label>
<label><input type="radio" name="test" value="value2" checked> text2</label></div>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::radioList('test', ['value2'], $this->getDataItems()));

        $expected = <<<'EOD'
<div><label><input type="radio" name="test" value="value1&lt;&gt;"> text1&lt;&gt;</label>
<label><input type="radio" name="test" value="value  2"> text  2</label></div>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::radioList('test', ['value2'], $this->getDataItems2()));

        $expected = <<<'EOD'
<input type="hidden" name="test" value="0"><div><label><input type="radio" name="test" value="value1"> text1</label><br>
<label><input type="radio" name="test" value="value2" checked> text2</label></div>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::radioList('test', ['value2'], $this->getDataItems(), [
            'separator' => "<br>\n",
            'unselect' => '0',
        ]));

        $expected = <<<'EOD'
<div>0<label>text1 <input type="radio" name="test" value="value1"></label>
1<label>text2 <input type="radio" name="test" value="value2" checked></label></div>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::radioList('test', ['value2'], $this->getDataItems(), [
            'item' => function ($index, $label, $name, $checked, $value) {
                return $index . Html::label($label . ' ' . Html::radio($name, $checked, ['value' => $value]));
            },
        ]));

        $expected = <<<'EOD'
0<label>text1 <input type="radio" name="test" value="value1"></label>
1<label>text2 <input type="radio" name="test" value="value2" checked></label>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::radioList('test', ['value2'], $this->getDataItems(), [
            'item' => function ($index, $label, $name, $checked, $value) {
                return $index . Html::label($label . ' ' . Html::radio($name, $checked, ['value' => $value]));
            },
            'tag' => false,
        ]));

        $this->assertEqualsWithoutLE($expected, Html::radioList('test', new \ArrayObject(['value2']), $this->getDataItems(), [
            'item' => function ($index, $label, $name, $checked, $value) {
                return $index . Html::label($label . ' ' . Html::radio($name, $checked, ['value' => $value]));
            },
            'tag' => false,
        ]));

        $expected = <<<'EOD'
<div><label><input type="radio" name="test" value="0" checked> zero</label>
<label><input type="radio" name="test" value="1"> one</label>
<label><input type="radio" name="test" value="value3"> text3</label></div>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::radioList('test', [0], $this->getDataItems3()));
        $this->assertEqualsWithoutLE($expected, Html::radioList('test', new \ArrayObject([0]), $this->getDataItems3()));

        $expected = <<<'EOD'
<div><label><input type="radio" name="test" value="0"> zero</label>
<label><input type="radio" name="test" value="1"> one</label>
<label><input type="radio" name="test" value="value3" checked> text3</label></div>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::radioList('test', ['value3'], $this->getDataItems3()));
        $this->assertEqualsWithoutLE($expected, Html::radioList('test', new \ArrayObject(['value3']), $this->getDataItems3()));
    }

    public function testUl()
    {
        $data = [
            1, 'abc', '<>',
        ];
        $expected = <<<'EOD'
<ul>
<li>1</li>
<li>abc</li>
<li>&lt;&gt;</li>
</ul>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::ul($data));
        $expected = <<<'EOD'
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
            },
        ]));

        $this->assertEquals('<ul class="test"></ul>', Html::ul([], ['class' => 'test']));

        $this->assertStringMatchesFormat('<foo>%A</foo>', Html::ul([], ['tag' => 'foo']));
    }

    public function testOl()
    {
        $data = [
            1, 'abc', '<>',
        ];
        $expected = <<<'EOD'
<ol>
<li class="ti">1</li>
<li class="ti">abc</li>
<li class="ti">&lt;&gt;</li>
</ol>
EOD;
        $this->assertEqualsWithoutLE($expected, Html::ol($data, [
            'itemOptions' => ['class' => 'ti'],
        ]));
        $expected = <<<'EOD'
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
            },
        ]));

        $this->assertEquals('<ol class="test"></ol>', Html::ol([], ['class' => 'test']));
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
        $expected = <<<'EOD'
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

        // Attributes for prompt (https://github.com/yiisoft/yii2/issues/7420)

        $data = [
            'value1' => 'label1',
            'value2' => 'label2',
        ];
        $expected = <<<'EOD'
<option class="prompt" value="-1" label="None">Please select</option>
<option value="value1" selected>label1</option>
<option value="value2">label2</option>
EOD;
        $attributes = [
            'prompt' => [
                'text' => 'Please select', 'options' => ['class' => 'prompt', 'value' => '-1', 'label' => 'None'],
            ],
        ];
        $this->assertEqualsWithoutLE($expected, Html::renderSelectOptions(['value1'], $data, $attributes));
    }

    public function testRenderAttributes()
    {
        $this->assertEquals('', Html::renderTagAttributes([]));
        $this->assertEquals(' name="test" value="1&lt;&gt;"', Html::renderTagAttributes(['name' => 'test', 'empty' => null, 'value' => '1<>']));
        $this->assertEquals(' checked disabled', Html::renderTagAttributes(['checked' => true, 'disabled' => true, 'hidden' => false]));
        $this->assertEquals(' class="first second"', Html::renderTagAttributes(['class' => ['first', 'second']]));
        $this->assertEquals('', Html::renderTagAttributes(['class' => []]));
        $this->assertEquals(' style="width: 100px; height: 200px;"', Html::renderTagAttributes(['style' => ['width' => '100px', 'height' => '200px']]));
        $this->assertEquals('', Html::renderTagAttributes(['style' => []]));

        $attributes = [
            'data' => [
                'foo' => [],
            ],
        ];
        $this->assertEquals(' data-foo=\'[]\'', Html::renderTagAttributes($attributes));
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

        $options = [
            'class' => ['test'],
        ];
        Html::addCssClass($options, 'test2');
        $this->assertEquals(['class' => ['test', 'test2']], $options);
        Html::addCssClass($options, 'test2');
        $this->assertEquals(['class' => ['test', 'test2']], $options);
        Html::addCssClass($options, ['test3']);
        $this->assertEquals(['class' => ['test', 'test2', 'test3']], $options);

        $options = [
            'class' => 'test',
        ];
        Html::addCssClass($options, ['test1', 'test2']);
        $this->assertEquals(['class' => 'test test1 test2'], $options);
    }

    /**
     * @depends testAddCssClass
     */
    public function testMergeCssClass()
    {
        $options = [
            'class' => [
                'persistent' => 'test1',
            ],
        ];
        Html::addCssClass($options, ['persistent' => 'test2']);
        $this->assertEquals(['persistent' => 'test1'], $options['class']);
        Html::addCssClass($options, ['additional' => 'test2']);
        $this->assertEquals(['persistent' => 'test1', 'additional' => 'test2'], $options['class']);
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

        $options = ['class' => ['test', 'test2', 'test3']];
        Html::removeCssClass($options, 'test2');
        $this->assertEquals(['class' => ['test', 2 => 'test3']], $options);
        Html::removeCssClass($options, 'test');
        Html::removeCssClass($options, 'test3');
        $this->assertEquals([], $options);

        $options = [
            'class' => 'test test1 test2',
        ];
        Html::removeCssClass($options, ['test1', 'test2']);
        $this->assertEquals(['class' => 'test'], $options);
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

        $options = [
            'style' => [
                'width' => '100px',
            ],
        ];
        Html::addCssStyle($options, ['color' => 'red'], false);
        $this->assertEquals('width: 100px; color: red;', $options['style']);
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
        $this->assertNotTrue(array_key_exists('style', $options));
        $options = [
            'style' => [
                'color' => 'red',
                'width' => '100px',
            ],
        ];
        Html::removeCssStyle($options, ['color']);
        $this->assertEquals('width: 100px;', $options['style']);
    }

    public function testBooleanAttributes()
    {
        $this->assertEquals('<input type="email" name="mail">', Html::input('email', 'mail', null, ['required' => false]));
        $this->assertEquals('<input type="email" name="mail" required>', Html::input('email', 'mail', null, ['required' => true]));
        $this->assertEquals('<input type="email" name="mail" required="hi">', Html::input('email', 'mail', null, ['required' => 'hi']));
    }

    public function testDataAttributes()
    {
        $this->assertEquals('<link src="xyz" data-a="1" data-b="c">', Html::tag('link', '', ['src' => 'xyz', 'data' => ['a' => 1, 'b' => 'c']]));
        $this->assertEquals('<link src="xyz" ng-a="1" ng-b="c">', Html::tag('link', '', ['src' => 'xyz', 'ng' => ['a' => 1, 'b' => 'c']]));
        $this->assertEquals('<link src="xyz" data-ng-a="1" data-ng-b="c">', Html::tag('link', '', ['src' => 'xyz', 'data-ng' => ['a' => 1, 'b' => 'c']]));
        $this->assertEquals('<link src=\'{"a":1,"b":"It\\u0027s"}\'>', Html::tag('link', '', ['src' => ['a' => 1, 'b' => "It's"]]));
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

    protected function getDataItems3()
    {
        return [
            'zero',
            'one',
            'value3' => 'text3',
        ];
    }

    /**
     * Data provider for [[testActiveTextInput()]].
     * @return array test data
     */
    public function dataProviderActiveTextInput()
    {
        return [
            [
                'some text',
                [],
                '<input type="text" id="htmltestmodel-name" name="HtmlTestModel[name]" value="some text">',
            ],
            [
                '',
                [
                    'maxlength' => true,
                ],
                '<input type="text" id="htmltestmodel-name" name="HtmlTestModel[name]" value="" maxlength="100">',
            ],
            [
                '',
                [
                    'maxlength' => 99,
                ],
                '<input type="text" id="htmltestmodel-name" name="HtmlTestModel[name]" value="" maxlength="99">',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderActiveTextInput
     *
     * @param string $value
     * @param array $options
     * @param string $expectedHtml
     */
    public function testActiveTextInput($value, array $options, $expectedHtml)
    {
        $model = new HtmlTestModel();
        $model->name = $value;
        $this->assertEquals($expectedHtml, Html::activeTextInput($model, 'name', $options));
    }

    /**
     * Data provider for [[testActivePasswordInput()]].
     * @return array test data
     */
    public function dataProviderActivePasswordInput()
    {
        return [
            [
                'some text',
                [],
                '<input type="password" id="htmltestmodel-name" name="HtmlTestModel[name]" value="some text">',
            ],
            [
                '',
                [
                    'maxlength' => true,
                ],
                '<input type="password" id="htmltestmodel-name" name="HtmlTestModel[name]" value="" maxlength="100">',
            ],
            [
                '',
                [
                    'maxlength' => 99,
                ],
                '<input type="password" id="htmltestmodel-name" name="HtmlTestModel[name]" value="" maxlength="99">',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderActivePasswordInput
     *
     * @param string $value
     * @param array $options
     * @param string $expectedHtml
     */
    public function testActivePasswordInput($value, array $options, $expectedHtml)
    {
        $model = new HtmlTestModel();
        $model->name = $value;
        $this->assertEquals($expectedHtml, Html::activePasswordInput($model, 'name', $options));
    }

    public function errorSummaryDataProvider()
    {
        return [
            [
                'ok',
                [],
                '<div style="display:none"><p>Please fix the following errors:</p><ul></ul></div>',
            ],
            [
                'ok',
                ['header' => 'Custom header', 'footer' => 'Custom footer', 'style' => 'color: red'],
                '<div style="color: red; display:none">Custom header<ul></ul>Custom footer</div>',
            ],
            [
                str_repeat('long_string', 60),
                [],
                '<div><p>Please fix the following errors:</p><ul><li>Name should contain at most 100 characters.</li></ul></div>',
            ],
            [
                'not_an_integer',
                [],
                '<div><p>Please fix the following errors:</p><ul><li>Error message. Here are some chars: &lt; &gt;</li></ul></div>',
                function ($model) {
                    /* @var $model DynamicModel */
                    $model->addError('name', 'Error message. Here are some chars: < >');
                },
            ],
            [
                'not_an_integer',
                ['encode' => false],
                '<div><p>Please fix the following errors:</p><ul><li>Error message. Here are some chars: < ></li></ul></div>',
                function ($model) {
                    /* @var $model DynamicModel */
                    $model->addError('name', 'Error message. Here are some chars: < >');
                },
            ],
            [
                str_repeat('long_string', 60),
                [],
                '<div><p>Please fix the following errors:</p><ul><li>Error message. Here are some chars: &lt; &gt;</li></ul></div>',
                function ($model) {
                    /* @var $model DynamicModel */
                    $model->addError('name', 'Error message. Here are some chars: < >');
                },
            ],
            [
                'not_an_integer',
                ['showAllErrors' => true],
                '<div><p>Please fix the following errors:</p><ul><li>Error message. Here are some chars: &lt; &gt;</li>
<li>Error message. Here are even more chars: &quot;&quot;</li></ul></div>',
                function ($model) {
                    /* @var $model DynamicModel */
                    $model->addError('name', 'Error message. Here are some chars: < >');
                    $model->addError('name', 'Error message. Here are even more chars: ""');
                },
            ],
        ];
    }

    /**
     * @dataProvider errorSummaryDataProvider
     *
     * @param string $value
     * @param array $options
     * @param string $expectedHtml
     * @param \Closure $beforeValidate
     */
    public function testErrorSummary($value, array $options, $expectedHtml, $beforeValidate = null)
    {
        $model = new HtmlTestModel();
        $model->name = $value;
        if ($beforeValidate !== null) {
            call_user_func($beforeValidate, $model);
        }
        $model->validate(null, false);

        $this->assertEqualsWithoutLE($expectedHtml, Html::errorSummary($model, $options));
    }

    public function testError()
    {
        $model = new HtmlTestModel();
        $model->validate();
        $this->assertEquals(
            '<div>Name cannot be blank.</div>',
            Html::error($model, 'name'),
            'Default error message after calling $model->getFirstError()'
        );

        $this->assertEquals(
            '<div>this is custom error message</div>',
            Html::error($model, 'name', ['errorSource' => [$model, 'customError']]),
            'Custom error message generated by callback'
        );
        $this->assertEquals(
            '<div>Error in yiiunit\framework\helpers\HtmlTestModel - name</div>',
            Html::error($model, 'name', ['errorSource' => function ($model, $attribute) {
                return 'Error in ' . get_class($model) . ' - ' . $attribute;
            }]),
            'Custom error message generated by closure'
        );
    }

    /**
     * Test that attributes that output same errors, return unique message error
     * @see https://github.com/yiisoft/yii2/pull/15859
     */
    public function testCollectError()
    {
        $model = new DynamicModel(compact('attr1', 'attr2'));

        $model->addError('attr1', 'error1');
        $model->addError('attr1', 'error2');
        $model->addError('attr2', 'error1');

        $this->assertEquals(
            '<div><p>Please fix the following errors:</p><ul><li>error1</li>
<li>error2</li></ul></div>',
            Html::errorSummary($model, ['showAllErrors' => true])
        );
    }

    /**
     * Data provider for [[testActiveTextArea()]].
     * @return array test data
     */
    public function dataProviderActiveTextArea()
    {
        return [
            [
                'some text',
                [],
                '<textarea id="htmltestmodel-description" name="HtmlTestModel[description]">some text</textarea>',
            ],
            [
                'some text',
                [
                    'maxlength' => true,
                ],
                '<textarea id="htmltestmodel-description" name="HtmlTestModel[description]" maxlength="500">some text</textarea>',
            ],
            [
                'some text',
                [
                    'maxlength' => 99,
                ],
                '<textarea id="htmltestmodel-description" name="HtmlTestModel[description]" maxlength="99">some text</textarea>',
            ],
            [
                'some text',
                [
                    'value' => 'override text',
                ],
                '<textarea id="htmltestmodel-description" name="HtmlTestModel[description]">override text</textarea>',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderActiveTextArea
     *
     * @param string $value
     * @param array $options
     * @param string $expectedHtml
     */
    public function testActiveTextArea($value, array $options, $expectedHtml)
    {
        $model = new HtmlTestModel();
        $model->description = $value;
        $this->assertEquals($expectedHtml, Html::activeTextarea($model, 'description', $options));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/10078
     */
    public function testCsrfDisable()
    {
        Yii::$app->request->enableCsrfValidation = true;
        Yii::$app->request->cookieValidationKey = 'foobar';

        $csrfForm = Html::beginForm('/index.php', 'post', ['id' => 'mycsrfform']);
        $this->assertEquals(
            '<form id="mycsrfform" action="/index.php" method="post">'
            . "\n" . '<input type="hidden" name="_csrf" value="' . Yii::$app->request->getCsrfToken() . '">',
            $csrfForm
        );

        $noCsrfForm = Html::beginForm('/index.php', 'post', ['csrf' => false, 'id' => 'myform']);
        $this->assertEquals('<form id="myform" action="/index.php" method="post">', $noCsrfForm);
    }

    /**
     * Data provider for [[testActiveRadio()]].
     * @return array test data
     */
    public function dataProviderActiveRadio()
    {
        return [
            [
                true,
                [],
                '<input type="hidden" name="HtmlTestModel[radio]" value="0"><label><input type="radio" id="htmltestmodel-radio" name="HtmlTestModel[radio]" value="1" checked> Radio</label>',
            ],
            [
                true,
                ['uncheck' => false],
                '<label><input type="radio" id="htmltestmodel-radio" name="HtmlTestModel[radio]" value="1" checked> Radio</label>',
            ],
            [
                true,
                ['label' => false],
                '<input type="hidden" name="HtmlTestModel[radio]" value="0"><input type="radio" id="htmltestmodel-radio" name="HtmlTestModel[radio]" value="1" checked>',
            ],
            [
                true,
                ['uncheck' => false, 'label' => false],
                '<input type="radio" id="htmltestmodel-radio" name="HtmlTestModel[radio]" value="1" checked>',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderActiveRadio
     *
     * @param string $value
     * @param array $options
     * @param string $expectedHtml
     */
    public function testActiveRadio($value, array $options, $expectedHtml)
    {
        $model = new HtmlTestModel();
        $model->radio = $value;
        $this->assertEquals($expectedHtml, Html::activeRadio($model, 'radio', $options));
    }

    /**
     * Data provider for [[testActiveCheckbox()]].
     * @return array test data
     */
    public function dataProviderActiveCheckbox()
    {
        return [
            [
                true,
                [],
                '<input type="hidden" name="HtmlTestModel[checkbox]" value="0"><label><input type="checkbox" id="htmltestmodel-checkbox" name="HtmlTestModel[checkbox]" value="1" checked> Checkbox</label>',
            ],
            [
                true,
                ['uncheck' => false],
                '<label><input type="checkbox" id="htmltestmodel-checkbox" name="HtmlTestModel[checkbox]" value="1" checked> Checkbox</label>',
            ],
            [
                true,
                ['label' => false],
                '<input type="hidden" name="HtmlTestModel[checkbox]" value="0"><input type="checkbox" id="htmltestmodel-checkbox" name="HtmlTestModel[checkbox]" value="1" checked>',
            ],
            [
                true,
                ['uncheck' => false, 'label' => false],
                '<input type="checkbox" id="htmltestmodel-checkbox" name="HtmlTestModel[checkbox]" value="1" checked>',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderActiveCheckbox
     *
     * @param string $value
     * @param array $options
     * @param string $expectedHtml
     */
    public function testActiveCheckbox($value, array $options, $expectedHtml)
    {
        $model = new HtmlTestModel();
        $model->checkbox = $value;
        $this->assertEquals($expectedHtml, Html::activeCheckbox($model, 'checkbox', $options));
    }

    /**
     * Data provider for [[testAttributeNameValidation()]].
     * @return array test data
     */
    public function validAttributeNamesProvider()
    {
        $data = [
            ['asd]asdf.asdfa[asdfa', 'asdf.asdfa'],
            ['a', 'a'],
            ['[0]a', 'a'],
            ['a[0]', 'a'],
            ['[0]a[0]', 'a'],
            ['[0]a.[0]', 'a.'],
        ];

        if (getenv('TRAVIS_PHP_VERSION') !== 'nightly') {
            $data = array_merge($data, [
                ['ä', 'ä'],
                ['ä', 'ä'],
                ['asdf]öáöio..[asdfasdf', 'öáöio..'],
                ['öáöio', 'öáöio'],
                ['[0]test.ööößß.d', 'test.ööößß.d'],
                ['ИІК', 'ИІК'],
                [']ИІК[', 'ИІК'],
                ['[0]ИІК[0]', 'ИІК'],
            ]);
        } else {
            $this->markTestIncomplete("Unicode characters check skipped for 'nightly' PHP version because \w does not work with these as expected. Check later with stable version.");
        }

        return $data;
    }

    /**
     * Data provider for [[testAttributeNameValidation()]].
     * @return array test data
     */
    public function invalidAttributeNamesProvider()
    {
        return [
            ['. ..'],
            ['a +b'],
            ['a,b'],
        ];
    }

    /**
     * @dataProvider validAttributeNamesProvider
     *
     * @param string $name
     * @param string $expected
     */
    public function testAttributeNameValidation($name, $expected)
    {
        if (!isset($expected)) {
            $this->expectException('yii\base\InvalidArgumentException');
            Html::getAttributeName($name);
        } else {
            $this->assertEquals($expected, Html::getAttributeName($name));
        }
    }

    /**
     * @dataProvider invalidAttributeNamesProvider
     *
     * @param string $name
     */
    public function testAttributeNameException($name)
    {
        $this->expectException('yii\base\InvalidArgumentException');
        Html::getAttributeName($name);
    }

    public function testActiveFileInput()
    {
        $expected = '<input type="hidden" name="foo" value=""><input type="file" id="htmltestmodel-types" name="foo">';
        $model = new HtmlTestModel();
        $actual = Html::activeFileInput($model, 'types', ['name' => 'foo']);
        $this->assertEqualsWithoutLE($expected, $actual);

        $expected = '<input type="hidden" id="specific-id" name="foo" value=""><input type="file" id="htmltestmodel-types" name="foo">';
        $model = new HtmlTestModel();
        $actual = Html::activeFileInput($model, 'types', ['name' => 'foo', 'hiddenOptions'=>['id'=>'specific-id']]);
        $this->assertEqualsWithoutLE($expected, $actual);

        $expected = '<input type="hidden" id="specific-id" name="HtmlTestModel[types]" value=""><input type="file" id="htmltestmodel-types" name="HtmlTestModel[types]">';
        $model = new HtmlTestModel();
        $actual = Html::activeFileInput($model, 'types', ['hiddenOptions'=>['id'=>'specific-id']]);
        $this->assertEqualsWithoutLE($expected, $actual);

        $expected = '<input type="hidden" name="HtmlTestModel[types]" value=""><input type="file" id="htmltestmodel-types" name="HtmlTestModel[types]">';
        $model = new HtmlTestModel();
        $actual = Html::activeFileInput($model, 'types', ['hiddenOptions'=>[]]);
        $this->assertEqualsWithoutLE($expected, $actual);

        $expected = '<input type="hidden" name="foo" value=""><input type="file" id="htmltestmodel-types" name="foo">';
        $model = new HtmlTestModel();
        $actual = Html::activeFileInput($model, 'types', ['name' => 'foo', 'hiddenOptions'=>[]]);
        $this->assertEqualsWithoutLE($expected, $actual);
    }

    /**
     * @expectedException \yii\base\InvalidArgumentException
     * @expectedExceptionMessage Attribute name must contain word characters only.
     */
    public function testGetAttributeValueInvalidArgumentException()
    {
        $model = new HtmlTestModel();
        Html::getAttributeValue($model, '-');
    }

    public function testGetAttributeValue()
    {
        $model = new HtmlTestModel();

        $expected = null;
        $actual = Html::getAttributeValue($model, 'types');
        $this->assertSame($expected, $actual);

        $activeRecord = $this->createMock(\yii\db\ActiveRecordInterface::class);
        $activeRecord->method('getPrimaryKey')->willReturn(1);
        $model->types = $activeRecord;

        $expected = 1;
        $actual = Html::getAttributeValue($model, 'types');
        $this->assertSame($expected, $actual);

        $model->types = [
            $activeRecord,
        ];

        $expected = [1];
        $actual = Html::getAttributeValue($model, 'types');
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \yii\base\InvalidArgumentException
     * @expectedExceptionMessage Attribute name must contain word characters only.
     */
    public function testGetInputNameInvalidArgumentExceptionAttribute()
    {
        $model = new HtmlTestModel();
        Html::getInputName($model, '-');
    }

    /**
     * @expectedException \yii\base\InvalidArgumentException
     * @expectedExceptionMessageRegExp /(.*)formName\(\) cannot be empty for tabular inputs.$/
     */
    public function testGetInputNameInvalidArgumentExceptionFormName()
    {
        $model = $this->createMock(\yii\base\Model::class);
        $model->method('formName')->willReturn('');
        Html::getInputName($model, '[foo]bar');
    }

    public function testGetInputName()
    {
        $model = $this->createMock(\yii\base\Model::class);
        $model->method('formName')->willReturn('');
        $expected = 'types';
        $actual = Html::getInputName($model, 'types');
        $this->assertSame($expected, $actual);
    }


    public function testEscapeJsRegularExpression()
    {
        $expected = '/[a-z0-9-]+/';
        $actual = Html::escapeJsRegularExpression('([a-z0-9-]+)');
        $this->assertSame($expected, $actual);

        $expected = '/([a-z0-9-]+)/gim';
        $actual = Html::escapeJsRegularExpression('/([a-z0-9-]+)/Ugimex');
        $this->assertSame($expected, $actual);
    }

    public function testActiveDropDownList()
    {
        $expected = <<<'HTML'
<input type="hidden" name="HtmlTestModel[types]" value=""><select id="htmltestmodel-types" name="HtmlTestModel[types][]" multiple="true" size="4">

</select>
HTML;
        $model = new HtmlTestModel();
        $actual = Html::activeDropDownList($model, 'types', [], ['multiple' => 'true']);
        $this->assertEqualsWithoutLE($expected, $actual);
    }

    public function testActiveCheckboxList()
    {
        $model = new HtmlTestModel();

        $expected = <<<'HTML'
<input type="hidden" name="HtmlTestModel[types]" value=""><div id="htmltestmodel-types"><label><input type="radio" name="HtmlTestModel[types]" value="0"> foo</label></div>
HTML;
        $actual = Html::activeRadioList($model, 'types', ['foo']);
        $this->assertEqualsWithoutLE($expected, $actual);
    }

    public function testActiveRadioList()
    {
        $model = new HtmlTestModel();

        $expected = <<<'HTML'
<input type="hidden" name="HtmlTestModel[types]" value=""><div id="htmltestmodel-types"><label><input type="checkbox" name="HtmlTestModel[types][]" value="0"> foo</label></div>
HTML;
        $actual = Html::activeCheckboxList($model, 'types', ['foo']);
        $this->assertEqualsWithoutLE($expected, $actual);
    }

    public function testActiveTextInput_placeholderFillFromModel()
    {
        $model = new HtmlTestModel();

        $html = Html::activeTextInput($model, 'name', ['placeholder' => true]);

        $this->assertContains('placeholder="Name"', $html);
    }

    public function testActiveTextInput_customPlaceholder()
    {
        $model = new HtmlTestModel();

        $html = Html::activeTextInput($model, 'name', ['placeholder' => 'Custom placeholder']);

        $this->assertContains('placeholder="Custom placeholder"', $html);
    }

    public function testActiveTextInput_placeholderFillFromModelTabular()
    {
        $model = new HtmlTestModel();

        $html = Html::activeTextInput($model, '[0]name', ['placeholder' => true]);

        $this->assertContains('placeholder="Name"', $html);
    }

}

/**
 * @property string name
 * @property array types
 * @property string description
 */
class HtmlTestModel extends DynamicModel
{
    public function init()
    {
        foreach (['name', 'types', 'description', 'radio', 'checkbox'] as $attribute) {
            $this->defineAttribute($attribute);
        }
    }

    public function rules()
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 100],
            ['description', 'string', 'max' => 500],
            [['radio', 'checkbox'], 'boolean'],
        ];
    }

    public function customError()
    {
        return 'this is custom error message';
    }
}
