<?php

namespace yiiunit\data\grid;

use yii\widgets\BaseListView;

class TestGridView extends \yii\grid\GridView
{
    public $options = [];
    public $tableOptions = [];

    public function getId($autoGenerate = true)
    {
        return parent::getId(false);
    }

    public function run()
    {
        BaseListView::run();
    }
}
