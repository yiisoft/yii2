<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql;

use yii\db\mysql\QueryBuilder;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Storage;
use yiiunit\data\ar\Type;
use yiiunit\base\db\BaseActiveRecord;
use yiiunit\support\DbHelper;

/**
 * @group db
 * @group mysql
 */
class ActiveRecordTest extends BaseActiveRecord
{
    public $driverName = 'mysql';
    protected static string $driverNameStatic = 'mysql';

    /**
     * @see https://github.com/yiisoft/yii2/issues/20275
     */
    public function testLoadEmptyTextDefaultValue(): void
    {
        $db = $this->getConnection();

        DbHelper::dropTablesIfExist($db, ['empty_text_default_test']);

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $db->getQueryBuilder();

        $default = $queryBuilder->isMariaDb() ? "''" : "('')";

        $db->createCommand()->createTable(
            'empty_text_default_test',
            ['key' => "tinytext NOT NULL DEFAULT {$default}"],
            'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        )->execute();

        $model = new class extends ActiveRecord {
            public static function tableName()
            {
                return 'empty_text_default_test';
            }
        };

        $model->loadDefaultValues();

        self::assertSame(
            '',
            $model->getAttribute('key'),
            'An empty text default must load as an empty PHP string.',
        );

        DbHelper::dropTablesIfExist($db, ['empty_text_default_test']);
    }

    public function testCastValues(): void
    {
        $model = new Type();
        $model->int_col = 123;
        $model->int_col2 = 456;
        $model->smallint_col = 42;
        $model->char_col = '1337';
        $model->char_col2 = 'test';
        $model->char_col3 = 'test123';
        $model->float_col = 3.742;
        $model->float_col2 = 42.1337;
        $model->bool_col = true;
        $model->bool_col2 = false;
        $model->save(false);

        /** @var Type $model */
        $model = Type::find()->one();
        $this->assertSame(123, $model->int_col);
        $this->assertSame(456, $model->int_col2);
        $this->assertSame(42, $model->smallint_col);
        $this->assertSame('1337', trim((string) $model->char_col));
        $this->assertSame('test', $model->char_col2);
        $this->assertSame('test123', $model->char_col3);
        $this->assertSame(3.742, $model->float_col);
        $this->assertSame(42.1337, $model->float_col2);
        $this->assertSame(1, $model->bool_col);
        $this->assertSame(0, $model->bool_col2);
    }

    public function testJsonColumn(): void
    {
        $data = [
            'obj' => ['a' => ['b' => ['c' => 2.7418]]],
            'array' => [1,2,null,3],
            'null_field' => null,
            'boolean_field' => true,
            'last_update_time' => '2018-02-21',
        ];

        $storage = new Storage(['data' => $data]);
        $this->assertTrue($storage->save(), 'Storage can be saved');
        $this->assertNotNull($storage->id);

        $retrievedStorage = Storage::findOne($storage->id);
        $this->assertSame($data, $retrievedStorage->data, 'Properties are restored from JSON to array without changes');

        $retrievedStorage->data = ['updatedData' => $data];
        $this->assertSame(1, $retrievedStorage->update(), 'Storage can be updated');

        $retrievedStorage->refresh();
        $this->assertSame(['updatedData' => $data], $retrievedStorage->data, 'Properties have been changed during update');
    }
}
