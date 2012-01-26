<?php

namespace yii\db\ar;

use yii\db\Exception;

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
		$table = $modelClass::getDbConnection()->getDriver()->getTableSchema($tableName);
		if ($table === null) {
			throw new Exception("Unable to find table '$tableName' for ActiveRecord class '$modelClass'.");
		}
		if ($table->primaryKey === null) {
			$primaryKey = $modelClass::primaryKey();
			if ($primaryKey !== null) {
				$table->fixPrimaryKey($primaryKey);
			} else {
				throw new Exception("The table '$tableName' for ActiveRecord class '$modelClass' does not have a primary key.");
			}
		}
		$this->table = $table;

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
			$relation->modelClass = '\\' . $matches[2];
			$relation->hasMany = isset($matches[3]);
			$this->relations[$relation->name] = $relation;
		} else {
			throw new Exception("Relation name in bad format: $name");
		}
	}
}