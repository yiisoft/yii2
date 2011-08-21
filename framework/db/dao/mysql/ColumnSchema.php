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
	protected function extractType($dbType)
	{
		if (strncmp($dbType, 'enum', 4) === 0)
			$this->type = 'string';
		elseif (strpos($dbType, 'float') !== false || strpos($dbType, 'double') !== false)
			$this->type = 'double';
		elseif (strpos($dbType, 'bool') !== false)
			$this->type = 'boolean';
		elseif (strpos($dbType, 'int') === 0 && strpos($dbType, 'unsigned') === false || preg_match('/(bit|tinyint|smallint|mediumint)/', $dbType))
			$this->type = 'integer';
		else
			$this->type = 'string';
	}

	/**
	 * Extracts the default value for the column.
	 * The value is typecasted to correct PHP type.
	 * @param mixed $defaultValue the default value obtained from metadata
	 */
	protected function extractDefault($defaultValue)
	{
		if ($this->dbType === 'timestamp' && $defaultValue === 'CURRENT_TIMESTAMP')
			$this->defaultValue = null;
		else
			parent::extractDefault($defaultValue);
	}

	/**
	 * Extracts size, precision and scale information from column's DB type.
	 * @param string $dbType the column's DB type
	 */
	protected function extractLimit($dbType)
	{
		if (strncmp($dbType, 'enum', 4) === 0 && preg_match('/\((.*)\)/', $dbType, $matches))
		{
			$values = explode(',', $matches[1]);
			$size = 0;
			foreach ($values as $value)
			{
				if (($n = strlen($value)) > $size)
					$size = $n;
			}
			$this->size = $this->precision = $size-2;
		}
		else
			parent::extractLimit($dbType);
	}
}