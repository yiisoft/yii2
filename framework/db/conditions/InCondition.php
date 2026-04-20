<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\conditions;

use Traversable;
use yii\base\InvalidArgumentException;
use yii\db\ExpressionInterface;

/**
 * Represents `IN` condition.
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class InCondition implements ConditionInterface
{
    /**
     * @param array|string|ExpressionInterface|Traversable $column the column name. If it is an array, a composite `IN`
     * condition will be generated.
     * @param string $operator the operator to use (e.g. `IN` or `NOT IN`).
     * @param array|int|string|ExpressionInterface|Traversable $values an array of values that [[column]] value should
     * be among. If it is an empty array the generated expression will be a `false` value if [[operator]] is `IN` and
     * empty if operator is `NOT IN`.
     */
    public function __construct(
        private array|string|ExpressionInterface|Traversable $column,
        private string $operator,
        private array|int|string|ExpressionInterface|Traversable $values
    ) {
    }

    /**
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @return array|string|ExpressionInterface
     */
    public function getColumn(): array|string|ExpressionInterface
    {
        if ($this->column instanceof Traversable && !$this->column instanceof ExpressionInterface) {
            $this->column = iterator_to_array($this->column);
        }

        return $this->column;
    }

    /**
     * @return array|int|string|ExpressionInterface
     */
    public function getValues(): array|int|string|ExpressionInterface
    {
        if ($this->values instanceof Traversable && !$this->values instanceof ExpressionInterface) {
            $this->values = iterator_to_array($this->values);
        }

        return $this->values;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException if wrong number of operands have been given.
     */
    public static function fromArrayDefinition($operator, $operands): static
    {
        if (!isset($operands[0], $operands[1])) {
            throw new InvalidArgumentException("Operator '$operator' requires two operands.");
        }

        return new static($operands[0], $operator, $operands[1]);
    }
}
