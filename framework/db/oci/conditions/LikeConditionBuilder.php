<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\oci\conditions;

use yii\db\ExpressionInterface;

/**
 * {@inheritdoc}
 */
class LikeConditionBuilder extends \yii\db\conditions\LikeConditionBuilder
{
    /**
     * {@inheritdoc}
     */
    protected $escapeCharacter = '!';
    /**
     * `\` is initialized in [[buildLikeCondition()]] method since
     * we need to choose replacement value based on [[\yii\db\Schema::quoteValue()]].
     * {@inheritdoc}
     */
    protected $escapingReplacements = [
        '%' => '!%',
        '_' => '!_',
        '!' => '!!',
    ];


    /**
     * {@inheritdoc}
     */
    public function build(ExpressionInterface $expression, array &$params = [])
    {
        if (!isset($this->escapingReplacements['\\'])) {
            /*
             * Different pdo_oci8 versions may or may not implement PDO::quote(), so
             * yii\db\Schema::quoteValue() may or may not quote \.
             */
            $this->escapingReplacements['\\'] = substr($this->queryBuilder->db->quoteValue('\\'), 1, -1);
        }

        return parent::build($expression, $params);
    }
}
