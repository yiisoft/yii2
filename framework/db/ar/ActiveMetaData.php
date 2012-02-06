<?php

namespace yii\db\ar;

use yii\db\Exception;
use yii\db\dao\TableSchema;

/**
 * ActiveMetaData represents the meta-data for an Active Record class.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveMetaData
{
	/**
	 * @var TableSchema the table schema information
	 */
	public $table;
	/**
	 * @var string the model class name
	 */
	public $modelClass;
	/**
	 * @var array list of relations
	 */
	public $relations = array();

	/**
	 * Constructor.
	 * @param string $modelClass the model class name
	 */
	public function __construct($modelClass)
	{
		$tableName = $modelClass::tableName();
		$this->table = $modelClass::getDbConnection()->getDriver()->getTableSchema($tableName);
		$this->modelClass = $modelClass;
		if ($this->table === null) {
			throw new Exception("Unable to find table '$tableName' for ActiveRecord class '$modelClass'.");
		}
		$primaryKey = $modelClass::primaryKey();
		if ($primaryKey !== null) {
			$this->table->fixPrimaryKey($primaryKey);
		} elseif ($this->table->primaryKey === null) {
			throw new Exception("The table '$tableName' for ActiveRecord class '$modelClass' does not have a primary key.");
		}

		foreach ($modelClass::relations() as $name => $config) {
			$this->addRelation($name, $config);
		}
	}

	/**
	 * Adds a relation.
	 *
	 * $config is an array with three elements:
	 * relation type, the related active record class and the foreign key.
	 *
	 * @throws Exception
	 * @param string $name $name Name of the relation.
	 * @param array $config $config Relation parameters.
	 * @return void
	 */
	public function addRelation($name, $config)
	{
		if (preg_match('/^(\w+)\s*:\s*\\\\?([\w\\\\]+)(\[\])?$/', $name, $matches)) {
			if (is_string($config)) {
				$config = array('on' => $config);
			}
			$relation = ActiveRelation::newInstance($config);
			$relation->name = $matches[1];
			$modelClass = $matches[2];
			if (strpos($modelClass, '\\') !== false) {
				$relation->modelClass = '\\' . ltrim($modelClass, '\\');
			} else {
				$relation->modelClass = dirname($this->modelClass) . '\\' . $modelClass;
			}
			$relation->hasMany = isset($matches[3]);
			$this->relations[$relation->name] = $relation;
		} else {
			throw new Exception("{$this->modelClass} has an invalid relation: $name");
		}
	}
}