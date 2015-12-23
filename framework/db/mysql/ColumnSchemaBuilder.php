<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mysql;

use yii\db\ColumnSchemaBuilder as AbstractColumnSchemaBuilder;

/**
 * ColumnSchemaBuilder is the schema builder for MySQL databases.
 *
 * @author Sanjar Khaytmetov <sanjar.khaytmetov@gmail.com>
 * @since 2.0.6
 */
class ColumnSchemaBuilder extends AbstractColumnSchemaBuilder
{
    /**
     * @var boolean whether the column is unsigned. If this is `true`, a `UNSIGNED` attribute will be added.
     */
    protected $isUnsigned = false;
    /**
     * @var string column comment.
     */
    protected $comment = '';

    /**
     * Adds a `UNSIGNED` to permit only nonnegative numbers.
     * @return $this
     */
    public function unsigned()
    {
        $this->isUnsigned = true;
        return $this;
    }

    /**
     * Adds a `COMMENT '{comment}'` to a column.
     * @return $this
     */
    public function comment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return
            $this->type .
            $this->buildLengthString() .
            $this->buildUnsignedString() .
            $this->buildDefaultString() .
            $this->buildNotNullString() .
            $this->buildCheckString() .
            $this->buildCommentString();
    }

    /**
     * Builds the unsigned attribute for the column.
     * @return string returns 'UNSIGNED' if [[isUnsigned]] is true, otherwise it returns an empty string.
     */
    protected function buildUnsignedString()
    {
        return $this->isUnsigned ? ' UNSIGNED' : '';
    }

    /**
     * Builds the comment for the column.
     * @return string returns "COMMENT '{comment}'" if [[comment]] is not empty, otherwise it returns an empty string.
     */
    protected function buildCommentString()
    {
        return $this->comment ? "COMMENT '" . addslashes($this->comment) . "'" : '';
    }

}
