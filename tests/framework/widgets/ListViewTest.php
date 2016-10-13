<?php

namespace yiiunit\framework\widgets;

use yii\data\ArrayDataProvider;
use yii\widgets\ListView;

/**
 * @group widgets
 */
class ListViewTest extends \yiiunit\TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testEmptyListShown()
    {
        $dataProvider = new ArrayDataProvider([
            'allModels' => [],
        ]);

        ob_start();
        echo ListView::widget([
            'dataProvider' => $dataProvider,
            'showOnEmpty' => false,
            'emptyText' => "Nothing at all",
        ]);
        $actualHtml = ob_get_clean();

        $this->assertTrue(strpos($actualHtml, "Nothing at all") !== false, "displays the empty message");
        $this->assertTrue(strpos($actualHtml, '<div class="empty">') !== false, "adds the 'empty' class");
        $this->assertTrue(strpos($actualHtml, '<div class="summary">') === false, "does not display the summary");
    }

    public function testEmptyListNotShown()
    {
        $dataProvider = new ArrayDataProvider([
            'allModels' => [],
        ]);

        ob_start();
        echo ListView::widget([
            'dataProvider' => $dataProvider,
            'showOnEmpty' => true,
            'emptyText' => "Nothing at all",
        ]);
        $actualHtml = ob_get_clean();

        $this->assertTrue(strpos($actualHtml, '<div class="empty">') === false, "does not add the 'empty' class");
        $this->assertTrue(strpos($actualHtml, '<div class="summary">') === false, "does not display the summary");
        $this->assertEmpty(trim(\strip_tags($actualHtml)), "contains no text");
    }
}
