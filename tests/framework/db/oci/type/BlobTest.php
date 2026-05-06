<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci\type;

use PDO;
use PHPUnit\Framework\Attributes\Group;
use yii\db\Expression;
use yii\db\PdoValue;
use yii\db\Query;
use yii\db\Schema;
use yii\db\oci\ColumnSchema;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * Unit test for {@see \yii\db\oci\ColumnSchema} with Oracle BLOB type.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('oci')]
#[Group('column')]
final class BlobTest extends DatabaseTestCase
{
    protected $driverName = 'oci';

    public function testDbTypecastReturnsExpressionForBlobString(): void
    {
        $value = 'a:1:{s:13:"template";s:1:"1";}';

        $result = $this->makeBlobColumn()->dbTypecast($value);

        $this->assertBlobExpression($result, $value);
    }

    public function testDbTypecastReturnsExpressionForPdoValueStringLob(): void
    {
        $value = 'binary content';

        $result = $this->makeBlobColumn()->dbTypecast(new PdoValue($value, PDO::PARAM_LOB));

        $this->assertBlobExpression($result, $value);
    }

    public function testDbTypecastPreservesPdoValueResource(): void
    {
        $resource = fopen('php://memory', 'rb+');

        fwrite($resource, 'binary content');
        rewind($resource);

        $pdoValue = new PdoValue($resource, PDO::PARAM_LOB);

        $result = $this->makeBlobColumn()->dbTypecast($pdoValue);

        self::assertSame(
            $pdoValue,
            $result,
            'Explicit PdoValue resource instances must be delegated without unwrapping.',
        );

        fclose($resource);
    }

    public function testBlob(): void
    {
        $db = $this->getConnection();

        $db->createCommand()->delete('type')->execute();
        $db->createCommand()->insert(
            'type',
            [
                'int_col' => $key = 1,
                'char_col' => 'test',
                'char_col2' => '6a3ce1a0bffe8eeb6fa986caf443e24c',
                'float_col' => 0.0,
                'blob_col' => 'a:1:{s:13:"template";s:1:"1";}',
                'bool_col' => 1,
            ],
        )->execute();

        $result = (new Query())
            ->select(['blob_col'])
            ->from('type')
            ->where(['int_col' => $key])
            ->createCommand($db)
            ->queryScalar();

        self::assertSame(
            'a:1:{s:13:"template";s:1:"1";}',
            $result,
            'BLOB column should return the exact serialized string that was inserted.',
        );

        $updatedBlob = 'a:2:{s:13:"template";s:1:"2";s:4:"name";s:4:"test";}';

        $db->createCommand()->update(
            'type',
            ['blob_col' => $updatedBlob],
            ['int_col' => $key],
        )->execute();

        $result = (new Query())
            ->select(['blob_col'])
            ->from('type')
            ->where(['int_col' => $key])
            ->createCommand($db)
            ->queryScalar();

        self::assertSame(
            $updatedBlob,
            $result,
            'BLOB column should return the exact serialized string after update.',
        );
    }

    private function makeBlobColumn(): ColumnSchema
    {
        $column = new ColumnSchema();

        $column->type = Schema::TYPE_BINARY;

        $column->phpType = 'resource';
        $column->dbType = 'BLOB';
        $column->allowNull = true;

        return $column;
    }

    private function assertBlobExpression(mixed $result, string $value): void
    {
        self::assertInstanceOf(
            Expression::class,
            $result,
            'BLOB strings must be wrapped in an Oracle BLOB expression.',
        );

        $placeholder = array_key_first($result->params);

        self::assertIsString(
            $placeholder,
            'BLOB expression must define a named placeholder.',
        );
        self::assertStringStartsWith(
            ':qp',
            $placeholder,
            'BLOB expression placeholder must use the query parameter prefix.',
        );
        self::assertSame(
            'TO_BLOB(UTL_RAW.CAST_TO_RAW(' . $placeholder . '))',
            (string) $result,
            'BLOB expression SQL must wrap the generated placeholder.',
        );
        self::assertSame(
            [$placeholder => $value],
            $result->params,
            'BLOB expression params must contain the original string value.',
        );
    }
}
