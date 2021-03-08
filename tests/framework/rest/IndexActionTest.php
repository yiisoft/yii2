<?php

namespace yiiunit\framework\rest;

use Yii;
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
