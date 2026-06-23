<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yii\db\mssql\Quoter;
use yiiunit\framework\db\mssql\providers\QuoterProvider;
use yiiunit\TestCase;

/**
 * Unit tests for {@see \yii\db\mssql\Quoter} for the MSSQL driver.
 *
 * {@see QuoterProvider} for test case data providers.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('mssql')]
#[Group('quoter')]
final class QuoterTest extends TestCase
{
    #[DataProviderExternal(QuoterProvider::class, 'escapeLiteralValue')]
    public function testEscapeLiteralValueDoublesSingleQuotes(string $value, string $expected): void
    {
        self::assertSame(
            $expected,
            Quoter::escapeLiteralValue($value),
            'Each single quote must be doubled.',
        );
    }

    #[DataProviderExternal(QuoterProvider::class, 'extractSimpleIdentifier')]
    public function testExtractSimpleIdentifierReturnsTrailingPart(string $name, string $expected): void
    {
        self::assertSame(
            $expected,
            Quoter::extractSimpleIdentifier($name),
            'Trailing identifier must be returned.',
        );
    }

    #[DataProviderExternal(QuoterProvider::class, 'isIdentifierBracketQuoted')]
    public function testIsIdentifierBracketQuotedDetectsBracketedNames(string $identifier, bool $expected): void
    {
        self::assertSame(
            $expected,
            Quoter::isIdentifierBracketQuoted($identifier),
            'Bracket-quoted state must match.',
        );
    }
}
