<?php
/**
 * ColumnSchema class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\dao;

/**
 * ColumnSchema class describes the meta data of a column in a database table.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ColumnSchema extends \yii\base\Component
{
	/**
	 * The followings are the supported abstract column data types.
	 */
	const TYPE_STRING = 'string';
	const TYPE_TEXT = 'text';
	const TYPE_SMALLINT = 'smallint';
	const TYPE_INTEGER = 'integer';
	const TYPE_BIGINT = 'bigint';
	const TYPE_FLOAT = 'float';
	const TYPE_DECIMAL = 'decimal';
	const TYPE_DATETIME = 'datetime';
	const TYPE_TIMESTAMP = 'timestamp';
	const TYPE_TIME = 'time';
	const TYPE_DATE = 'date';
	const TYPE_BINARY = 'binary';
	const TYPE_BOOLEAN = 'boolean';
	const TYPE_MONEY = 'money';

	/**
	 * @var string name of this column (without quotes).
	 */
	public $name;
	/**
	 * @var string the quoted name of this column.
	 */
	public $quotedName;
	/**
	 * @var boolean whether this column can be null.
	 */
	public $allowNull;
	/**
	 * @var string logical type of this column. Possible logic types include:
	 * string, text, boolean, smallint, integer, bigint, float, decimal, datetime,
	 * timestamp, time, date, binary, and money.
	 */
	public $type;
	/**
	 * @var string the PHP type of this column. Possible PHP types include:
	 * string, boolean, integer, double.
	 */
	public $phpType;
	/**
	 * @var string the DB type of this column. Possible DB types vary according to the type of DBMS.
	 */
	public $dbType;
	/**
	 * @var mixed default value of this column
	 */
	public $defaultValue;
	/**
	 * @var array enumerable values. This is set only if the column is declared to be an enumerable type.
	 */
	public $enumValues;
	/**
	 * @var integer display size of the column.
	 */
	public $size;
	/**
	 * @var integer precision of the column data, if it is numeric.
	 */
	public $precision;
	/**
	 * @var integer scale of the column data, if it is numeric.
	 */
	public $scale;
	/**
	 * @var boolean whether this column is a primary key
	 */
	public $isPrimaryKey;
	/**
	 * @var boolean whether this column is auto-incremental
	 */
	public $autoIncrement = false;
	/**
	 * @var boolean whether this column is unsigned. This is only meaningful
	 * when [[type]] is `smallint`, `integer` or `bigint`.
	 */
	public $unsigned;

	/**
	 * Extracts the PHP type from DB type.
	 * @return string PHP type name.
	 */
	protected function extractPhpType()
	{
		static $typeMap = array( // logical type => php type
			'smallint' => 'integer',
			'integer' => 'integer',
			'bigint' => 'integer',
			'boolean' => 'boolean',
			'float' => 'double',
		);
		if (isset($typeMap[$this->type])) {
			if ($this->type === 'bigint') {
				return PHP_INT_SIZE == 8 && !$this->unsigned ? 'integer' : 'string';
			} elseif ($this->type === 'integer') {
				return PHP_INT_SIZE == 4 && $this->unsigned ? 'string' : 'integer';
			}
			return $typeMap[$this->type];
		}
		return 'string';
	}

	/**
	 * Converts the input value according to [[phpType]].
	 * If the value is null or an [[Expression]], it will not be converted.
	 * @param mixed $value input value
	 * @return mixed converted value
	 */
	public function typecast($value)
	{
		if ($value === null || gettype($value) === $this->phpType || $value instanceof Expression) {
			return $value;
		}
		switch ($this->phpType) {
			case 'string':
				return (string)$value;
			case 'integer':
				return (integer)$value;
			case 'boolean':
				return (boolean)$value;
		}
		return $value;
	}
}
