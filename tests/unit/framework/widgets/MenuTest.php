<?php

namespace yiiunit\framework\widgets;

use yii\widgets\Menu;
use yii\widgets\Spaceless;

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

        $this->assertEquals(<<<HTML
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

        $this->assertEquals(<<<HTML
<ul><li><a href="#"><span class="glyphicon glyphicon-user"></span> Users</a></li>
<li><a href="#">Authors &amp; Publications</a></li></ul>
HTML
            , $output);

    }
}
