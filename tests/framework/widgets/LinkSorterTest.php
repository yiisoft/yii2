<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\widgets;

use yii\data\ActiveDataProvider;
use yii\widgets\Breadcrumbs;
use yii\widgets\LinkSorter;
use yii\widgets\ListView;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Order;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * @group widgets
 * @group db
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
            'layout' => '{sorter}',
        ]);
        $actualHtml = ob_get_clean();

        $this->assertNotFalse(strpos($actualHtml,
            '<a href="/index.php?r=site%2Findex&amp;sort=customer_id" data-sort="customer_id">Customer</a>'));
        $this->assertNotFalse(strpos($actualHtml,
            '<a href="/index.php?r=site%2Findex&amp;sort=total" data-sort="total">Invoice Total</a>'));
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
            'layout' => '{sorter}',
        ]);
        $actualHtml = ob_get_clean();

        $this->assertFalse(strpos($actualHtml,
            '<a href="/index.php?r=site%2Findex&amp;sort=customer_id" data-sort="customer_id">Customer</a>'));
        $this->assertNotFalse(strpos($actualHtml,
            '<a href="/index.php?r=site%2Findex&amp;sort=total" data-sort="total">Invoice Total</a>'));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/15536
     */
    public function testShouldTriggerInitEvent()
    {
        $initTriggered = false;
        $linkSorter = new LinkSorter(
            [
                'sort' => [
                    'attributes' => ['total'],
                    'route' => 'site/index',
                ],
                'on init' => function () use (&$initTriggered) {
                    $initTriggered = true;
                }
            ]
        );

        $this->assertTrue($initTriggered);
    }
}
