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
 * ColumnSchema class describes the meta data of a MySQL table column.
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
			'tinyint' => self::TYPE_SMALLINT,
			'bit' => self::TYPE_SMALLINT,
			'smallint' => self::TYPE_SMALLINT,
			'mediumint' => self::TYPE_INTEGER,
			'int' => self::TYPE_INTEGER,
			'integer' => self::TYPE_INTEGER,
			'bigint' => self::TYPE_BIGINT,
			'float' => self::TYPE_FLOAT,
			'double' => self::TYPE_FLOAT,
			'real' => self::TYPE_FLOAT,
			'decimal' => self::TYPE_DECIMAL,
			'numeric' => self::TYPE_DECIMAL,
            'tinytext' => self::TYPE_TEXT,
            'mediumtext' => self::TYPE_TEXT,
            'longtext' => self::TYPE_TEXT,
            'text' => self::TYPE_TEXT,
            'varchar' => self::TYPE_STRING,
            'string' => self::TYPE_STRING,
            'char' => self::TYPE_STRING,
			'datetime' => self::TYPE_DATETIME,
			'year' => self::TYPE_DATE,
			'date' => self::TYPE_DATE,
			'time' => self::TYPE_TIME,
			'timestamp' => self::TYPE_TIMESTAMP,
			'enum' => self::TYPE_STRING,
		);

		$this->dbType = $dbType;
		$this->type = self::TYPE_STRING;
		$this->unsigned = strpos($this->dbType, 'unsigned') !== false;

		if (preg_match('/^(\w+)(?:\(([^\)]+)\))?/', $this->dbType, $matches)) {
			$type = $matches[1];
			if (isset($typeMap[$type])) {
				$this->type = $typeMap[$type];
			}

			if (!empty($matches[2])) {
				if ($type === 'enum') {
					$values = explode(',', $matches[2]);
					foreach ($values as $i => $value) {
						$values[$i] = trim($value, "'");
					}
					$this->enumValues = $values;
				} else {
					$values = explode(',', $matches[2]);
					$this->size = $this->precision = (int)$values[0];
					if (isset($values[1])) {
						$this->scale = (int)$values[1];
					}
					if ($this->size === 1 && ($type === 'tinyint' || $type === 'bit')) {
						$this->type = 'boolean';
					} elseif ($type === 'bit') {
						if ($this->size > 32) {
							$this->type = 'bigint';
						} elseif ($this->size === 32) {
							$this->type = 'integer';
						}
					}
				}
			}
		}

		$this->phpType = $this->extractPhpType();
	}

	/**
	 * Extracts the default value for the column.
	 * The value is typecast to correct PHP type.
	 * @param mixed $defaultValue the default value obtained from metadata
	 */
	public function initDefaultValue($defaultValue)
	{
		if ($this->type === 'timestamp' && $defaultValue === 'CURRENT_TIMESTAMP') {
			$this->defaultValue = null;
		} else {
			$this->defaultValue = $this->typecast($defaultValue);
		}
	}
}