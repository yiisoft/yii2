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
use yii\base\NotSupportedException;
use yii\db\Expression;
use yii\db\PdoValue;
use yii\db\Query;
use yiiunit\framework\db\DatabaseTestCase;
use yiiunit\support\DbHelper;
use function fclose;
use function fopen;
use function rewind;
use function strlen;
use function strrev;

/**
 * Integration tests for Oracle BLOB binding.
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

    public function testInsertRoundTripsBinaryPayloadSizes(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->delete('type')->execute();

        foreach ($this->blobPayloads() as $index => $payload) {
            $id = $index + 1;

            $command->insert(
                'type',
                $this->rowValues($id, $payload),
            )->execute();

            self::assertSame(
                $payload,
                $this->readBlob($id),
                "BLOB bytes must round-trip exactly for row {$id}.",
            );
        }
    }

    public function testUpdateRoundTripsBinaryPayloadSizes(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->delete('type')->execute();
        $command->insert(
            'type',
            $this->rowValues(1, 'initial'),
        )->execute();

        foreach ($this->blobPayloads() as $payload) {
            self::assertSame(
                1,
                $command->update(
                    'type',
                    ['blob_col' => $payload],
                    ['int_col' => 1],
                )->execute(),
                'Exactly one row must be updated.',
            );
            self::assertSame(
                $payload,
                $this->readBlob(1),
                'BLOB bytes must round-trip exactly for row 1.',
            );
        }
    }

    /**
     * Documented limitation: Oracle `RETURNING lob INTO :p` binds one scalar locator, so a multi-row UPDATE fills
     * only one matched row. Multi-row BLOB UPDATE is unsupported and is deliberately not split into N statements.
     */
    public function testMultiRowBlobUpdateFillsOnlyOneMatchedRow(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->delete('type')->execute();
        $command->insert(
            'type',
            $this->rowValues(1, 'first'),
        )->execute();
        $command->insert(
            'type',
            $this->rowValues(2, 'second'),
        )->execute();

        $payload = $this->createPayload(65_536);

        $command->update(
            'type',
            ['blob_col' => $payload],
            ['int_col' => [1, 2]],
        )->execute();

        $filled = 0;

        foreach ([1, 2] as $id) {
            if ($this->readBlob($id) === $payload) {
                $filled++;
            }
        }

        self::assertLessThan(
            2,
            $filled,
            'Multi-row BLOB update must not fill every matched row.',
        );
    }

    public function testPdoValueStringAndStreamRoundTrip(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->delete('type')->execute();

        $stringPayload = $this->createPayload(65_536);
        $streamPayload = $this->createPayload(1_048_576);

        $command->insert(
            'type',
            $this->rowValues(1, new PdoValue($stringPayload, PDO::PARAM_LOB)),
        )->execute();

        $stream = fopen('php://temp', 'w+b');

        self::assertIsResource(
            $stream,
            'Fixture stream must open.',
        );
        self::assertSame(
            strlen($streamPayload),
            fwrite($stream, $streamPayload),
            'Fixture payload must be written.',
        );
        self::assertTrue(
            rewind($stream),
            'Fixture stream must be rewound.',
        );


        $command->insert(
            'type',
            $this->rowValues(2, new PdoValue($stream, PDO::PARAM_LOB)),
        )->execute();

        fclose($stream);

        self::assertSame(
            $stringPayload,
            $this->readBlob(1),
            'BLOB bytes must round-trip exactly for row 1.',
        );
        self::assertSame(
            $streamPayload,
            $this->readBlob(2),
            'BLOB bytes must round-trip exactly for row 2.',
        );
    }

    public function testNullExpressionAndEmptyBlobSemantics(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->delete('type')->execute();
        $command->insert(
            'type',
            $this->rowValues(1, null),
        )->execute();
        $command->insert(
            'type',
            $this->rowValues(2, new Expression('EMPTY_BLOB()')),
        )->execute();
        $command->insert(
            'type',
            $this->rowValues(3, ''),
        )->execute();

        self::assertSame(
            '1',
            $db->createCommand(
                <<<SQL
                SELECT CASE WHEN "blob_col" IS NULL THEN 1 ELSE 0 END FROM "type" WHERE "int_col" = 1
                SQL,
            )->queryScalar(),
            'A null PHP value must remain an Oracle NULL.',
        );

        foreach ([2, 3] as $id) {
            self::assertSame(
                '0',
                $db->createCommand(
                    <<<SQL
                    SELECT CASE WHEN "blob_col" IS NULL THEN 1 ELSE 0 END FROM "type" WHERE "int_col" = :id
                    SQL,
                    [':id' => $id],
                )->queryScalar(),
                'An empty BLOB must be a non-NULL locator.',
            );
            self::assertSame(
                '0',
                $db->createCommand(
                    <<<SQL
                    SELECT DBMS_LOB.GETLENGTH("blob_col") FROM "type" WHERE "int_col" = :id
                    SQL,
                    [':id' => $id],
                )->queryScalar(),
                'An empty BLOB must have zero bytes.',
            );
            self::assertSame(
                '',
                $this->readBlob($id),
                "BLOB bytes must round-trip exactly for row {$id}.",
            );
        }
    }

    public function testCallerTransactionControlsBlobCommit(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->delete('type')->execute();

        $transaction = $db->beginTransaction();

        $command->insert(
            'type',
            $this->rowValues(1, $this->createPayload(1_048_576)),
        )->execute();

        $transaction->rollBack();

        self::assertSame(
            '0',
            $db->createCommand(
                <<<SQL
                SELECT COUNT(*) FROM "type" WHERE "int_col" = 1
                SQL,
            )->queryScalar(),
            'LOB binding must not commit a caller-owned transaction.',
        );
    }

    public function testThrowNotSupportedExceptionForBatchInsertWithBlobValue(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->delete('type')->execute();

        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage(
            "Oracle does not support 'batchInsert()' with a BLOB value; insert the rows individually.",
        );

        $command->batchInsert(
            'type',
            [
                'int_col',
                'char_col',
                'float_col',
                'blob_col',
                'bool_col',
            ],
            [
                [1, 'batch-1', 0.0, $this->createPayload(32_768), 1],
            ],
        );
    }

    public function testBatchInsertAllowsNullBlobValues(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->delete('type')->execute();

        self::assertSame(
            2,
            $command->batchInsert(
                'type',
                [
                    'int_col',
                    'char_col',
                    'float_col',
                    'blob_col',
                    'bool_col',
                ],
                [
                    [1, 'batch-1', 0.0, null, 1],
                    [2, 'batch-2', 0.0, null, 1],
                ],
            )->execute(),
            'A batch that targets a BLOB column with only `null` values must not be rejected.',
        );
    }

    public function testUpsertRoundTripsLargeBlobOnInsertAndUpdate(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->delete('type')->execute();
        $command->addPrimaryKey(
            'PK_type_blob',
            'type',
            'int_col',
        )->execute();

        $insertPayload = $this->createPayload(1_048_576);

        $updatePayload = strrev($insertPayload);

        $values = $this->rowValues(1, $insertPayload);

        self::assertSame(
            1,
            $command->upsert(
                'type',
                $values,
            )->execute(),
            'Exactly one row must be inserted.',
        );
        self::assertSame(
            $insertPayload,
            $this->readBlob(1),
            'BLOB bytes must round-trip exactly for row 1.',
        );

        $values['blob_col'] = $updatePayload;

        self::assertSame(
            1,
            $command->upsert(
                'type',
                $values,
            )->execute(),
            'Exactly one row must be updated.',
        );
        self::assertSame(
            $updatePayload,
            $this->readBlob(1),
            'BLOB bytes must round-trip exactly for row 1.',
        );
    }

    public function testUpsertPreservesNulBytesOnInsertAndUpdate(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->delete('type')->execute();
        $command->addPrimaryKey(
            'PK_type_blob_nul',
            'type',
            'int_col',
        )->execute();

        $insertPayload = $this->createPayload(131_072);

        $updatePayload = strrev($insertPayload);

        self::assertSame(
            1,
            $command->upsert(
                'type',
                $this->rowValues(1, $insertPayload),
            )->execute(),
            'Exactly one row must be inserted.',
        );
        self::assertSame(
            $insertPayload,
            $this->readBlob(1),
            'BLOB bytes must round-trip exactly for row 1.',
        );
        self::assertSame(
            1,
            $command->upsert(
                'type',
                $this->rowValues(1, $updatePayload),
            )->execute(),
            'Exactly one row must be updated.',
        );
        self::assertSame(
            $updatePayload,
            $this->readBlob(1),
            'BLOB bytes must round-trip exactly for row 1.',
        );
    }

    public function testUpsertWithResourceLeavesStreamOpen(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->delete('type')->execute();
        $command->addPrimaryKey(
            'PK_type_blob_res',
            'type',
            'int_col',
        )->execute();

        $payload = $this->createPayload(65_536);

        $stream = fopen('php://temp', 'w+b');

        self::assertIsResource(
            $stream,
            'Fixture stream must open.',
        );
        self::assertSame(
            strlen($payload),
            fwrite($stream, $payload),
            'Fixture payload must be written.',
        );
        self::assertTrue(
            rewind($stream),
            'Fixture stream must rewind.',
        );
        self::assertSame(
            1,
            $command->upsert(
                'type',
                $this->rowValues(1, $stream),
            )->execute(),
            'Exactly one row must be inserted.',
        );
        self::assertIsResource(
            $stream,
            'Caller stream must not be closed by the upsert.',
        );
        self::assertSame(
            $payload,
            $this->readBlob(1),
            'BLOB bytes must round-trip exactly for row 1.',
        );

        fclose($stream);
    }

    public function testUpsertHonorsCallerTransactionRollback(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->delete('type')->execute();
        $command->addPrimaryKey(
            'PK_type_blob_txn',
            'type',
            'int_col',
        )->execute();

        $transaction = $db->beginTransaction();

        $command->upsert(
            'type',
            $this->rowValues(1, $this->createPayload(1_048_576)),
        )->execute();

        $transaction->rollBack();

        self::assertSame(
            '0',
            $db->createCommand(
                <<<SQL
                SELECT COUNT(*) FROM "type" WHERE "int_col" = 1
                SQL
            )->queryScalar(),
            'A rolled-back caller transaction must discard the BLOB upsert.',
        );
    }

    public function testUpsertAllowsNullBlobValue(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->delete('type')->execute();
        $command->addPrimaryKey(
            'PK_type_blob_nullups',
            'type',
            'int_col',
        )->execute();

        self::assertSame(
            1,
            $db->createCommand()->upsert(
                'type',
                $this->rowValues(1, null),
            )->execute(),
            'A `null` BLOB value must route through the base MERGE path.',
        );
        self::assertSame(
            '1',
            $db->createCommand(
                <<<SQL
                SELECT CASE WHEN "blob_col" IS NULL THEN 1 ELSE 0 END FROM "type" WHERE "int_col" = 1
                SQL
            )->queryScalar(),
            "The 'null' BLOB must remain an Oracle 'NULL'.",
        );
    }

    public function testUpsertWithoutUpdateColumnsReturnsZeroForExistingRow(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->delete('type')->execute();
        $command->addPrimaryKey(
            'PK_type_blob_noupd',
            'type',
            'int_col',
        )->execute();

        $payload = $this->createPayload(65_536);

        $insertCommand = $command->upsert(
            'type',
            $this->rowValues(1, $payload),
            false,
        );

        self::assertSame(
            <<<SQL
            DECLARE
                affected PLS_INTEGER := 0;
            BEGIN
                BEGIN
                    INSERT INTO "type" ("int_col", "char_col", "float_col", "blob_col", "bool_col") VALUES (:qp0, :qp1, :qp2, EMPTY_BLOB(), :qp4) RETURNING "blob_col" INTO :lob3;
                    affected := SQL%ROWCOUNT;
                EXCEPTION
                    WHEN DUP_VAL_ON_INDEX THEN
                        affected := 0;
                END;
                :yii_upsert_affected := affected;
            END;
            SQL,
            $insertCommand->getSql(),
            'Insert-only upsert must compile to the exact PL/SQL block.',
        );
        self::assertSame(
            1,
            $insertCommand->execute(),
            'Fresh row must report one affected row.',
        );

        self::assertSame(
            $payload,
            $this->readBlob(1),
            'BLOB bytes must round-trip exactly for row 1.',
        );

        self::assertSame(
            0,
            $db->createCommand()->upsert(
                'type',
                $this->rowValues(1, strrev($payload)),
                false,
            )->execute(),
            'Affected count must be `0` when the row exists and updates are disabled.',
        );
        self::assertSame(
            $payload,
            $this->readBlob(1),
            'BLOB bytes must round-trip exactly for row 1.',
        );
    }

    public function testUpsertAffectedParameterAvoidsReservedPrefixCollision(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->delete('type')->execute();
        $command->addPrimaryKey(
            'PK_type_blob_param',
            'type',
            'int_col',
        )->execute();

        $reserved = ':yii_upsert_affected';
        $affected = "{$reserved}_";

        $payload = $this->createPayload(65_536);
        $values = $this->rowValues(1, $payload);

        $values['char_col'] = new Expression($reserved, [$reserved => 'collision-value']);

        $params = [];

        $sql = $db->getQueryBuilder()->upsert(
            'type',
            $values,
            true,
            $params,
        );

        self::assertSame(
            'collision-value',
            $params[$reserved],
            'The caller must be able to bind a parameter that collides with the reserved prefix.',
        );
        self::assertArrayHasKey(
            $affected,
            $params,
            'The reserved prefix must be used to generate a unique parameter name for the affected row count.',
        );
        self::assertInstanceOf(
            PdoValue::class,
            $params[$affected],
            'The affected row count must be bound as a PdoValue to allow output binding.',
        );
        self::assertSame(
            '',
            $params[$affected]->getValue(),
            'The affected row count must be initialized to an empty string for output binding.',
        );
        self::assertSame(
            PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT,
            $params[$affected]->getType(),
            'The affected row count must be bound as an output string parameter.',
        );
        self::assertSame(
            <<<SQL
            DECLARE
                affected PLS_INTEGER := 0;
            BEGIN
                UPDATE "type" SET "char_col" = :yii_upsert_affected, "float_col" = :qp2, "blob_col" = EMPTY_BLOB(), "bool_col" = :qp4 WHERE "int_col" = :qp0 RETURNING "blob_col" INTO :lob3;
                affected := SQL%ROWCOUNT;
                IF affected = 0 THEN
                    BEGIN
                        INSERT INTO "type" ("int_col", "char_col", "float_col", "blob_col", "bool_col") VALUES (:qp0, :yii_upsert_affected, :qp2, EMPTY_BLOB(), :qp4) RETURNING "blob_col" INTO :lob3;
                        affected := SQL%ROWCOUNT;
                    EXCEPTION
                        WHEN DUP_VAL_ON_INDEX THEN
                            UPDATE "type" SET "char_col" = :yii_upsert_affected, "float_col" = :qp2, "blob_col" = EMPTY_BLOB(), "bool_col" = :qp4 WHERE "int_col" = :qp0 RETURNING "blob_col" INTO :lob3;
                            affected := SQL%ROWCOUNT;
                            IF affected = 0 THEN
                                RAISE;
                            END IF;
                    END;
                END IF;
                :yii_upsert_affected_ := affected;
            END;
            SQL,
            $sql,
            'Collision upsert must compile to the exact PL/SQL block with the suffixed OUT parameter.',
        );
        self::assertSame(
            1,
            $db->createCommand($sql, $params)->execute(),
            'Exactly one row must be inserted.',
        );
        self::assertSame(
            $payload,
            $this->readBlob(1),
            'BLOB bytes must round-trip exactly for row 1.',
        );
    }

    public function testUpsertActivatesLobPathFromExplicitUpdateColumns(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->delete('type')->execute();
        $command->addPrimaryKey(
            'PK_type_blob_explicit',
            'type',
            'int_col',
        )->execute();
        $command->insert(
            'type',
            $this->rowValues(1, 'initial'),
        )->execute();

        $payload = $this->createPayload(200_000);

        $insertColumns = [
            'int_col' => 1,
            'char_col' => 'seed',
            'float_col' => 0.0,
            'bool_col' => 1,
        ];

        self::assertSame(
            1,
            $command->upsert(
                'type',
                $insertColumns,
                ['blob_col' => $payload],
            )->execute(),
            'A BLOB in explicit updateColumns must activate the locator upsert.',
        );
        self::assertSame(
            $payload,
            $this->readBlob(1),
            'BLOB bytes must round-trip exactly for row 1.',
        );
    }

    public function testUpsertMatchesSingleUniqueConstraint(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->delete('type')->execute();
        $command->createIndex(
            'UQ_type_single',
            'type',
            'int_col',
            true,
        )->execute();

        $insertPayload = $this->createPayload(200_000);

        $updatePayload = strrev($insertPayload);

        self::assertSame(
            1,
            $command->upsert(
                'type',
                $this->rowValues(1, $insertPayload),
            )->execute(),
            'Exactly one row must be inserted.',
        );
        self::assertSame(
            $insertPayload,
            $this->readBlob(1),
            'BLOB bytes must round-trip exactly for row 1.',
        );
        self::assertSame(
            1,
            $command->upsert('type', $this->rowValues(1, $updatePayload))->execute(),
            'Exactly one row must be updated.',
        );
        self::assertSame(
            $updatePayload,
            $this->readBlob(1),
            'BLOB bytes must round-trip exactly for row 1.',
        );
    }

    public function testThrowNotSupportedExceptionWhenUpsertHasNoMatchingConstraint(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->delete('type')->execute();

        // The `type` fixture declares no primary key or unique constraint.
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage(
            'Oracle BLOB upsert requires exactly one matching primary-key or unique constraint.',
        );

        $command->upsert(
            'type',
            $this->rowValues(1, $this->createPayload(1_024)),
        )->execute();
    }

    public function testThrowNotSupportedExceptionWhenUpsertMatchesMultipleConstraints(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->delete('type')->execute();
        $command->addPrimaryKey(
            'PK_type_multi',
            'type',
            'int_col',
        )->execute();
        $command->createIndex(
            'UQ_type_multi',
            'type',
            'char_col',
            true,
        )->execute();

        // The insert values cover both the primary key and the unique index, so the target is ambiguous.
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage(
            'Oracle BLOB upsert requires exactly one matching primary-key or unique constraint.',
        );

        $command->upsert(
            'type',
            $this->rowValues(1, $this->createPayload(1_024)),
        )->execute();
    }

    public function testThrowNotSupportedExceptionForQueryUpsertWithBlobValue(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->delete('type')->execute();
        $command->addPrimaryKey(
            'PK_type_blob_query',
            'type',
            'int_col',
        )->execute();

        $insertQuery = (new Query())
            ->select(
                [
                    'int_col' => new Expression('1'),
                    'char_col' => new Expression("'query-upsert'"),
                    'float_col' => new Expression('0'),
                    'blob_col' => new Expression('EMPTY_BLOB()'),
                    'bool_col' => new Expression("'1'"),
                ],
            )
            ->from('DUAL');

        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage(
            'Oracle does not support upserting a BLOB value sourced from a query.',
        );

        $command->upsert(
            'type',
            $insertQuery,
            ['blob_col' => $this->createPayload(65_536)],
        );
    }

    public function testInsertRoundTripsMultipleBlobColumns(): void
    {
        $db = $this->getConnection();

        DbHelper::dropTablesIfExist($db, ['blob_multi']);

        $command = $db->createCommand();

        $command->createTable(
            'blob_multi',
            ['id' => 'integer', 'blob_a' => 'binary', 'blob_b' => 'binary'],
        )->execute();

        $payloadA = $this->createPayload(65_536);

        $payloadB = strrev($this->createPayload(32_768));

        $params = [];

        $sql = $db->getQueryBuilder()->insert(
            'blob_multi',
            ['id' => 1, 'blob_a' => $payloadA, 'blob_b' => $payloadB],
            $params,
        );

        self::assertSame(
            <<<SQL
            INSERT INTO "blob_multi" ("id", "blob_a", "blob_b") VALUES (:qp0, EMPTY_BLOB(), EMPTY_BLOB()) RETURNING "blob_a", "blob_b" INTO :lob1, :lob2
            SQL,
            $sql,
            'Both BLOB columns must share one RETURNING clause.',
        );
        self::assertSame(
            1,
            $command->insert(
                'blob_multi',
                ['id' => 1, 'blob_a' => $payloadA, 'blob_b' => $payloadB],
            )->execute(),
        );

        $selectBlob = static fn(string $column): mixed => (new Query())
            ->select([$column])
            ->from('blob_multi')
            ->where(['id' => 1])
            ->createCommand($db)
            ->queryScalar();

        self::assertSame(
            $payloadA,
            $selectBlob('blob_a'),
            'First BLOB column must round-trip.',
        );
        self::assertSame(
            $payloadB,
            $selectBlob('blob_b'),
            'Second BLOB column must round-trip.',
        );

        DbHelper::dropTablesIfExist($db, ['blob_multi']);
    }

    public function testSchemaInsertReturnsPrimaryKeyWithBlobValue(): void
    {
        $db = $this->getConnection();

        DbHelper::dropTablesIfExist($db, ['blob_pk']);

        $command = $db->createCommand();

        $command->createTable(
            'blob_pk',
            ['id' => 'pk', 'blob_col' => 'binary'],
        )->execute();

        $payload = $this->createPayload(65_536);

        // Same path as ActiveRecord::insertInternal(): the PK and the LOB must share one RETURNING clause.
        $primaryKeys = $db->getSchema()->insert('blob_pk', ['blob_col' => $payload]);

        self::assertIsArray(
            $primaryKeys,
            'Primary keys must be returned.',
        );
        self::assertSame(
            1,
            (int) ($primaryKeys['id'] ?? 0),
            'Identity value must be filled.',
        );

        $stored = (new Query())
            ->select(['blob_col'])
            ->from('blob_pk')
            ->where(['id' => 1])
            ->createCommand($db)
            ->queryScalar();

        self::assertSame(
            $payload,
            $stored,
            'BLOB bytes must round-trip exactly.',
        );

        DbHelper::dropTablesIfExist($db, ['blob_pk']);
    }

    public function testUserProvidedStreamRemainsOpenAfterExecution(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->delete('type')->execute();

        $payload = $this->createPayload(65_536);

        $stream = fopen('php://temp', 'w+b');

        self::assertIsResource(
            $stream,
            'Fixture stream must open.',
        );
        self::assertSame(
            strlen($payload),
            fwrite($stream, $payload),
            'Fixture payload must be written.',
        );
        self::assertTrue(
            rewind($stream),
            'Fixture stream must rewind.',
        );


        $command->insert(
            'type',
            $this->rowValues(1, new PdoValue($stream, PDO::PARAM_LOB)),
        )->execute();

        self::assertIsResource(
            $stream,
            'Caller stream must not be closed by the command.',
        );
        self::assertSame(
            $payload,
            $this->readBlob(1),
            'BLOB bytes must round-trip exactly for row 1.',
        );

        fclose($stream);
    }

    /**
     * @return string[] Binary payloads at the required boundary sizes.
     */
    private function blobPayloads(): array
    {
        return [
            $this->createPayload(31),
            $this->createPayload(32_767),
            $this->createPayload(32_768),
            $this->createPayload(65_536),
            $this->createPayload(1_048_576),
        ];
    }

    private function createPayload(int $length): string
    {
        $seed = "Yii2\0Oracle\xFF\xFE\x80BLOB";

        $payload = substr(str_repeat($seed, (int) ceil($length / strlen($seed))), 0, $length);

        self::assertSame(
            $length,
            strlen($payload),
            'Payload must have the requested length.',
        );
        self::assertStringContainsString(
            "\0",
            $payload,
            'Payload must contain NUL bytes.',
        );

        return $payload;
    }

    /**
     * @return array<string, mixed> Complete values for an Oracle `type` fixture row.
     */
    private function rowValues(int $id, mixed $blob): array
    {
        return [
            'int_col' => $id,
            'char_col' => "blob-{$id}",
            'float_col' => 0.0,
            'blob_col' => $blob,
            'bool_col' => 1,
        ];
    }

    private function readBlob(int $id): mixed
    {
        return (new Query())
            ->select(['blob_col'])
            ->from('type')
            ->where(['int_col' => $id])
            ->createCommand($this->getConnection(false))
            ->queryScalar();
    }
}
