<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\BaseObject;

/**
 * SqlToken represents SQL tokens produced by [[SqlTokenizer]] or its child classes.
 *
 * @property SqlToken[] $children Child tokens.
 * @property-read bool $hasChildren Whether the token has children.
 * @property-read bool $isCollection Whether the token represents a collection of tokens.
 * @property-read string $sql SQL code.
 *
 * @author Sergey Makinen <sergey@makinen.ru>
 * @since 2.0.13
 *
 * @implements \ArrayAccess<int, SqlToken>
 */
class SqlToken extends BaseObject implements \ArrayAccess
{
    public const TYPE_CODE = 0;
    public const TYPE_STATEMENT = 1;
    public const TYPE_TOKEN = 2;
    public const TYPE_PARENTHESIS = 3;
    public const TYPE_KEYWORD = 4;
    public const TYPE_OPERATOR = 5;
    public const TYPE_IDENTIFIER = 6;
    public const TYPE_STRING_LITERAL = 7;
    /**
     * @var int token type. It has to be one of the following constants:
     *
     * - [[TYPE_CODE]]
     * - [[TYPE_STATEMENT]]
     * - [[TYPE_TOKEN]]
     * - [[TYPE_PARENTHESIS]]
     * - [[TYPE_KEYWORD]]
     * - [[TYPE_OPERATOR]]
     * - [[TYPE_IDENTIFIER]]
     * - [[TYPE_STRING_LITERAL]]
     */
    public $type = self::TYPE_TOKEN;
    /**
     * @var string|null token content.
     */
    public $content;
    /**
     * @var int original SQL token start position.
     */
    public $startOffset;
    /**
     * @var int original SQL token end position.
     */
    public $endOffset;
    /**
     * @var SqlToken parent token.
     */
    public $parent;

    /**
     * @var SqlToken[] token children.
     */
    private $_children = [];


    /**
     * Returns the SQL code representing the token.
     * @return string SQL code.
     */
    public function __toString()
    {
        return $this->getSql();
    }

    /**
     * Returns whether there is a child token at the specified offset.
     * This method is required by the SPL [[\ArrayAccess]] interface.
     * It is implicitly called when you use something like `isset($token[$offset])`.
     * @param int $offset child token offset.
     * @return bool whether the token exists.
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->_children[$this->calculateOffset($offset)]);
    }

    /**
     * Returns a child token at the specified offset.
     * This method is required by the SPL [[\ArrayAccess]] interface.
     * It is implicitly called when you use something like `$child = $token[$offset];`.
     * @param int $offset child token offset.
     * @return SqlToken|null the child token at the specified offset, `null` if there's no token.
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        $offset = $this->calculateOffset($offset);
        return isset($this->_children[$offset]) ? $this->_children[$offset] : null;
    }

    /**
     * Adds a child token to the token.
     * This method is required by the SPL [[\ArrayAccess]] interface.
     * It is implicitly called when you use something like `$token[$offset] = $child;`.
     * @param int|null $offset child token offset.
     * @param SqlToken $token token to be added.
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $token)
    {
        $token->parent = $this;
        if ($offset === null) {
            $this->_children[] = $token;
        } else {
            $this->_children[$this->calculateOffset($offset)] = $token;
        }
        $this->updateCollectionOffsets();
    }

    /**
     * Removes a child token at the specified offset.
     * This method is required by the SPL [[\ArrayAccess]] interface.
     * It is implicitly called when you use something like `unset($token[$offset])`.
     * @param int $offset child token offset.
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        $offset = $this->calculateOffset($offset);
        if (isset($this->_children[$offset])) {
            array_splice($this->_children, $offset, 1);
        }
        $this->updateCollectionOffsets();
    }

    /**
     * Returns child tokens.
     * @return SqlToken[] child tokens.
     */
    public function getChildren()
    {
        return $this->_children;
    }

    /**
     * Sets a list of child tokens.
     * @param SqlToken[] $children child tokens.
     */
    public function setChildren($children)
    {
        $this->_children = [];
        foreach ($children as $child) {
            $child->parent = $this;
            $this->_children[] = $child;
        }
        $this->updateCollectionOffsets();
    }

    /**
     * Returns whether the token represents a collection of tokens.
     * @return bool whether the token represents a collection of tokens.
     */
    public function getIsCollection()
    {
        return in_array($this->type, [
            self::TYPE_CODE,
            self::TYPE_STATEMENT,
            self::TYPE_PARENTHESIS,
        ], true);
    }

    /**
     * Returns whether the token represents a collection of tokens and has non-zero number of children.
     * @return bool whether the token has children.
     */
    public function getHasChildren()
    {
        return $this->getIsCollection() && !empty($this->_children);
    }

    /**
     * Returns the SQL code representing the token.
     * @return string SQL code.
     */
    public function getSql()
    {
        $code = $this;
        while ($code->parent !== null) {
            $code = $code->parent;
        }

        return mb_substr($code->content, $this->startOffset, $this->endOffset - $this->startOffset, 'UTF-8');
    }

    /**
     * Returns whether this token (including its children) matches the specified "pattern" SQL code.
     *
     * Usage Example:
     *
     * ```
     * $patternToken = (new \yii\db\sqlite\SqlTokenizer('SELECT any FROM any'))->tokenize();
     * if ($sqlToken->matches($patternToken, 0, $firstMatchIndex, $lastMatchIndex)) {
     *     // ...
     * }
     * ```
     *
     * @param SqlToken $patternToken tokenized SQL code to match against. In addition to normal SQL, the
     * `any` keyword is supported which will match any number of keywords, identifiers, whitespaces.
     * @param int $offset token children offset to start lookup with.
     * @param int|null $firstMatchIndex token children offset where a successful match begins.
     * @param int|null $lastMatchIndex token children offset where a successful match ends.
     * @return bool whether this token matches the pattern SQL code.
     */
    public function matches(SqlToken $patternToken, $offset = 0, &$firstMatchIndex = null, &$lastMatchIndex = null)
    {
        if (!$patternToken->getHasChildren()) {
            return false;
        }

        $patternToken = $patternToken[0];
        return $this->tokensMatch($patternToken, $this, $offset, $firstMatchIndex, $lastMatchIndex);
    }

    /**
     * Tests the given token to match the specified pattern token.
     * @param SqlToken $patternToken
     * @param SqlToken $token
     * @param int $offset
     * @param int|null $firstMatchIndex
     * @param int|null $lastMatchIndex
     * @return bool
     */
    private function tokensMatch(SqlToken $patternToken, SqlToken $token, $offset = 0, &$firstMatchIndex = null, &$lastMatchIndex = null)
    {
        if (
            $patternToken->getIsCollection() !== $token->getIsCollection()
            || (!$patternToken->getIsCollection() && $patternToken->content !== $token->content)
        ) {
            return false;
        }

        if ($patternToken->children === $token->children) {
            $firstMatchIndex = $lastMatchIndex = $offset;
            return true;
        }

        $firstMatchIndex = $lastMatchIndex = null;
        $wildcard = false;
        for ($index = 0, $count = count($patternToken->children); $index < $count; $index++) {
            // Here we iterate token by token with an exception of "any" that toggles
            // an iteration until we matched with a next pattern token or EOF.
            if ($patternToken[$index]->content === 'any') {
                $wildcard = true;
                continue;
            }

            for ($limit = $wildcard ? count($token->children) : $offset + 1; $offset < $limit; $offset++) {
                if (!$wildcard && !isset($token[$offset])) {
                    break;
                }

                if (!$this->tokensMatch($patternToken[$index], $token[$offset])) {
                    continue;
                }

                if ($firstMatchIndex === null) {
                    $firstMatchIndex = $offset;
                }
                $lastMatchIndex = $offset;
                $wildcard = false;
                $offset++;
                continue 2;
            }

            return false;
        }

        return true;
    }

    /**
     * Returns an absolute offset in the children array.
     * @param int $offset
     * @return int
     */
    private function calculateOffset($offset)
    {
        if ($offset >= 0) {
            return $offset;
        }

        return count($this->_children) + $offset;
    }

    /**
     * Updates token SQL code start and end offsets based on its children.
     */
    private function updateCollectionOffsets()
    {
        if (!empty($this->_children)) {
            $this->startOffset = reset($this->_children)->startOffset;
            $this->endOffset = end($this->_children)->endOffset;
        }
        if ($this->parent !== null) {
            $this->parent->updateCollectionOffsets();
        }
    }
}
