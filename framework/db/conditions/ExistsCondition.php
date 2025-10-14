<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\conditions;

use yii\base\InvalidArgumentException;
use yii\db\Query;

/**
 * Condition that represents `EXISTS` operator.
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 * @phpcs:disable Squiz.NamingConventions.ValidVariableName.PrivateNoUnderscore
 */
class ExistsCondition implements ConditionInterface
{
    /**
     * @var string $operator the operator to use (e.g. `EXISTS` or `NOT EXISTS`)
     */
    private $operator;
    /**
     * @var Query the [[Query]] object representing the sub-query.
     */
    private $query;


    /**
     * ExistsCondition constructor.
     *
     * @param string $operator the operator to use (e.g. `EXISTS` or `NOT EXISTS`)
     * @param Query $query the [[Query]] object representing the sub-query.
     */
    public function __construct($operator, $query)
    {
        $this->operator = $operator;
        $this->query = $query;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArrayDefinition($operator, $operands)
    {
        if (!isset($operands[0]) || !$operands[0] instanceof Query) {
            throw new InvalidArgumentException('Subquery for EXISTS operator must be a Query object.');
        }

        return new static($operator, $operands[0]);
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return Query
     */
    public function getQuery()
    {
        return $this->query;
    }
}
