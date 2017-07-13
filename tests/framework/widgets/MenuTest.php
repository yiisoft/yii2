<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\widgets;

use Yii;
use yii\widgets\Menu;

/**
 * @group widgets
 */
class MenuTest extends \yiiunit\TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testEncodeLabel()
    {
        $output = Menu::widget([
            'route' => 'test/test',
            'params' => [],
            'encodeLabels' => true,
            'items' => [
                [
                    'encode' => false,
                    'label' => '<span class="glyphicon glyphicon-user"></span> Users',
                    'url' => '#',
                ],
                [
                    'encode' => true,
                    'label' => 'Authors & Publications',
                    'url' => '#',
                ],
            ],
        ]);

        $expected = <<<'HTML'
<ul><li><a href="#"><span class="glyphicon glyphicon-user"></span> Users</a></li>
<li><a href="#">Authors &amp; Publications</a></li></ul>
HTML;
        $this->assertEqualsWithoutLE($expected, $output);

        $output = Menu::widget([
            'route' => 'test/test',
            'params' => [],
            'encodeLabels' => false,
            'items' => [
                [
                    'encode' => false,
                    'label' => '<span class="glyphicon glyphicon-user"></span> Users',
                    'url' => '#',
                ],
                [
                    'encode' => true,
                    'label' => 'Authors & Publications',
                    'url' => '#',
                ],
            ],
        ]);

        $expected = <<<'HTML'
<ul><li><a href="#"><span class="glyphicon glyphicon-user"></span> Users</a></li>
<li><a href="#">Authors &amp; Publications</a></li></ul>
HTML;
        $this->assertEqualsWithoutLE($expected, $output);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/8064
     */
    public function testTagOption()
    {
        $output = Menu::widget([
            'route' => 'test/test',
            'params' => [],
            'encodeLabels' => true,
            'options' => [
                'tag' => false,
            ],
            'items' => [
                [
                    'label' => 'item1',
                    'url' => '#',
                    'options' => ['tag' => 'div'],
                ],
                [
                    'label' => 'item2',
                    'url' => '#',
                    'options' => ['tag' => false],
                ],
            ],
        ]);

        $expected = <<<'HTML'
<div><a href="#">item1</a></div>
<a href="#">item2</a>
HTML;
        $this->assertEqualsWithoutLE($expected, $output);

        $output = Menu::widget([
            'route' => 'test/test',
            'params' => [],
            'encodeLabels' => true,
            'options' => [
                'tag' => false,
            ],
            'items' => [
                [
                    'label' => 'item1',
                    'url' => '#',
                ],
                [
                    'label' => 'item2',
                    'url' => '#',
                ],
            ],
            'itemOptions' => ['tag' => false],
        ]);

        $expected = <<<'HTML'
<a href="#">item1</a>
<a href="#">item2</a>
HTML;

        $this->assertEqualsWithoutLE($expected, $output);
    }

    public function testItemTemplate()
    {
        $output = Menu::widget([
            'route' => 'test/test',
            'params' => [],
            'linkTemplate' => '',
            'labelTemplate' => '',
            'items' => [
                [
                    'label' => 'item1',
                    'url' => '#',
                    'template' => 'label: {label}; url: {url}',
                ],
                [
                    'label' => 'item2',
                    'template' => 'label: {label}',
                ],
                [
                    'label' => 'item3 (no template)',
                ],
            ],
        ]);

        $expected = <<<'HTML'
<ul><li>label: item1; url: #</li>
<li>label: item2</li>
<li></li></ul>
HTML;

        $this->assertEqualsWithoutLE($expected, $output);
    }

    public function testActiveItemClosure()
    {
        $output = Menu::widget([
            'route' => 'test/test',
            'params' => [],
            'linkTemplate' => '',
            'labelTemplate' => '',
            'items' => [
                [
                    'label' => 'item1',
                    'url' => '#',
                    'template' => 'label: {label}; url: {url}',
                    'active' => function ($item, $hasActiveChild, $isItemActive, $widget) {
                        return isset($item, $hasActiveChild, $isItemActive, $widget);
                    },
                ],
                [
                    'label' => 'item2',
                    'template' => 'label: {label}',
                    'active' => false,
                ],
                [
                    'label' => 'item3 (no template)',
                    'active' => 'somestring',
                ],
            ],
        ]);

        $expected = <<<'HTML'
<ul><li class="active">label: item1; url: #</li>
<li>label: item2</li>
<li class="active"></li></ul>
HTML;

        $this->assertEqualsWithoutLE($expected, $output);
    }

    public function testItemClassAsArray()
    {
        $output = Menu::widget([
            'route' => 'test/test',
            'params' => [],
            'encodeLabels' => true,
            'activeCssClass' => 'item-active',
            'items' => [
                [
                    'label' => 'item1',
                    'url' => '#',
                    'active' => true,
                    'options' => [
                        'class' => [
                            'someclass',
                        ],
                    ],
                ],
                [
                    'label' => 'item2',
                    'url' => '#',
                    'options' => [
                        'class' => [
                            'another-class',
                            'other--class',
                            'two classes',
                        ],
                    ],
                ],
                [
                    'label' => 'item3',
                    'url' => '#',
                ],
                [
                    'label' => 'item4',
                    'url' => '#',
                    'options' => [
                        'class' => [
                            'some-other-class',
                            'foo_bar_baz_class',
                        ],
                    ],
                ],
            ],
        ]);

        $expected = <<<'HTML'
<ul><li class="someclass item-active"><a href="#">item1</a></li>
<li class="another-class other--class two classes"><a href="#">item2</a></li>
<li><a href="#">item3</a></li>
<li class="some-other-class foo_bar_baz_class"><a href="#">item4</a></li></ul>
HTML;
        $this->assertEqualsWithoutLE($expected, $output);
    }

    public function testItemClassAsString()
    {
        $output = Menu::widget([
            'route' => 'test/test',
            'params' => [],
            'encodeLabels' => true,
            'activeCssClass' => 'item-active',
            'items' => [
                [
                    'label' => 'item1',
                    'url' => '#',
                    'options' => [
                        'class' => 'someclass',
                    ],
                ],
                [
                    'label' => 'item2',
                    'url' => '#',
                ],
                [
                    'label' => 'item3',
                    'url' => '#',
                    'options' => [
                        'class' => 'some classes',
                    ],
                ],
                [
                    'label' => 'item4',
                    'url' => '#',
                    'active' => true,
                    'options' => [
                        'class' => 'another-class other--class two classes',
                    ],
                ],
            ],
        ]);

        $expected = <<<'HTML'
<ul><li class="someclass"><a href="#">item1</a></li>
<li><a href="#">item2</a></li>
<li class="some classes"><a href="#">item3</a></li>
<li class="another-class other--class two classes item-active"><a href="#">item4</a></li></ul>
HTML;
        $this->assertEqualsWithoutLE($expected, $output);
    }

    /*public function testIsItemActive()
    {
        // TODO: implement test of protected method isItemActive()
    }*/
}
