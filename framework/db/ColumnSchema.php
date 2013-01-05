<?php
/**
 * ColumnSchema class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * ColumnSchema class describes the metadata of a column in a database table.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ColumnSchema extends \yii\base\Component
{
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
	 */
	public function resolvePhpType()
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
				$this->phpType = PHP_INT_SIZE == 8 && !$this->unsigned ? 'integer' : 'string';
			} elseif ($this->type === 'integer') {
				$this->phpType = PHP_INT_SIZE == 4 && $this->unsigned ? 'string' : 'integer';
			} else {
				$this->phpType = $typeMap[$this->type];
			}
		} else {
			$this->phpType = 'string';
		}
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
