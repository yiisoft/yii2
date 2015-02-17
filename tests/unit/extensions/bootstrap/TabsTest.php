<?php
namespace yiiunit\extensions\bootstrap;

use yii\bootstrap\Tabs;
use yii\helpers\Html;
/**
 * Tests for Tabs widget
 *
 * @group bootstrap
 */
class TabsTest extends BootstrapTestCase
{
    /**
     * Each tab should have a corresponding unique ID
     *
     * @see https://github.com/yiisoft/yii2/issues/6150
     */
    public function testIds()
    {
        Tabs::$counter = 0;
        $out = Tabs::widget([
            'items' => [
                [
                    'label' => 'Page1', 'content' => 'Page1',
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
                    'items' => [
                        ['label' => 'Page4', 'content' => 'Page4'],
                        ['label' => 'Page5', 'content' => 'Page5'],
                    ]
                ],
                [
                    'label' => $extAnchor='External link', 'url' => $extUrl=['//other/route'],
                ],                
            ]
        ]);

        $page1 = 'w0-tab0';
        $page2 = 'w0-dd1-tab0';
        $page3 = 'w0-dd1-tab1';
        $page4 = 'w0-dd2-tab0';
        $page5 = 'w0-dd2-tab1';

        $shouldContain = [
            'w0', // nav widget container
                "#$page1", // Page1

                'w1', // Dropdown1
                    "$page2", // Page2
                    "$page3", // Page3


                'w2', // Dropdown2
                    "#$page4", // Page4
                    "#$page5", // Page5

            // containers
            "id=\"$page1\"",
            "id=\"$page2\"",
            "id=\"$page3\"",
            "id=\"$page4\"",
            "id=\"$page5\"",
            Html::a($extAnchor,$extUrl),
        ];

        foreach ($shouldContain as $string) {
            $this->assertContains($string, $out);
        }
    }
}
