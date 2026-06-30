<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yii\db\mysql\Quoter;
use yiiunit\framework\db\mysql\providers\QuoterProvider;
use yiiunit\TestCase;

/**
 * Unit tests for {@see \yii\db\mysql\Quoter} for the MySQL driver.
 *
 * {@see QuoterProvider} for test case data providers.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('mysql')]
#[Group('quoter')]
final class QuoterTest extends TestCase
{
    #[DataProviderExternal(QuoterProvider::class, 'escapeLiteralValue')]
    public function testEscapeLiteralValueEscapesMysqlSpecialCharacters(string $value, string $expected): void
    {
        self::assertSame(
            $expected,
            Quoter::escapeLiteralValue($value),
            'MySQL special characters must be backslash-escaped.',
        );
    }
}
