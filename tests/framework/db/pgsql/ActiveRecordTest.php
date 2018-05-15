<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\pgsql;

use yii\behaviors\TimestampBehavior;
use yii\db\ArrayExpression;
use yii\db\Expression;
use yii\db\ExpressionInterface;
use yii\db\JsonExpression;
use yii\db\pgsql\Schema;
use yii\helpers\Json;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\DefaultPk;
use yiiunit\framework\ar\ActiveRecordTestTrait;
use yiiunit\TestCase;

/**
 * @group db
 * @group pgsql
 */
class ActiveRecordTest extends \yiiunit\framework\db\ActiveRecordTest
{
    protected $driverName = 'pgsql';

    public function testBooleanAttribute()
    {
        /* @var $customerClass \yii\db\ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();
        /* @var $this TestCase|ActiveRecordTestTrait */
        $customer = new $customerClass();
        $customer->name = 'boolean customer';
        $customer->email = 'mail@example.com';
        $customer->bool_status = false;
        $customer->save(false);

        $customer->refresh();
        $this->assertFalse($customer->bool_status);

        $customer->bool_status = true;
        $customer->save(false);

        $customer->refresh();
        $this->assertTrue($customer->bool_status);

        $customers = $customerClass::find()->where(['bool_status' => true])->all();
        $this->assertCount(3, $customers);

        $customers = $customerClass::find()->where(['bool_status' => false])->all();
        $this->assertCount(1, $customers);
    }

    public function testFindAsArray()
    {
        /* @var $customerClass \yii\db\ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();

        // asArray
        $customer = $customerClass::find()->where(['id' => 2])->asArray()->one();
        $this->assertEquals([
            'id' => 2,
            'email' => 'user2@example.com',
            'name' => 'user2',
            'address' => 'address2',
            'status' => 1,
            'profile_id' => null,
            'bool_status' => true,
        ], $customer);

        // find all asArray
        $customers = $customerClass::find()->asArray()->all();
        $this->assertCount(3, $customers);
        $this->assertArrayHasKey('id', $customers[0]);
        $this->assertArrayHasKey('name', $customers[0]);
        $this->assertArrayHasKey('email', $customers[0]);
        $this->assertArrayHasKey('address', $customers[0]);
        $this->assertArrayHasKey('status', $customers[0]);
        $this->assertArrayHasKey('bool_status', $customers[0]);
        $this->assertArrayHasKey('id', $customers[1]);
        $this->assertArrayHasKey('name', $customers[1]);
        $this->assertArrayHasKey('email', $customers[1]);
        $this->assertArrayHasKey('address', $customers[1]);
        $this->assertArrayHasKey('status', $customers[1]);
        $this->assertArrayHasKey('bool_status', $customers[1]);
        $this->assertArrayHasKey('id', $customers[2]);
        $this->assertArrayHasKey('name', $customers[2]);
        $this->assertArrayHasKey('email', $customers[2]);
        $this->assertArrayHasKey('address', $customers[2]);
        $this->assertArrayHasKey('status', $customers[2]);
        $this->assertArrayHasKey('bool_status', $customers[2]);
    }

    public function testBooleanValues()
    {
        $db = $this->getConnection();
        $command = $db->createCommand();
        $command->batchInsert('bool_values',
            ['bool_col'], [
                [true],
                [false],
            ]
        )->execute();

        $this->assertEquals(1, BoolAR::find()->where('bool_col = TRUE')->count('*', $db));
        $this->assertEquals(1, BoolAR::find()->where('bool_col = FALSE')->count('*', $db));
        $this->assertEquals(2, BoolAR::find()->where('bool_col IN (TRUE, FALSE)')->count('*', $db));

        $this->assertEquals(1, BoolAR::find()->where(['bool_col' => true])->count('*', $db));
        $this->assertEquals(1, BoolAR::find()->where(['bool_col' => false])->count('*', $db));
        $this->assertEquals(2, BoolAR::find()->where(['bool_col' => [true, false]])->count('*', $db));

        $this->assertEquals(1, BoolAR::find()->where('bool_col = :bool_col', ['bool_col' => true])->count('*', $db));
        $this->assertEquals(1, BoolAR::find()->where('bool_col = :bool_col', ['bool_col' => false])->count('*', $db));

        $this->assertTrue(BoolAR::find()->where(['bool_col' => true])->one($db)->bool_col);
        $this->assertFalse(BoolAR::find()->where(['bool_col' => false])->one($db)->bool_col);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/4672
     */
    public function testBooleanValues2()
    {
        $db = $this->getConnection();
        $db->charset = 'utf8';

        $db->createCommand('DROP TABLE IF EXISTS bool_user;')->execute();
        $db->createCommand()->createTable('bool_user', [
            'id' => Schema::TYPE_PK,
            'username' => Schema::TYPE_STRING . ' NOT NULL',
            'auth_key' => Schema::TYPE_STRING . '(32) NOT NULL',
            'password_hash' => Schema::TYPE_STRING . ' NOT NULL',
            'password_reset_token' => Schema::TYPE_STRING,
            'email' => Schema::TYPE_STRING . ' NOT NULL',
            'role' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 10',

            'status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 10',
            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
        ])->execute();
        $db->createCommand()->addColumn('bool_user', 'is_deleted', Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT FALSE')->execute();

        $user = new UserAR();
        $user->username = 'test';
        $user->auth_key = 'test';
        $user->password_hash = 'test';
        $user->email = 'test@example.com';
        $user->save(false);

        $this->assertCount(1, UserAR::find()->where(['is_deleted' => false])->all($db));
        $this->assertCount(0, UserAR::find()->where(['is_deleted' => true])->all($db));
        $this->assertCount(1, UserAR::find()->where(['is_deleted' => [true, false]])->all($db));
    }

    public function testBooleanDefaultValues()
    {
        $model = new BoolAR();
        $this->assertNull($model->bool_col);
        $this->assertNull($model->default_true);
        $this->assertNull($model->default_false);
        $model->loadDefaultValues();
        $this->assertNull($model->bool_col);
        $this->assertTrue($model->default_true);
        $this->assertFalse($model->default_false);

        $this->assertTrue($model->save(false));
    }

    public function testPrimaryKeyAfterSave()
    {
        $record = new DefaultPk();
        $record->type = 'type';
        $record->save(false);
        $this->assertEquals(5, $record->primaryKey);
    }

    /**
     * @dataProvider arrayValuesProvider $attributes
     */
    public function testArrayValues($attributes)
    {
        $type = new ArrayAndJsonTypes();
        foreach ($attributes as $attribute => $expected) {
            $type->$attribute = $expected[0];
        }
        $type->save();

        $type = ArrayAndJsonTypes::find()->one();
        foreach ($attributes as $attribute => $expected) {
            $expected = $expected[1] ?? $expected[0];
            $value = $type->$attribute;

            $this->assertEquals($expected, $value, 'In column ' . $attribute);

            if ($value instanceof ArrayExpression) {
                $this->assertInstanceOf('\ArrayAccess', $value);
                $this->assertInstanceOf('\Traversable', $value);
                foreach ($type->$attribute as $key => $v) { // testing arrayaccess
                    $this->assertSame($expected[$key], $value[$key]);
                }
            }
        }

        // Testing UPDATE
        foreach ($attributes as $attribute => $expected) {
            $type->markAttributeDirty($attribute);
        }
        $this->assertSame(1, $type->update(), 'The record got updated');
    }

    public function arrayValuesProvider()
    {
        return [
            'simple arrays values' => [[
                'intarray_col' => [
                    new ArrayExpression([1,-2,null,'42'], 'int4', 1),
                    new ArrayExpression([1,-2,null,42], 'int4', 1),
                ],
                'textarray2_col' => [
                    new ArrayExpression([['text'], [null], [1]], 'text', 2),
                    new ArrayExpression([['text'], [null], ['1']], 'text', 2),
                ],
                'json_col' => [['a' => 1, 'b' => null, 'c' => [1,3,5]]],
                'jsonb_col' => [[null, 'a', 'b', '\"', '{"af"}']],
                'jsonarray_col' => [new ArrayExpression([[',', 'null', true, 'false', 'f']], 'json')],
            ]],
            'null arrays values' => [[
                'intarray_col' => [
                    null,
                ],
                'textarray2_col' => [
                    [null, null],
                    new ArrayExpression([null, null], 'text', 2),
                ],
                'json_col' => [
                    null
                ],
                'jsonarray_col' => [
                    null
                ],
            ]],
            'empty arrays values' => [[
                'textarray2_col' => [
                    [[], []],
                    new ArrayExpression([], 'text', 2),
                ],
            ]],
            'nested objects' => [[
                'intarray_col' => [
                    new ArrayExpression(new ArrayExpression([1,2,3]), 'int', 1),
                    new ArrayExpression([1,2,3], 'int4', 1),
                ],
                'textarray2_col' => [
                    new ArrayExpression([new ArrayExpression(['text']), [null], [1]], 'text', 2),
                    new ArrayExpression([['text'], [null], ['1']], 'text', 2),
                ],
                'json_col' => [
                    new JsonExpression(new JsonExpression(new JsonExpression(['a' => 1, 'b' => null, 'c' => new JsonExpression([1,3,5])]))),
                    ['a' => 1, 'b' => null, 'c' => [1,3,5]]
                ],
                'jsonb_col' => [
                    new JsonExpression(new ArrayExpression([1,2,3])),
                    [1,2,3]
                ],
                'jsonarray_col' => [
                    new ArrayExpression([new JsonExpression(['1', 2]), [3,4,5]], 'json'),
                    new ArrayExpression([['1', 2], [3,4,5]], 'json')
                ]
            ]],
            'arrays packed in classes' => [[
                'intarray_col' => [
                    new ArrayExpression([1,-2,null,'42'], 'int', 1),
                    new ArrayExpression([1,-2,null,42], 'int4', 1),
                ],
                'textarray2_col' => [
                    new ArrayExpression([['text'], [null], [1]], 'text', 2),
                    new ArrayExpression([['text'], [null], ['1']], 'text', 2),
                ],
                'json_col' => [
                    new JsonExpression(['a' => 1, 'b' => null, 'c' => [1,3,5]]),
                    ['a' => 1, 'b' => null, 'c' => [1,3,5]]
                ],
                'jsonb_col' => [
                    new JsonExpression([null, 'a', 'b', '\"', '{"af"}']),
                    [null, 'a', 'b', '\"', '{"af"}']
                ],
                'jsonarray_col' => [
                    new Expression("array['[\",\",\"null\",true,\"false\",\"f\"]'::json]::json[]"),
                    new ArrayExpression([[',', 'null', true, 'false', 'f']], 'json'),
                ]
            ]],
            'scalars' => [[
                'json_col' => [
                    '5.8',
                ],
                'jsonb_col' => [
                    pi()
                ],
            ]],
        ];
    }
}

class BoolAR extends ActiveRecord
{
    public static function tableName()
    {
        return 'bool_values';
    }
}

class UserAR extends ActiveRecord
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;
    const ROLE_USER = 10;

    public static function tableName()
    {
        return '{{%bool_user}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}

/**
 * {@inheritdoc}
 * @property array id
 * @property array intarray_col
 * @property array textarray2_col
 * @property array json_col
 * @property array jsonb_col
 * @property array jsonarray_col
 */
class ArrayAndJsonTypes extends ActiveRecord
{
}
