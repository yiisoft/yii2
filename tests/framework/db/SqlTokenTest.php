<?php

namespace yiiunit\framework\db;

use yii\db\SqlToken;
use yiiunit\TestCase;

class SqlTokenTest extends TestCase
{
    private function createToken(array $config = []): SqlToken
    {
        $token = new SqlToken();
        foreach ($config as $property => $value) {
            $token->$property = $value;
        }
        return $token;
    }

    private function createLeafToken(string $content, int $type = SqlToken::TYPE_KEYWORD): SqlToken
    {
        return $this->createToken([
            'type' => $type,
            'content' => $content,
            'startOffset' => 0,
            'endOffset' => mb_strlen($content, 'UTF-8'),
        ]);
    }

    private function createCollectionToken(int $type = SqlToken::TYPE_STATEMENT): SqlToken
    {
        return $this->createToken([
            'type' => $type,
        ]);
    }

    /**
     * @dataProvider collectionTypeProvider
     */
    public function testIsCollectionForCollectionTypes(int $type): void
    {
        $token = $this->createToken(['type' => $type]);
        $this->assertTrue($token->getIsCollection());
    }

    public static function collectionTypeProvider(): array
    {
        return [
            'TYPE_CODE' => [SqlToken::TYPE_CODE],
            'TYPE_STATEMENT' => [SqlToken::TYPE_STATEMENT],
            'TYPE_PARENTHESIS' => [SqlToken::TYPE_PARENTHESIS],
        ];
    }

    /**
     * @dataProvider nonCollectionTypeProvider
     */
    public function testIsCollectionFalseForNonCollectionTypes(int $type): void
    {
        $token = $this->createToken(['type' => $type]);
        $this->assertFalse($token->getIsCollection());
    }

    public static function nonCollectionTypeProvider(): array
    {
        return [
            'TYPE_TOKEN' => [SqlToken::TYPE_TOKEN],
            'TYPE_KEYWORD' => [SqlToken::TYPE_KEYWORD],
            'TYPE_OPERATOR' => [SqlToken::TYPE_OPERATOR],
            'TYPE_IDENTIFIER' => [SqlToken::TYPE_IDENTIFIER],
            'TYPE_STRING_LITERAL' => [SqlToken::TYPE_STRING_LITERAL],
        ];
    }

    public function testDefaultTypeIsToken(): void
    {
        $token = new SqlToken();
        $this->assertSame(SqlToken::TYPE_TOKEN, $token->type);
    }

    public function testHasChildrenFalseWhenEmpty(): void
    {
        $token = $this->createCollectionToken();
        $this->assertFalse($token->getHasChildren());
    }

    public function testHasChildrenFalseForNonCollection(): void
    {
        $token = $this->createLeafToken('SELECT');
        $this->assertFalse($token->getHasChildren());
    }

    public function testHasChildrenTrueWhenPopulated(): void
    {
        $token = $this->createCollectionToken();
        $child = $this->createLeafToken('SELECT');
        $token[] = $child;
        $this->assertTrue($token->getHasChildren());
    }

    public function testGetChildrenReturnsEmptyArrayByDefault(): void
    {
        $token = $this->createCollectionToken();
        $this->assertSame([], $token->getChildren());
    }

    public function testOffsetSetAppendSetsParent(): void
    {
        $parent = $this->createCollectionToken();
        $child = $this->createLeafToken('SELECT');
        $parent[] = $child;

        $this->assertSame($parent, $child->parent);
        $this->assertCount(1, $parent->getChildren());
    }

    public function testOffsetSetWithIndexSetsParent(): void
    {
        $parent = $this->createCollectionToken();
        $child = $this->createLeafToken('SELECT');
        $parent[0] = $child;

        $this->assertSame($parent, $child->parent);
        $this->assertSame($child, $parent[0]);
    }

    public function testOffsetExistsPositive(): void
    {
        $parent = $this->createCollectionToken();
        $parent[] = $this->createLeafToken('a');
        $this->assertTrue(isset($parent[0]));
    }

    public function testOffsetExistsNegativeIndex(): void
    {
        $parent = $this->createCollectionToken();
        $parent[] = $this->createLeafToken('a');
        $parent[] = $this->createLeafToken('b');
        $this->assertTrue(isset($parent[-1]));
    }

    public function testOffsetExistsFalseForOutOfRange(): void
    {
        $parent = $this->createCollectionToken();
        $this->assertFalse(isset($parent[0]));
    }

    public function testOffsetGetReturnsNullForMissing(): void
    {
        $parent = $this->createCollectionToken();
        $this->assertNull($parent[5]);
    }

    public function testOffsetGetNegativeIndex(): void
    {
        $parent = $this->createCollectionToken();
        $first = $this->createLeafToken('a');
        $last = $this->createLeafToken('b');
        $parent[] = $first;
        $parent[] = $last;

        $this->assertSame($last, $parent[-1]);
        $this->assertSame($first, $parent[-2]);
    }

    public function testOffsetUnsetRemovesChild(): void
    {
        $parent = $this->createCollectionToken();
        $child1 = $this->createToken([
            'type' => SqlToken::TYPE_KEYWORD,
            'startOffset' => 0,
            'endOffset' => 3,
        ]);
        $child2 = $this->createToken([
            'type' => SqlToken::TYPE_KEYWORD,
            'startOffset' => 4,
            'endOffset' => 7,
        ]);
        $parent[] = $child1;
        $parent[] = $child2;

        $this->assertSame(0, $parent->startOffset);
        $this->assertSame(7, $parent->endOffset);

        unset($parent[0]);

        $this->assertCount(1, $parent->getChildren());
        $this->assertSame(4, $parent->startOffset);
        $this->assertSame(7, $parent->endOffset);
    }

    public function testOffsetUnsetNonExistent(): void
    {
        $parent = $this->createCollectionToken();
        $parent[] = $this->createLeafToken('a');

        unset($parent[5]);

        $this->assertCount(1, $parent->getChildren());
    }

    public function testSetChildrenReplacesAll(): void
    {
        $parent = $this->createCollectionToken();
        $parent[] = $this->createLeafToken('old');

        $newChild1 = $this->createToken([
            'type' => SqlToken::TYPE_KEYWORD,
            'startOffset' => 10,
            'endOffset' => 15,
        ]);
        $newChild2 = $this->createToken([
            'type' => SqlToken::TYPE_KEYWORD,
            'startOffset' => 16,
            'endOffset' => 20,
        ]);
        $parent->setChildren([$newChild1, $newChild2]);

        $this->assertCount(2, $parent->getChildren());
        $this->assertSame($parent, $newChild1->parent);
        $this->assertSame($parent, $newChild2->parent);
        $this->assertSame(10, $parent->startOffset);
        $this->assertSame(20, $parent->endOffset);
    }

    public function testToStringDelegatesToGetSql(): void
    {
        $code = $this->createToken([
            'type' => SqlToken::TYPE_CODE,
            'content' => 'SELECT id FROM users',
        ]);

        $child = $this->createToken([
            'type' => SqlToken::TYPE_KEYWORD,
            'startOffset' => 0,
            'endOffset' => 6,
        ]);
        $child->parent = $code;

        $this->assertSame('SELECT', $child->getSql());
        $this->assertSame('SELECT', (string) $child);
    }

    public function testGetSqlTraversesToRoot(): void
    {
        $root = $this->createToken([
            'type' => SqlToken::TYPE_CODE,
            'content' => 'SELECT id FROM users',
        ]);

        $statement = $this->createToken([
            'type' => SqlToken::TYPE_STATEMENT,
            'startOffset' => 0,
            'endOffset' => 20,
        ]);
        $statement->parent = $root;

        $keyword = $this->createToken([
            'type' => SqlToken::TYPE_KEYWORD,
            'startOffset' => 15,
            'endOffset' => 20,
        ]);
        $keyword->parent = $statement;

        $this->assertSame('users', $keyword->getSql());
    }

    public function testGetSqlWithNonZeroStartOffset(): void
    {
        $root = $this->createToken([
            'type' => SqlToken::TYPE_CODE,
            'content' => 'SELECT id FROM users WHERE active',
        ]);

        $keyword = $this->createToken([
            'type' => SqlToken::TYPE_KEYWORD,
            'startOffset' => 10,
            'endOffset' => 14,
        ]);
        $keyword->parent = $root;

        $this->assertSame('FROM', $keyword->getSql());
    }

    public function testGetSqlWithUtf8Content(): void
    {
        $root = $this->createToken([
            'type' => SqlToken::TYPE_CODE,
            'content' => 'SELECT название FROM таблица',
        ]);

        $keyword = $this->createToken([
            'type' => SqlToken::TYPE_IDENTIFIER,
            'startOffset' => 7,
            'endOffset' => 15,
        ]);
        $keyword->parent = $root;

        $this->assertSame('название', $keyword->getSql());
    }

    public function testUpdateCollectionOffsetsOnAppend(): void
    {
        $parent = $this->createCollectionToken();

        $child1 = $this->createToken([
            'type' => SqlToken::TYPE_KEYWORD,
            'startOffset' => 0,
            'endOffset' => 6,
        ]);
        $child2 = $this->createToken([
            'type' => SqlToken::TYPE_KEYWORD,
            'startOffset' => 7,
            'endOffset' => 12,
        ]);

        $parent[] = $child1;
        $parent[] = $child2;

        $this->assertSame(0, $parent->startOffset);
        $this->assertSame(12, $parent->endOffset);
    }

    public function testUpdateCollectionOffsetsCascadesToParent(): void
    {
        $root = $this->createToken(['type' => SqlToken::TYPE_CODE]);
        $statement = $this->createToken(['type' => SqlToken::TYPE_STATEMENT]);
        $root[] = $statement;

        $keyword = $this->createToken([
            'type' => SqlToken::TYPE_KEYWORD,
            'startOffset' => 5,
            'endOffset' => 10,
        ]);
        $statement[] = $keyword;

        $this->assertSame(5, $statement->startOffset);
        $this->assertSame(10, $statement->endOffset);
        $this->assertSame(5, $root->startOffset);
        $this->assertSame(10, $root->endOffset);
    }

    public function testCalculateOffsetPositive(): void
    {
        $parent = $this->createCollectionToken();
        $parent[] = $this->createLeafToken('a');
        $parent[] = $this->createLeafToken('b');
        $parent[] = $this->createLeafToken('c');

        $this->assertSame('a', $parent[0]->content);
        $this->assertSame('b', $parent[1]->content);
        $this->assertSame('c', $parent[2]->content);
    }

    public function testCalculateOffsetNegative(): void
    {
        $parent = $this->createCollectionToken();
        $parent[] = $this->createLeafToken('a');
        $parent[] = $this->createLeafToken('b');
        $parent[] = $this->createLeafToken('c');

        $this->assertSame('c', $parent[-1]->content);
        $this->assertSame('b', $parent[-2]->content);
        $this->assertSame('a', $parent[-3]->content);
    }

    public function testMatchesReturnsFalseForEmptyPattern(): void
    {
        $token = $this->createCollectionToken(SqlToken::TYPE_STATEMENT);
        $token[] = $this->createLeafToken('SELECT');

        $emptyPattern = $this->createCollectionToken(SqlToken::TYPE_CODE);

        $this->assertFalse($token->matches($emptyPattern));
    }

    public function testMatchesExactTokens(): void
    {
        $sql = 'SELECT id FROM users';
        $code = $this->createToken(['type' => SqlToken::TYPE_CODE, 'content' => $sql]);
        $statement = $this->createCollectionToken(SqlToken::TYPE_STATEMENT);
        $code[] = $statement;

        $select = $this->createLeafToken('SELECT');
        $id = $this->createLeafToken('id', SqlToken::TYPE_IDENTIFIER);
        $from = $this->createLeafToken('FROM');
        $users = $this->createLeafToken('users', SqlToken::TYPE_IDENTIFIER);
        $statement[] = $select;
        $statement[] = $id;
        $statement[] = $from;
        $statement[] = $users;

        $patternCode = $this->createToken(['type' => SqlToken::TYPE_CODE, 'content' => 'SELECT']);
        $patternStatement = $this->createCollectionToken(SqlToken::TYPE_STATEMENT);
        $patternCode[] = $patternStatement;
        $patternSelect = $this->createLeafToken('SELECT');
        $patternStatement[] = $patternSelect;

        $firstMatch = null;
        $lastMatch = null;
        $this->assertTrue($statement->matches($patternCode, 0, $firstMatch, $lastMatch));
        $this->assertSame(0, $firstMatch);
        $this->assertSame(0, $lastMatch);
    }

    public function testMatchesWithAnyWildcard(): void
    {
        $code = $this->createToken(['type' => SqlToken::TYPE_CODE, 'content' => 'SELECT id FROM users']);
        $statement = $this->createCollectionToken(SqlToken::TYPE_STATEMENT);
        $code[] = $statement;

        $statement[] = $this->createLeafToken('SELECT');
        $statement[] = $this->createLeafToken('id', SqlToken::TYPE_IDENTIFIER);
        $statement[] = $this->createLeafToken('FROM');
        $statement[] = $this->createLeafToken('users', SqlToken::TYPE_IDENTIFIER);

        $patternCode = $this->createToken(['type' => SqlToken::TYPE_CODE, 'content' => 'SELECT any FROM any']);
        $patternStatement = $this->createCollectionToken(SqlToken::TYPE_STATEMENT);
        $patternCode[] = $patternStatement;
        $patternStatement[] = $this->createLeafToken('SELECT');
        $patternStatement[] = $this->createLeafToken('any');
        $patternStatement[] = $this->createLeafToken('FROM');
        $patternStatement[] = $this->createLeafToken('any');

        $firstMatch = null;
        $lastMatch = null;
        $this->assertTrue($statement->matches($patternCode, 0, $firstMatch, $lastMatch));
    }

    public function testMatchesReturnsFalseForMismatch(): void
    {
        $code = $this->createToken(['type' => SqlToken::TYPE_CODE, 'content' => 'INSERT INTO t']);
        $statement = $this->createCollectionToken(SqlToken::TYPE_STATEMENT);
        $code[] = $statement;
        $statement[] = $this->createLeafToken('INSERT');
        $statement[] = $this->createLeafToken('INTO');

        $patternCode = $this->createToken(['type' => SqlToken::TYPE_CODE, 'content' => 'SELECT']);
        $patternStatement = $this->createCollectionToken(SqlToken::TYPE_STATEMENT);
        $patternCode[] = $patternStatement;
        $patternStatement[] = $this->createLeafToken('SELECT');

        $this->assertFalse($statement->matches($patternCode));
    }

    public function testMatchesWithOffset(): void
    {
        $code = $this->createToken(['type' => SqlToken::TYPE_CODE, 'content' => 'SELECT id FROM users']);
        $statement = $this->createCollectionToken(SqlToken::TYPE_STATEMENT);
        $code[] = $statement;

        $statement[] = $this->createLeafToken('SELECT');
        $statement[] = $this->createLeafToken('id', SqlToken::TYPE_IDENTIFIER);
        $statement[] = $this->createLeafToken('FROM');
        $statement[] = $this->createLeafToken('users', SqlToken::TYPE_IDENTIFIER);

        $patternCode = $this->createToken(['type' => SqlToken::TYPE_CODE, 'content' => 'FROM any']);
        $patternStatement = $this->createCollectionToken(SqlToken::TYPE_STATEMENT);
        $patternCode[] = $patternStatement;
        $patternStatement[] = $this->createLeafToken('FROM');
        $patternStatement[] = $this->createLeafToken('any');

        $firstMatch = null;
        $lastMatch = null;
        $this->assertTrue($statement->matches($patternCode, 2, $firstMatch, $lastMatch));
        $this->assertSame(2, $firstMatch);
        $this->assertSame(2, $lastMatch);
    }

    public function testTokensMatchReturnsFalseForCollectionVsLeaf(): void
    {
        $code = $this->createToken(['type' => SqlToken::TYPE_CODE, 'content' => 'SELECT']);
        $statement = $this->createCollectionToken(SqlToken::TYPE_STATEMENT);
        $code[] = $statement;

        $leaf = $this->createLeafToken('SELECT');
        $statement[] = $leaf;

        $patternCode = $this->createToken(['type' => SqlToken::TYPE_CODE, 'content' => 'test']);
        $patternStatement = $this->createCollectionToken(SqlToken::TYPE_STATEMENT);
        $patternCode[] = $patternStatement;
        $patternSubCollection = $this->createCollectionToken(SqlToken::TYPE_PARENTHESIS);
        $patternStatement[] = $patternSubCollection;

        $this->assertFalse($statement->matches($patternCode));
    }

    public function testTypeConstants(): void
    {
        $this->assertSame(0, SqlToken::TYPE_CODE);
        $this->assertSame(1, SqlToken::TYPE_STATEMENT);
        $this->assertSame(2, SqlToken::TYPE_TOKEN);
        $this->assertSame(3, SqlToken::TYPE_PARENTHESIS);
        $this->assertSame(4, SqlToken::TYPE_KEYWORD);
        $this->assertSame(5, SqlToken::TYPE_OPERATOR);
        $this->assertSame(6, SqlToken::TYPE_IDENTIFIER);
        $this->assertSame(7, SqlToken::TYPE_STRING_LITERAL);
    }

    public function testOffsetSetReplacesByIndex(): void
    {
        $parent = $this->createCollectionToken();
        $parent[] = $this->createLeafToken('old');
        $replacement = $this->createLeafToken('new');
        $parent[0] = $replacement;

        $this->assertCount(1, $parent->getChildren());
        $this->assertSame('new', $parent[0]->content);
        $this->assertSame($parent, $replacement->parent);
    }

    public function testOffsetUnsetWithNegativeIndex(): void
    {
        $parent = $this->createCollectionToken();
        $parent[] = $this->createLeafToken('a');
        $parent[] = $this->createLeafToken('b');
        $parent[] = $this->createLeafToken('c');

        unset($parent[-1]);

        $this->assertCount(2, $parent->getChildren());
        $this->assertSame('a', $parent[0]->content);
        $this->assertSame('b', $parent[1]->content);
    }

    public function testSetChildrenSetsParentForEachChild(): void
    {
        $parent = $this->createCollectionToken();
        $child1 = $this->createLeafToken('x');
        $child2 = $this->createLeafToken('y');
        $parent->setChildren([$child1, $child2]);

        $this->assertSame($parent, $child1->parent);
        $this->assertSame($parent, $child2->parent);
        $this->assertCount(2, $parent->getChildren());
    }

    public function testSetChildrenClearsPrevious(): void
    {
        $parent = $this->createCollectionToken();
        $old = $this->createLeafToken('old');
        $parent[] = $old;

        $new = $this->createLeafToken('new');
        $parent->setChildren([$new]);

        $this->assertCount(1, $parent->getChildren());
        $this->assertSame('new', $parent[0]->content);
    }

    public function testMatchesFailsWhenPatternExceedsTokenCount(): void
    {
        $code = $this->createToken(['type' => SqlToken::TYPE_CODE, 'content' => 'SELECT']);
        $statement = $this->createCollectionToken(SqlToken::TYPE_STATEMENT);
        $code[] = $statement;
        $statement[] = $this->createLeafToken('SELECT');

        $patternCode = $this->createToken(['type' => SqlToken::TYPE_CODE, 'content' => 'SELECT FROM']);
        $patternStatement = $this->createCollectionToken(SqlToken::TYPE_STATEMENT);
        $patternCode[] = $patternStatement;
        $patternStatement[] = $this->createLeafToken('SELECT');
        $patternStatement[] = $this->createLeafToken('FROM');

        $this->assertFalse($statement->matches($patternCode));
    }
}
