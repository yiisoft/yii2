<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\conditions;

use yii\base\InvalidArgumentException;
use yii\db\ExpressionBuilderInterface;
use yii\db\ExpressionBuilderTrait;
use yii\db\ExpressionInterface;

/**
 * 类 LikeConditionBuilder 构建 [[LikeCondition]] 类的对象
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class LikeConditionBuilder implements ExpressionBuilderInterface
{
    use ExpressionBuilderTrait;

    /**
     * @var array 在 LIKE 条件下，字符到替换字符的映射。
     * 默认情况下，指定用 `\` 转义 `%`、`_` 以及 `\`。
     */
    protected $escapingReplacements = [
        '%' => '\%',
        '_' => '\_',
        '\\' => '\\\\',
    ];
    /**
     * @var string|null 用于在 LIKE 条件下转义特殊字符的字符。
     * 默认情况下，其假定为 `\`。
     */
    protected $escapeCharacter;


    /**
     * 从不会被额外转义或引用的 $expression 接口
     * 构建原始 SQL 语句的方法。
     *
     * @param ExpressionInterface|LikeCondition $expression 构建的表达式。
     * @param array $params 绑定参数。
     * @return string 不会被额外转义或引用的原始 SQL 语句。
     */
    public function build(ExpressionInterface $expression, array &$params = [])
    {
        $operator = $expression->getOperator();
        $column = $expression->getColumn();
        $values = $expression->getValue();
        $escape = $expression->getEscapingReplacements();
        if ($escape === null || $escape === []) {
            $escape = $this->escapingReplacements;
        }

        list($andor, $not, $operator) = $this->parseOperator($operator);

        if (!is_array($values)) {
            $values = [$values];
        }

        if (empty($values)) {
            return $not ? '' : '0=1';
        }

        if (strpos($column, '(') === false) {
            $column = $this->queryBuilder->db->quoteColumnName($column);
        }

        $escapeSql = $this->getEscapeSql();
        $parts = [];
        foreach ($values as $value) {
            if ($value instanceof ExpressionInterface) {
                $phName = $this->queryBuilder->buildExpression($value, $params);
            } else {
                $phName = $this->queryBuilder->bindParam(empty($escape) ? $value : ('%' . strtr($value, $escape) . '%'), $params);
            }
            $parts[] = "{$column} {$operator} {$phName}{$escapeSql}";
        }

        return implode($andor, $parts);
    }

    /**
     * @return string
     */
    private function getEscapeSql()
    {
        if ($this->escapeCharacter !== null) {
            return " ESCAPE '{$this->escapeCharacter}'";
        }

        return '';
    }

    /**
     * @param string $operator
     * @return array
     */
    protected function parseOperator($operator)
    {
        if (!preg_match('/^(AND |OR |)(((NOT |))I?LIKE)/', $operator, $matches)) {
            throw new InvalidArgumentException("Invalid operator '$operator'.");
        }
        $andor = ' ' . (!empty($matches[1]) ? $matches[1] : 'AND ');
        $not = !empty($matches[3]);
        $operator = $matches[2];

        return [$andor, $not, $operator];
    }
}
