<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\data;

use yii\data\Sort;
use yii\web\UrlManager;
use yiiunit\TestCase;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 *
 * @group data
 */
class SortTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testGetOrders()
    {
        $sort = new Sort([
            'attributes' => [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ],
            'params' => [
                'sort' => 'age,-name',
            ],
            'enableMultiSort' => true,
        ]);

        $orders = $sort->getOrders();
        $this->assertCount(3, $orders);
        $this->assertEquals(SORT_ASC, $orders['age']);
        $this->assertEquals(SORT_DESC, $orders['first_name']);
        $this->assertEquals(SORT_DESC, $orders['last_name']);

        $sort->enableMultiSort = false;
        $orders = $sort->getOrders(true);
        $this->assertCount(1, $orders);
        $this->assertEquals(SORT_ASC, $orders['age']);
    }

    /**
     * @depends testGetOrders
     */
    public function testGetAttributeOrders()
    {
        $sort = new Sort([
            'attributes' => [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ],
            'params' => [
                'sort' => 'age,-name',
            ],
            'enableMultiSort' => true,
        ]);

        $orders = $sort->getAttributeOrders();
        $this->assertCount(2, $orders);
        $this->assertEquals(SORT_ASC, $orders['age']);
        $this->assertEquals(SORT_DESC, $orders['name']);

        $sort->enableMultiSort = false;
        $orders = $sort->getAttributeOrders(true);
        $this->assertCount(1, $orders);
        $this->assertEquals(SORT_ASC, $orders['age']);
    }

    /**
     * @depends testGetAttributeOrders
     */
    public function testGetAttributeOrder()
    {
        $sort = new Sort([
            'attributes' => [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ],
            'params' => [
                'sort' => 'age,-name',
            ],
            'enableMultiSort' => true,
        ]);

        $this->assertEquals(SORT_ASC, $sort->getAttributeOrder('age'));
        $this->assertEquals(SORT_DESC, $sort->getAttributeOrder('name'));
        $this->assertNull($sort->getAttributeOrder('xyz'));
    }

    /**
     * @depends testGetAttributeOrders
     */
    public function testSetAttributeOrders()
    {
        $sort = new Sort([
            'attributes' => [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ],
            'params' => [
                'sort' => 'age,-name',
            ],
            'enableMultiSort' => true,
        ]);

        $orders = [
            'age' => SORT_DESC,
            'name' => SORT_ASC,
        ];
        $sort->setAttributeOrders($orders);
        $this->assertEquals($orders, $sort->getAttributeOrders());

        $sort->enableMultiSort = false;
        $sort->setAttributeOrders($orders);
        $this->assertEquals(['age' => SORT_DESC], $sort->getAttributeOrders());
        $sort->setAttributeOrders($orders, false);
        $this->assertEquals($orders, $sort->getAttributeOrders());

        $orders = ['unexistingAttribute' => SORT_ASC];
        $sort->setAttributeOrders($orders);
        $this->assertEquals([], $sort->getAttributeOrders());
        $sort->setAttributeOrders($orders, false);
        $this->assertEquals($orders, $sort->getAttributeOrders());
    }

    public function testCreateSortParam()
    {
        $sort = new Sort([
            'attributes' => [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ],
            'params' => [
                'sort' => 'age,-name',
            ],
            'enableMultiSort' => true,
            'route' => 'site/index',
        ]);

        $this->assertEquals('-age,-name', $sort->createSortParam('age'));
        $this->assertEquals('name,age', $sort->createSortParam('name'));
    }

    public function testCreateUrl()
    {
        $manager = new UrlManager([
            'baseUrl' => '/',
            'ScriptUrl' => '/index.php',
            'cache' => null,
        ]);

        $sort = new Sort([
            'attributes' => [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ],
            'params' => [
                'sort' => 'age,-name',
            ],
            'enableMultiSort' => true,
            'urlManager' => $manager,
            'route' => 'site/index',
        ]);

        $this->assertEquals('/index.php?r=site%2Findex&sort=-age%2C-name', $sort->createUrl('age'));
        $this->assertEquals('/index.php?r=site%2Findex&sort=name%2Cage', $sort->createUrl('name'));
    }

    /**
     * @depends testCreateUrl
     */
    public function testLink()
    {
        $this->mockApplication();
        $manager = new UrlManager([
            'baseUrl' => '/',
            'scriptUrl' => '/index.php',
            'cache' => null,
        ]);

        $sort = new Sort([
            'attributes' => [
                'age',
                'name' => [
                    'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                    'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                ],
            ],
            'params' => [
                'sort' => 'age,-name',
            ],
            'enableMultiSort' => true,
            'urlManager' => $manager,
            'route' => 'site/index',
        ]);

        $this->assertEquals('<a class="asc" href="/index.php?r=site%2Findex&amp;sort=-age%2C-name" data-sort="-age,-name">Age</a>', $sort->link('age'));
    }

    public function testParseSortParam()
    {
        $sort = new CustomSort([
            'attributes' => [
                'age',
                'name',
            ],
            'params' => [
                'sort' => [
                    ['field' => 'age', 'dir' => 'asc'],
                    ['field' => 'name', 'dir' => 'desc'],
                ],
            ],
            'enableMultiSort' => true,
        ]);

        $this->assertEquals(SORT_ASC, $sort->getAttributeOrder('age'));
        $this->assertEquals(SORT_DESC, $sort->getAttributeOrder('name'));
    }

    /**
     * @depends testGetOrders
     *
     * @see https://github.com/yiisoft/yii2/pull/13260
     */
    public function testGetExpressionOrders()
    {
        $sort = new Sort([
            'attributes' => [
                'name' => [
                    'asc' => '[[last_name]] ASC NULLS FIRST',
                    'desc' => '[[last_name]] DESC NULLS LAST',
                ],
            ],
        ]);

        $sort->params = ['sort' => '-name'];
        $orders = $sort->getOrders();
        $this->assertEquals(1, count($orders));
        $this->assertEquals('[[last_name]] DESC NULLS LAST', $orders[0]);

        $sort->params = ['sort' => 'name'];
        $orders = $sort->getOrders(true);
        $this->assertEquals(1, count($orders));
        $this->assertEquals('[[last_name]] ASC NULLS FIRST', $orders[0]);
    }
}

class CustomSort extends Sort
{
    protected function parseSortParam($params)
    {
        $attributes = [];
        foreach ($params as $item) {
            $attributes[] = ($item['dir'] == 'desc') ? '-' . $item['field'] : $item['field'];
        }
        return $attributes;
    }
}
