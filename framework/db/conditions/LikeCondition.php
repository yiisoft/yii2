<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\conditions;

use yii\base\InvalidArgumentException;

/**
 * Class LikeCondition represents a `LIKE` condition.
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class LikeCondition extends SimpleCondition
{
    /**
     * @var array|false map of chars to their replacements, false if characters should not be escaped
     * or either null or empty array if escaping is condition builder responsibility.
     * By default it's set to `null`.
     */
    protected $escapingReplacements;


    /**
     * @param string $column the column name.
     * @param string $operator the operator to use (e.g. `LIKE`, `NOT LIKE`, `OR LIKE` or `OR NOT LIKE`)
     * @param string[]|string $value single value or an array of values that $column should be compared with.
     * If it is an empty array the generated expression will  be a `false` value if operator is `LIKE` or `OR LIKE`
     * and empty if operator is `NOT LIKE` or `OR NOT LIKE`.
     */
    public function __construct($column, $operator, $value)
    {
        parent::__construct($column, $operator, $value);
    }

    /**
     * This method allows to specify how to escape special characters in the value(s).
     *
     * @param array an array of mappings from the special characters to their escaped counterparts.
     * You may use `false` or an empty array to indicate the values are already escaped and no escape
     * should be applied. Note that when using an escape mapping (or the third operand is not provided),
     * the values will be automatically enclosed within a pair of percentage characters.
     */
    public function setEscapingReplacements($escapingReplacements)
    {
        $this->escapingReplacements = $escapingReplacements;
    }

    /**
     * @return array|false
     */
    public function getEscapingReplacements()
    {
        return $this->escapingReplacements;
    }

    /**
     * {@inheritdoc}
     * @throws InvalidArgumentException if wrong number of operands have been given.
     */
    public static function fromArrayDefinition($operator, $operands)
    {
        if (!isset($operands[0], $operands[1])) {
            throw new InvalidArgumentException("Operator '$operator' requires two operands.");
        }

        $condition = new static($operands[0], $operator, $operands[1]);
        if (isset($operands[2])) {
            $condition->escapingReplacements = $operands[2];
        }

        return $condition;
    }
}
