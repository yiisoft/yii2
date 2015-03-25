<?php

namespace yiiunit\framework\widgets;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Connection;
use yii\db\Query;
use yii\widgets\Breadcrumbs;
use yii\widgets\LinkSorter;
use yii\widgets\ListView;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Order;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * @group widgets
 */
class LinkSorterTest extends DatabaseTestCase
{
    protected $driverName = 'sqlite';

    protected function setUp()
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();
        $this->mockWebApplication();
        $this->breadcrumbs = new Breadcrumbs();
    }

    public function testLabelsSimple()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Order::find(),
            'models' => [new Order()],
            'totalCount' => 1,
            'sort' => [
                'route' => 'site/index',
            ],
        ]);

        ob_start();
        echo ListView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{sorter}",
        ]);
        $actualHtml = ob_get_clean();

        $this->assertTrue(strpos($actualHtml, '<a href="/index.php?r=site%2Findex&amp;sort=customer_id" data-sort="customer_id">Customer</a>') !== false);
        $this->assertTrue(strpos($actualHtml, '<a href="/index.php?r=site%2Findex&amp;sort=total" data-sort="total">Invoice Total</a>') !== false);
    }

    public function testLabelsExplicit()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Order::find(),
            'models' => [new Order()],
            'totalCount' => 1,
            'sort' => [
                'attributes' => ['total'],
                'route' => 'site/index',
            ],
        ]);

        ob_start();
        echo ListView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{sorter}",
        ]);
        $actualHtml = ob_get_clean();

        $this->assertFalse(strpos($actualHtml, '<a href="/index.php?r=site%2Findex&amp;sort=customer_id" data-sort="customer_id">Customer</a>') !== false);
        $this->assertTrue(strpos($actualHtml, '<a href="/index.php?r=site%2Findex&amp;sort=total" data-sort="total">Invoice Total</a>') !== false);
    }

}
