<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\pgsql;

/**
 * QueryBuilder is the query builder for PostgreSQL databases.
 *
 * @author Gevik Babakhani <gevikb@gmail.com>
 * @since 2.0
 */
class QueryBuilder extends \yii\db\QueryBuilder {

	/**
	 * @var array mapping from abstract column types (keys) to physical column types (values).
	 */
	public $typeMap = array(
	    Schema::TYPE_PK => 'bigserial not null primary key',
	    Schema::TYPE_STRING => 'varchar',
	    Schema::TYPE_TEXT => 'text',
	    Schema::TYPE_SMALLINT => 'smallint',
	    Schema::TYPE_INTEGER => 'integer',
	    Schema::TYPE_BIGINT => 'bigint',
	    Schema::TYPE_FLOAT => 'real',
	    Schema::TYPE_DECIMAL => 'decimal',
	    Schema::TYPE_DATETIME => 'timestamp',
	    Schema::TYPE_TIMESTAMP => 'timestamp',
	    Schema::TYPE_TIME => 'time',
	    Schema::TYPE_DATE => 'date',
	    Schema::TYPE_BINARY => 'bytea',
	    Schema::TYPE_BOOLEAN => 'boolean',
	    Schema::TYPE_MONEY => 'numeric(19,4)',
	);

}
