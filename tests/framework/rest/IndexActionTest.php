<?php

namespace yiiunit\framework\rest;

use Yii;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\data\Sort;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\rest\ActiveController;
use yii\rest\IndexAction;
use yiiunit\framework\filters\stubs\UserIdentity;
use yiiunit\TestCase;

/**
 * @group rest
 */
class IndexActionTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockWebApplication([
            'components' => [
                'db' => [
                    'class' => '\yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ],
                'user' => [
                    'identityClass' => UserIdentity::className(),
                ],
            ],
        ]);
        $columns = [
            'id' => 'pk',
            'name' => 'string',
        ];
        Yii::$app->getDb()->createCommand()->createTable(IndexActionModel::tableName(), $columns)->execute();
    }

    public function testPrepareSearchQueryAttribute()
    {
        $sql = '';
        Yii::$app->controller = new RestController(
            'rest',
            new Module('rest'), [
            'modelClass' => IndexActionModel::className(),
            'actions' => [
                'index' => [
                    'class' => IndexAction::className(),
                    'modelClass' => IndexActionModel::className(),
                    'prepareSearchQuery' => function ($query, $requestParams) use (&$sql) {
                        $this->assertTrue($query instanceof Query);
                        $sql = $query->createCommand()->getRawSql();

                        return $query;
                    },
                ],
            ],
        ]);
        Yii::$app->controller->run('index');

        $this->assertEquals(
            'SELECT * FROM `' . IndexActionModel::tableName() . '`',
            $sql
        );
    }

    /**
     * @dataProvider dataProviderTestPrepareDataProviderWithPaginationAndSorting
     *
     * @param string $sql
     * @param array $params
     * @param string $expectedRawSql
     */
    public function testPrepareDataProviderWithPaginationAndSorting(
        $pagination,
        $sort,
        $expectedPaginationPageSize = null,
        $expectedPaginationDefaultPageSize = null,
        $expectedSortOrders = [],
        $expectedSortDefaultOrder = null
    ) {
        Yii::$app->getRequest()->setBodyParams([
            'per-page' => 11,
            'sort' => '-test-sort'
        ]);

        $controller = new RestController(
            'rest',
            new Module('rest'), [
            'modelClass' => IndexActionModel::className(),
            'actions' => [
                'index' => [
                    'class' => IndexAction::className(),
                    'modelClass' => IndexActionModel::className(),
                    'pagination' => $pagination,
                    'sort' => $sort,
                ],
            ],
        ]);

        /** @var ActiveDataProvider $dataProvider */
        $dataProvider = $controller->createAction('index')->runWithParams([]);
        $actualPagination = $dataProvider->getPagination();
        $actualSort = $dataProvider->getSort();

        if ($pagination === false) {
            $this->assertFalse($actualPagination);
        } else {
            $this->assertEquals($expectedPaginationPageSize, $actualPagination->pageSize);
            $this->assertEquals($expectedPaginationDefaultPageSize, $actualPagination->defaultPageSize);
        }

        if ($sort === false) {
            $this->assertFalse($actualSort);
        } else {
            $this->assertEquals($expectedSortOrders, $actualSort->getOrders());
            $this->assertEquals($expectedSortDefaultOrder, $actualSort->defaultOrder);
        }
    }

    /**
     * Data provider for [[testPrepareDataProviderWithPaginationAndSorting()]].
     * @return array test data
     */
    public function dataProviderTestPrepareDataProviderWithPaginationAndSorting()
    {
        return [
            [ // Default config
                [],
                [],
                11, // page size set as param in test
                (new Pagination())->defaultPageSize,
                [],
                null
            ],
            [ // Default config
                [],
                [
                    'attributes' => ['test-sort'],
                ],
                11, // page size set as param in test
                (new Pagination())->defaultPageSize,
                ['test-sort' => SORT_DESC], // test sort set as param in test
                null
            ],
            [ // Config via array
                [
                    'pageSize' => 12, // Testing a fixed page size
                    'defaultPageSize' => 991,
                ],
                [
                    'attributes' => ['test-sort'],
                    'defaultOrder' => [
                        'created_at_1' => SORT_DESC,
                    ],
                ],
                12,
                991,
                ['test-sort' => SORT_DESC], // test sort set as param in test
                ['created_at_1' => SORT_DESC]
            ],
            [ // Config via objects
                new Pagination([
                    'defaultPageSize' => 992,
                ]),
                new Sort([
                    'attributes' => ['created_at_2'],
                    'defaultOrder' => [
                        'created_at_2' => SORT_DESC,
                    ],
                ]),
                11, // page size set as param in test
                992,
                [], // sort param is set so no default sorting anymore
                ['created_at_2' => SORT_DESC]
            ],
            [ // Disable pagination and sort
                false,
                false,
            ]
        ];
    }
}

class RestController extends ActiveController
{
    public $actions = [];

    public function actions()
    {
        return $this->actions;
    }
}

class Module extends \yii\base\Module
{

}

/**
 * Test Active Record class with [[TimestampBehavior]] behavior attached.
 *
 * @property int $id
 * @property int $name
 */
class IndexActionModel extends ActiveRecord
{
    public static $tableName = 'test_index_action';

    public static function tableName()
    {
        return static::$tableName;
    }
}
