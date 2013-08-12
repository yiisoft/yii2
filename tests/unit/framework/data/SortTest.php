<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\data;

use yii\web\UrlManager;
use yiiunit\TestCase;
use yii\data\Sort;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class SortTest extends TestCase
{
	public function testGetOrders()
	{
		$sort = new Sort(array(
			'attributes' => array(
				'age',
				'name' => array(
					'asc' => array('first_name' => Sort::ASC, 'last_name' => Sort::ASC),
					'desc' => array('first_name' => Sort::DESC, 'last_name' => Sort::DESC),
				),
			),
			'params' => array(
				'sort' => 'age.name-desc'
			),
			'enableMultiSort' => true,
		));

		$orders = $sort->getOrders();
		$this->assertEquals(3, count($orders));
		$this->assertEquals(Sort::ASC, $orders['age']);
		$this->assertEquals(Sort::DESC, $orders['first_name']);
		$this->assertEquals(Sort::DESC, $orders['last_name']);

		$sort->enableMultiSort = false;
		$orders = $sort->getOrders(true);
		$this->assertEquals(1, count($orders));
		$this->assertEquals(Sort::ASC, $orders['age']);
	}

	public function testGetAttributeOrders()
	{
		$sort = new Sort(array(
			'attributes' => array(
				'age',
				'name' => array(
					'asc' => array('first_name' => Sort::ASC, 'last_name' => Sort::ASC),
					'desc' => array('first_name' => Sort::DESC, 'last_name' => Sort::DESC),
				),
			),
			'params' => array(
				'sort' => 'age.name-desc'
			),
			'enableMultiSort' => true,
		));

		$orders = $sort->getAttributeOrders();
		$this->assertEquals(2, count($orders));
		$this->assertEquals(Sort::ASC, $orders['age']);
		$this->assertEquals(Sort::DESC, $orders['name']);

		$sort->enableMultiSort = false;
		$orders = $sort->getAttributeOrders(true);
		$this->assertEquals(1, count($orders));
		$this->assertEquals(Sort::ASC, $orders['age']);
	}

	public function testGetAttributeOrder()
	{
		$sort = new Sort(array(
			'attributes' => array(
				'age',
				'name' => array(
					'asc' => array('first_name' => Sort::ASC, 'last_name' => Sort::ASC),
					'desc' => array('first_name' => Sort::DESC, 'last_name' => Sort::DESC),
				),
			),
			'params' => array(
				'sort' => 'age.name-desc'
			),
			'enableMultiSort' => true,
		));

		$this->assertEquals(Sort::ASC, $sort->getAttributeOrder('age'));
		$this->assertEquals(Sort::DESC, $sort->getAttributeOrder('name'));
		$this->assertNull($sort->getAttributeOrder('xyz'));
	}

	public function testCreateSortVar()
	{
		$sort = new Sort(array(
			'attributes' => array(
				'age',
				'name' => array(
					'asc' => array('first_name' => Sort::ASC, 'last_name' => Sort::ASC),
					'desc' => array('first_name' => Sort::DESC, 'last_name' => Sort::DESC),
				),
			),
			'params' => array(
				'sort' => 'age.name-desc'
			),
			'enableMultiSort' => true,
			'route' => 'site/index',
		));

		$this->assertEquals('age-desc.name-desc', $sort->createSortVar('age'));
		$this->assertEquals('name.age', $sort->createSortVar('name'));
	}

	public function testCreateUrl()
	{
		$manager = new UrlManager(array(
			'baseUrl' => '/index.php',
			'cache' => null,
		));

		$sort = new Sort(array(
			'attributes' => array(
				'age',
				'name' => array(
					'asc' => array('first_name' => Sort::ASC, 'last_name' => Sort::ASC),
					'desc' => array('first_name' => Sort::DESC, 'last_name' => Sort::DESC),
				),
			),
			'params' => array(
				'sort' => 'age.name-desc'
			),
			'enableMultiSort' => true,
			'urlManager' => $manager,
			'route' => 'site/index',
		));

		$this->assertEquals('/index.php?r=site/index&sort=age-desc.name-desc', $sort->createUrl('age'));
		$this->assertEquals('/index.php?r=site/index&sort=name.age', $sort->createUrl('name'));
	}

	public function testLink()
	{
		$this->mockApplication();
		$manager = new UrlManager(array(
			'baseUrl' => '/index.php',
			'cache' => null,
		));

		$sort = new Sort(array(
			'attributes' => array(
				'age',
				'name' => array(
					'asc' => array('first_name' => Sort::ASC, 'last_name' => Sort::ASC),
					'desc' => array('first_name' => Sort::DESC, 'last_name' => Sort::DESC),
				),
			),
			'params' => array(
				'sort' => 'age.name-desc'
			),
			'enableMultiSort' => true,
			'urlManager' => $manager,
			'route' => 'site/index',
		));

		$this->assertEquals('<a class="asc" href="/index.php?r=site/index&amp;sort=age-desc.name-desc" data-sort="age-desc.name-desc">Age</a>', $sort->link('age'));
	}
}
