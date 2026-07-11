<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use PHPUnit\Framework\Attributes\Group;
use yii\db\oci\LobValue;
use yii\db\oci\LobValueBuilder;
use yiiunit\TestCase;

/**
 * Unit tests for {@see \yii\db\oci\LobValueBuilder} rendering the `EMPTY_BLOB()` value expression.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('oci')]
final class LobValueBuilderTest extends TestCase
{
    public function testBuildRendersEmptyBlobAndRegistersValue(): void
    {
        $builder = new LobValueBuilder();

        $value = new LobValue('blob_col', 'payload');

        $params = [':qp0' => 1];

        $sql = $builder->build($value, $params);

        self::assertSame(
            'EMPTY_BLOB()',
            $sql,
            "Value expression must be 'EMPTY_BLOB()'.",
        );
        self::assertCount(
            2,
            $params,
            'A LOB placeholder must be registered.',
        );
        self::assertContains(
            $value,
            $params,
            "Registered param must be the 'LobValue' object.",
        );
    }

    public function testBuildPreservesColumnNameAndValue(): void
    {
        $builder = new LobValueBuilder();

        $value = new LobValue('blob_col', 'payload');

        $params = [];

        $builder->build($value, $params);

        $registered = array_pop($params);

        self::assertSame(
            'blob_col',
            $registered->getColumnName(),
            'Column name must be carried.',
        );
        self::assertSame(
            'payload',
            $registered->getValue(),
            'Payload must be carried.',
        );
    }

    public function testBindReturnsPlaceholderOfRegisteredValue(): void
    {
        $value = new LobValue('blob_col', 'payload');

        $params = [':qp0' => 1];

        [$sql, $placeholder] = LobValueBuilder::bind($value, $params);

        self::assertSame(
            'EMPTY_BLOB()',
            $sql,
            "Value expression must be 'EMPTY_BLOB()'.",
        );
        self::assertSame(
            ':lob1',
            $placeholder,
            "Placeholder must follow the ':lobN' pattern.",
        );
        self::assertSame(
            $value,
            $params[$placeholder],
            "Placeholder must map to the 'LobValue' object.",
        );
    }

    public function testBuildAvoidsPlaceholderCollision(): void
    {
        $builder = new LobValueBuilder();

        $value = new LobValue('blob_col', 'payload');

        $params = [':lob1' => 'existing'];

        $builder->build($value, $params);

        self::assertSame(
            'existing',
            $params[':lob1'],
            'Existing param must be preserved.',
        );
        self::assertContains(
            $value,
            $params,
            'Value must be registered under a fresh placeholder.',
        );
        self::assertCount(
            2,
            $params,
            'Exactly one new placeholder must be added.',
        );
    }
}
