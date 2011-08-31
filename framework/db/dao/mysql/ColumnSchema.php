<?php
/**
 * ColumnSchema class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\dao\mysql;

/**
 * ColumnSchema class describes the column meta data of a MySQL table.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ColumnSchema extends \yii\db\dao\ColumnSchema
{
	/**
	 * Extracts the PHP type from DB type.
	 * @param string $dbType DB type
	 */
	public function initTypes($dbType)
	{
		static $typeMap = array(  // dbType => type
			'tinyint' => 'smallint',
			'bit' => 'smallint',
			'smallint' => 'smallint',
			'mediumint' => 'integer',
			'int' => 'integer',
			'integer' => 'integer',
			'bigint' => 'bigint',
			'float' => 'float',
			'double' => 'float',
			'real' => 'float',
			'decimal' => 'decimal',
			'numeric' => 'decimal',
            'tinytext' => 'text',
            'mediumtext' => 'text',
            'longtext' => 'text',
            'text' => 'text',
            'varchar' => 'string',
            'string' => 'string',
            'char' => 'string',
			'datetime' => 'datetime',
			'year' => 'date',
			'date' => 'date',
			'time' => 'time',
			'timestamp' => 'timestamp',
			'enum' => 'enum',
		);

		$this->dbType = $dbType;
		$this->type = 'string';
		$this->unsigned = strpos($this->dbType, 'unsigned') !== false;

		if (preg_match('/^(\w+)(?:\(([^\)]+)\))?/', $this->dbType, $matches)) {
			$type = $matches[1];
			if (isset($typeMap[$type])) {
				$this->type = $typeMap[$type];
			}

			if (!empty($matches[2])) {
				if ($this->type === 'enum') {
					$values = explode(',', $matches[2]);
					foreach ($values as $i => $value) {
						$values[$i] = trim($value, "'");
					}
					$this->enumValues = $values;
				}
				else {
					$values = explode(',', $matches[2]);
					$this->size = $this->precision = (int)$values[0];
					if (isset($values[1])) {
						$this->scale = (int)$values[1];
					}
					if ($this->size === 1 && ($type === 'tinyint' || $type === 'bit')) {
						$this->type = 'boolean';
					}
					elseif ($type === 'bit') {
						if ($this->size > 32) {
							$this->type = 'bigint';
						}
						elseif ($this->size === 32) {
							$this->type = 'integer';
						}
					}
				}
			}
		}

		$this->phpType = $this->getPhpType();
	}

	/**
	 * Extracts the default value for the column.
	 * The value is typecasted to correct PHP type.
	 * @param mixed $defaultValue the default value obtained from metadata
	 */
	public function initDefaultValue($defaultValue)
	{
		if ($this->type === 'timestamp' && $defaultValue === 'CURRENT_TIMESTAMP') {
			$this->defaultValue = null;
		}
		else {
			$this->defaultValue = $this->typecast($defaultValue);
		}
	}
}