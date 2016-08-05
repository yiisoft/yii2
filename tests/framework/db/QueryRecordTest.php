<?php

namespace yiiunit\framework\db;

use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\QueryRecord;
use yiiunit\data\ar\TotalOrder;
use yiiunit\data\ar\Item;

/**
 * Description of QueryRecordTest
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.0.10
 */
abstract class QueryRecordTest extends DatabaseTestCase
{

    protected function setUp()
    {
        parent::setUp();
        ActiveRecord::$db = QueryRecord::$db = $this->getConnection();
    }

    public function testFind()
    {
        $this->assertEquals(['item_id' => 2, 'quantity' => 3, 'total' => 120.0], TotalOrder::find()
                ->where(['item_id' => 2])->asArray()->one());
        TotalOrder::find()->select(['quantity'])->column();
        $this->assertEquals([8.0, 10.0, 15.0, 30.0, 120.0], TotalOrder::find()
                ->select(['total'])->orderBy(['total' => SORT_ASC])->column());
    }

    public function testRelation()
    {
        $items = Item::find()
            ->alias('i')
            ->joinWith('totalOrder t')
            ->where(['i.category_id' => 2])
            ->orderBy(['t.total' => SORT_ASC])
            ->all();

        $this->assertEquals(3, count($items));
        $this->assertEquals('Ice Age', $items[0]->name);
        $this->assertEquals('Toy Story', $items[1]->name);

        $totals = [30.0, 120.0, 8.0, 10.0, 15.0];
        foreach (Item::find()->with(['totalOrder'])->orderBy(['id' => SORT_ASC])->all() as $i => $item) {
            $this->assertEquals($totals[$i], $item->totalOrder->total);
        }
    }
}
