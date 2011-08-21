<?php
/**
 * ColumnSchema class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * ColumnSchema class describes the column meta data of a database table.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ColumnSchema extends CComponent
{
	/**
	 * @var string name of this column (without quotes).
	 */
	public $name;
	/**
	 * @var string raw name of this column. This is the quoted name that can be used in SQL queries.
	 */
	public $quotedName;
	/**
	 * @var boolean whether this column can be null.
	 */
	public $allowNull;
	/**
	 * @var string logical type of this column. Possible logic types include:
	 * string, text, boolean, integer, float, decimal, datetime, timestamp, time, date, binary
	 */
	public $type;
	/**
	 * @var string the PHP type of this column. Possible PHP types include:
	 * string, boolean, integer, double.
	 */
	public $phpType;
	/**
	 * @var string the DB type of this column.
	 */
	public $dbType;
	/**
	 * @var mixed default value of this column
	 */
	public $defaultValue;
	/**
	 * @var integer size of the column.
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
	 * @var boolean whether this column is a foreign key
	 */
	public $isForeignKey;
	/**
	 * @var boolean whether this column is auto-incremental
	 */
	public $autoIncrement = false;


	/**
	 * Initializes the column with its DB type and default value.
	 * This sets up the column's PHP type, size, precision, scale as well as default value.
	 * @param string $dbType the column's DB type
	 * @param mixed $defaultValue the default value
	 */
	public function init($dbType, $defaultValue)
	{
		$this->dbType = $dbType;
		$this->extractType($dbType);
		$this->extractLimit($dbType);
		if ($defaultValue !== null)
			$this->extractDefault($defaultValue);
	}

	/**
	 * Extracts the PHP type from DB type.
	 * @param string $dbType DB type
	 */
	protected function extractType($dbType)
	{
		if (stripos($dbType, 'int') !== false && stripos($dbType, 'unsigned int') === false)
			$this->type = 'integer';
		elseif (stripos($dbType, 'bool') !== false)
			$this->type = 'boolean';
		elseif (preg_match('/(real|floa|doub)/i', $dbType))
			$this->type = 'double';
		else
			$this->type = 'string';
	}

	/**
	 * Extracts size, precision and scale information from column's DB type.
	 * @param string $dbType the column's DB type
	 */
	protected function extractLimit($dbType)
	{
		if (strpos($dbType, '(') && preg_match('/\((.*)\)/', $dbType, $matches))
		{
			$values = explode(',', $matches[1]);
			$this->size = $this->precision = (int)$values[0];
			if (isset($values[1]))
				$this->scale = (int)$values[1];
		}
	}

	/**
	 * Extracts the default value for the column.
	 * The value is typecasted to correct PHP type.
	 * @param mixed $defaultValue the default value obtained from metadata
	 */
	protected function extractDefault($defaultValue)
	{
		$this->defaultValue = $this->typecast($defaultValue);
	}

	/**
	 * Converts the input value to the type that this column is of.
	 * @param mixed $value input value
	 * @return mixed converted value
	 */
	public function typecast($value)
	{
		if (gettype($value) === $this->type || $value === null || $value instanceof CDbExpression)
			return $value;
		if ($value === '')
			return $this->type === 'string' ? '' : null;
		switch ($this->type)
		{
			case 'string': return (string)$value;
			case 'integer': return (integer)$value;
			case 'boolean': return (boolean)$value;
			case 'double':
			default: return $value;
		}
	}
}
