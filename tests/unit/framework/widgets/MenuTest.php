<?php

namespace yiiunit\framework\widgets;

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
                    'label'  => '<span class="glyphicon glyphicon-user"></span> Users',
                    'url'    => '#',
                ],
                [
                    'encode' => true,
                    'label'  => 'Authors & Publications',
                    'url'    => '#',
                ],
            ]
        ]);

        $this->assertEqualsWithoutLE(<<<HTML
<ul><li><a href="#"><span class="glyphicon glyphicon-user"></span> Users</a></li>
<li><a href="#">Authors &amp; Publications</a></li></ul>
HTML
        , $output);

        $output = Menu::widget([
            'route' => 'test/test',
            'params' => [],
            'encodeLabels' => false,
            'items' => [
                [
                    'encode' => false,
                    'label'  => '<span class="glyphicon glyphicon-user"></span> Users',
                    'url'    => '#',
                ],
                [
                    'encode' => true,
                    'label'  => 'Authors & Publications',
                    'url'    => '#',
                ],
            ]
        ]);

        $this->assertEqualsWithoutLE(<<<HTML
<ul><li><a href="#"><span class="glyphicon glyphicon-user"></span> Users</a></li>
<li><a href="#">Authors &amp; Publications</a></li></ul>
HTML
            , $output);

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
                    'label'  => 'item1',
                    'url'    => '#',
                    'options' => ['tag' => 'div'],
                ],
                [
                    'label'  => 'item2',
                    'url'    => '#',
                    'options' => ['tag' => false],
                ],
            ]
        ]);

        $this->assertEqualsWithoutLE(<<<HTML
<div><a href="#">item1</a></div>
<a href="#">item2</a>
HTML
        , $output);
    }


}
