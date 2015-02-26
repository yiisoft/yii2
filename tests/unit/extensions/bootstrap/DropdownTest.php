<?php
namespace yiiunit\extensions\bootstrap;

use yii\bootstrap\Dropdown;

/**
 * Tests for Dropdown widget
 *
 * @group bootstrap
 */
class DropdownTest extends BootstrapTestCase
{
    public function testIds()
    {
        Dropdown::$counter = 0;
        $out = Dropdown::widget(
            [
                'items' => [
                    [
                        'label' => 'Page1',
                        'content' => 'Page1',
                    ],
                    [
                        'label' => 'Dropdown1',
                        'items' => [
                            ['label' => 'Page2', 'content' => 'Page2'],
                            ['label' => 'Page3', 'content' => 'Page3'],
                        ]
                    ],
                    [
                        'label' => 'Dropdown2',
                        'visible' => false,
                        'items' => [
                            ['label' => 'Page4', 'content' => 'Page4'],
                            ['label' => 'Page5', 'content' => 'Page5'],
                        ]
                    ]
                ]
            ]
        ) . "\n" . Dropdown::widget(
            [
                'options' => [
                    'role' => 'menu',
                ],
                'submenuOptions' => [
                    'class' => 'dropdown-submenu',
                ],
                'items' => [
                    [
                        'label' => 'Page6',
                        'content' => 'Page6',
                    ],
                    [
                        'label' => 'Dropdown3',
                        'items' => [
                            ['label' => 'Page7', 'content' => 'Page7'],
                            ['label' => 'Page8', 'content' => 'Page8'],
                        ]
                    ],
                ]
            ]
        );

        $expected = <<<EXPECTED
<ul id="w0" class="dropdown-menu"><li class="dropdown-header">Page1</li>
<li class="dropdown-submenu"><a href="#" tabindex="-1">Dropdown1</a><ul><li class="dropdown-header">Page2</li>
<li class="dropdown-header">Page3</li></ul></li></ul>
<ul id="w1" class="dropdown-menu" role="menu"><li class="dropdown-header">Page6</li>
<li class="dropdown-submenu"><a href="#" tabindex="-1">Dropdown3</a><ul class="dropdown-submenu"><li class="dropdown-header">Page7</li>
<li class="dropdown-header">Page8</li></ul></li></ul>
EXPECTED;

        $this->assertEqualsWithoutLE($expected, $out);
    }
}
