<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yiiunit\base\db\BaseSchemaQuote;
use yiiunit\framework\db\oci\providers\SchemaProvider;

/**
 * Unit tests for {@see \yii\db\oci\Schema} identifier quoting for the Oracle driver.
 *
 * {@see SchemaProvider} for test case data providers.
 */
#[Group('db')]
#[Group('oci')]
#[Group('schema')]
#[Group('quote')]
final class SchemaQuoteTest extends BaseSchemaQuote
{
    protected $driverName = 'oci';
    protected static string $driverNameStatic = 'oci';

    #[DataProviderExternal(SchemaProvider::class, 'quoteSimpleTableName')]
    public function testQuoteSimpleTableName(string $name, string $expectedName): void
    {
        parent::testQuoteSimpleTableName($name, $expectedName);
    }
}
