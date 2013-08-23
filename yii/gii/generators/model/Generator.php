<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\generators\model;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\gii\CodeFile;
use yii\helpers\Inflector;

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{
	public $db = 'db';
	public $ns = 'app\models';
	public $tableName;
	public $modelClass;
	public $baseClass = '\yii\db\ActiveRecord';
	public $generateRelations = true;
	public $generateLabelsFromComments = false;


	public function getName()
	{
		return 'Model Generator';
	}

	public function getDescription()
	{
		return 'This generator generates an ActiveRecord class for the specified database table.';
	}

	public function rules()
	{
		return array_merge(parent::rules(), array(
			array('db, ns, tableName, modelClass, baseClass', 'filter', 'filter' => 'trim'),
			array('db, ns, tableName, baseClass', 'required'),
			array('db, modelClass', 'match', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'),
			array('ns, baseClass', 'match', 'pattern' => '/^[\w\\\\]+$/', 'message' => 'Only word characters and backslashes are allowed.'),
			array('tableName', 'match', 'pattern' => '/^(\w+\.)?\w+\*?$/', 'message' => 'Only word characters, and optionally an asterisk and/or a dot are allowed.'),
			array('db', 'validateDb'),
			array('ns', 'validateNamespace'),
			array('tableName', 'validateTableName'),
			array('modelClass', 'validateModelClass'),
			array('baseClass', 'validateClass', 'params' => array('extends' => ActiveRecord::className())),
			array('generateRelations, generateLabelsFromComments', 'boolean'),
		));
	}

	public function attributeLabels()
	{
		return array(
			'ns' => 'Namespace',
			'db' => 'Database Connection ID',
			'tableName' => 'Table Name',
			'modelClass' => 'Model Class',
			'baseClass' => 'Base Class',
			'generateRelations' => 'Generate Relations',
			'generateLabelsFromComments' => 'Generate Labels from DB Comments',
		);
	}

	public function hints()
	{
		return array(
			'ns' => 'This is the namespace of the ActiveRecord class to be generated, e.g., <code>app\models</code>',
			'db' => 'This is the ID of the DB application component.',
			'tableName' => 'This is the name of the DB table that the new ActiveRecord class is associated with, e.g. <code>tbl_post</code>.
				The table name may consist of the DB schema part if needed, e.g. <code>public.tbl_post</code>.
				The table name may contain an asterisk at the end to match multiple table names, e.g. <code>tbl_*</code>.
				In this case, multiple ActiveRecord classes will be generated, one for each matching table name.',
			'modelClass' => 'This is the name of the ActiveRecord class to be generated. The class name should not contain
				the namespace part as it is specified in "Namespace". You do not need to specify the class name
				if "Table Name" contains an asterisk at the end, in which case multiple ActiveRecord classes will be generated.',
			'baseClass' => 'This is the base class of the new ActiveRecord class. It should be a fully qualified namespaced class name.',
			'generateRelations' => 'This indicates whether the generator should generate relations based on
				foreign key constraints it detects in the database. Note that if your database contains too many tables,
				you may want to uncheck this option to accelerate the code generation proc	ess.',
			'generateLabelsFromComments' => 'This indicates whether the generator should generate attribute labels
				by using the comments of the corresponding DB columns.',
		);
	}

	public function requiredTemplates()
	{
		return array(
			'model.php',
		);
	}

	public function stickyAttributes()
	{
		return array('ns', 'db', 'baseClass', 'generateRelations', 'generateLabelsFromComments');
	}

	/**
	 * @return Connection
	 */
	public function getDbConnection()
	{
		return Yii::$app->{$this->db};
	}

	public function generate()
	{
		$files = array();
		foreach ($this->getTableNames() as $tableName) {
			$className = $this->generateClassName($tableName);
			$tableSchema = $this->getTableSchema($tableName);
			$params = array(
				'tableName' => $tableName,
				'className' => $className,
				'tableSchema' => $tableSchema,
				'labels' => $this->generateLabels($tableSchema),
			);
			$files[] = new CodeFile(
				Yii::getAlias('@' . $this->ns) . '/' . $className . '.php',
				$this->render('model.php', $params)
			);
		}

		return $files;
	}

	public function getTableSchema($tableName)
	{
		return $this->getDbConnection()->getTableSchema($tableName, true);
	}

	public function generateLabels($table)
	{
		$labels = array();
		foreach ($table->columns as $column) {
			if ($this->generateLabelsFromComments && !empty($column->comment)) {
				$labels[$column->name] = $column->comment;
			} else {
				$label = Inflector::camel2words($column->name);
				if (strcasecmp(substr($label, -3), ' id') === 0) {
					$label = substr($label, 0, -3) . ' ID';
				}
				$labels[$column->name] = $label;
			}
		}
		return $labels;
	}

	public function generateRules($table)
	{
		$rules = array();
		$required = array();
		$integers = array();
		$numerical = array();
		$length = array();
		$safe = array();
		foreach ($table->columns as $column) {
			if ($column->autoIncrement) {
				continue;
			}
			$r = !$column->allowNull && $column->defaultValue === null;
			if ($r) {
				$required[] = $column->name;
			}
			if ($column->type === 'integer') {
				$integers[] = $column->name;
			} elseif ($column->type === 'double') {
				$numerical[] = $column->name;
			} elseif ($column->type === 'string' && $column->size > 0) {
				$length[$column->size][] = $column->name;
			} elseif (!$column->isPrimaryKey && !$r) {
				$safe[] = $column->name;
			}
		}
		if ($required !== array()) {
			$rules[] = "array('" . implode(', ', $required) . "', 'required')";
		}
		if ($integers !== array()) {
			$rules[] = "array('" . implode(', ', $integers) . "', 'numerical', 'integerOnly'=>true)";
		}
		if ($numerical !== array()) {
			$rules[] = "array('" . implode(', ', $numerical) . "', 'numerical')";
		}
		if ($length !== array()) {
			foreach ($length as $len => $cols) {
				$rules[] = "array('" . implode(', ', $cols) . "', 'length', 'max'=>$len)";
			}
		}
		if ($safe !== array()) {
			$rules[] = "array('" . implode(', ', $safe) . "', 'safe')";
		}

		return $rules;
	}

	public function getRelations($className)
	{
		return isset($this->relations[$className]) ? $this->relations[$className] : array();
	}

	protected function removePrefix($tableName, $addBrackets = true)
	{
		if ($addBrackets && Yii::$app->{$this->connectionId}->tablePrefix == '') {
			return $tableName;
		}
		$prefix = $this->tablePrefix != '' ? $this->tablePrefix : Yii::$app->{$this->connectionId}->tablePrefix;
		if ($prefix != '') {
			if ($addBrackets && Yii::$app->{$this->connectionId}->tablePrefix != '') {
				$prefix = Yii::$app->{$this->connectionId}->tablePrefix;
				$lb = '{{';
				$rb = '}}';
			} else {
				$lb = $rb = '';
			}
			if (($pos = strrpos($tableName, '.')) !== false) {
				$schema = substr($tableName, 0, $pos);
				$name = substr($tableName, $pos + 1);
				if (strpos($name, $prefix) === 0) {
					return $schema . '.' . $lb . substr($name, strlen($prefix)) . $rb;
				}
			} elseif (strpos($tableName, $prefix) === 0) {
				return $lb . substr($tableName, strlen($prefix)) . $rb;
			}
		}
		return $tableName;
	}

	protected function generateRelations()
	{
		if (!$this->generateRelations) {
			return array();
		}

		$schemaName = '';
		if (($pos = strpos($this->tableName, '.')) !== false) {
			$schemaName = substr($this->tableName, 0, $pos);
		}

		$relations = array();
		foreach (Yii::$app->{$this->connectionId}->schema->getTables($schemaName) as $table) {
			if ($this->tablePrefix != '' && strpos($table->name, $this->tablePrefix) !== 0) {
				continue;
			}
			$tableName = $table->name;

			if ($this->isRelationTable($table)) {
				$pks = $table->primaryKey;
				$fks = $table->foreignKeys;

				$table0 = $fks[$pks[0]][0];
				$table1 = $fks[$pks[1]][0];
				$className0 = $this->generateClassName($table0);
				$className1 = $this->generateClassName($table1);

				$unprefixedTableName = $this->removePrefix($tableName);

				$relationName = $this->generateRelationName($table0, $table1, true);
				$relations[$className0][$relationName] = "array(self::MANY_MANY, '$className1', '$unprefixedTableName($pks[0], $pks[1])')";

				$relationName = $this->generateRelationName($table1, $table0, true);

				$i = 1;
				$rawName = $relationName;
				while (isset($relations[$className1][$relationName])) {
					$relationName = $rawName . $i++;
				}

				$relations[$className1][$relationName] = "array(self::MANY_MANY, '$className0', '$unprefixedTableName($pks[1], $pks[0])')";
			} else {
				$className = $this->generateClassName($tableName);
				foreach ($table->foreignKeys as $fkName => $fkEntry) {
					// Put table and key name in variables for easier reading
					$refTable = $fkEntry[0]; // Table name that current fk references to
					$refKey = $fkEntry[1]; // Key in that table being referenced
					$refClassName = $this->generateClassName($refTable);

					// Add relation for this table
					$relationName = $this->generateRelationName($tableName, $fkName, false);
					$relations[$className][$relationName] = "array(self::BELONGS_TO, '$refClassName', '$fkName')";

					// Add relation for the referenced table
					$relationType = $table->primaryKey === $fkName ? 'HAS_ONE' : 'HAS_MANY';
					$relationName = $this->generateRelationName($refTable, $this->removePrefix($tableName, false), $relationType === 'HAS_MANY');
					$i = 1;
					$rawName = $relationName;
					while (isset($relations[$refClassName][$relationName])) {
						$relationName = $rawName . ($i++);
					}
					$relations[$refClassName][$relationName] = "array(self::$relationType, '$className', '$fkName')";
				}
			}
		}
		return $relations;
	}

	/**
	 * Checks if the given table is a "many to many" pivot table.
	 * Their PK has 2 fields, and both of those fields are also FK to other separate tables.
	 * @param CDbTableSchema table to inspect
	 * @return boolean true if table matches description of helpter table.
	 */
	protected function isRelationTable($table)
	{
		$pk = $table->primaryKey;
		return (count($pk) === 2 // we want 2 columns
			&& isset($table->foreignKeys[$pk[0]]) // pk column 1 is also a foreign key
			&& isset($table->foreignKeys[$pk[1]]) // pk column 2 is also a foriegn key
			&& $table->foreignKeys[$pk[0]][0] !== $table->foreignKeys[$pk[1]][0]); // and the foreign keys point different tables
	}

	protected function generateClassName($tableName)
	{
		if ($this->tableName === $tableName || ($pos = strrpos($this->tableName, '.')) !== false && substr($this->tableName, $pos + 1) === $tableName) {
			return $this->modelClass;
		}

		$tableName = $this->removePrefix($tableName, false);
		if (($pos = strpos($tableName, '.')) !== false) // remove schema part (e.g. remove 'public2.' from 'public2.post')
		{
			$tableName = substr($tableName, $pos + 1);
		}
		$className = '';
		foreach (explode('_', $tableName) as $name) {
			if ($name !== '') {
				$className .= ucfirst($name);
			}
		}
		return $className;
	}

	/**
	 * Generate a name for use as a relation name (inside relations() function in a model).
	 * @param string the name of the table to hold the relation
	 * @param string the foreign key name
	 * @param boolean whether the relation would contain multiple objects
	 * @return string the relation name
	 */
	protected function generateRelationName($tableName, $fkName, $multiple)
	{
		if (strcasecmp(substr($fkName, -2), 'id') === 0 && strcasecmp($fkName, 'id')) {
			$relationName = rtrim(substr($fkName, 0, -2), '_');
		} else {
			$relationName = $fkName;
		}
		$relationName[0] = strtolower($relationName);

		if ($multiple) {
			$relationName = $this->pluralize($relationName);
		}

		$names = preg_split('/_+/', $relationName, -1, PREG_SPLIT_NO_EMPTY);
		if (empty($names)) {
			return $relationName;
		} // unlikely
		for ($name = $names[0], $i = 1; $i < count($names); ++$i) {
			$name .= ucfirst($names[$i]);
		}

		$rawName = $name;
		$table = Yii::$app->{$this->connectionId}->schema->getTable($tableName);
		$i = 0;
		while (isset($table->columns[$name])) {
			$name = $rawName . ($i++);
		}

		return $name;
	}

	public function validateDb()
	{
		if (Yii::$app->hasComponent($this->db) === false || !(Yii::$app->getComponent($this->db) instanceof Connection)) {
			$this->addError('db', 'Database Connection ID must refer to a valid application component.');
		}
	}

	public function validateNamespace()
	{
		$path = Yii::getAlias('@' . ltrim($this->ns, '\\'), false);
		if ($path === false) {
			$this->addError('ns', 'Namespace must be associated with an existing directory.');
		}
	}

	public function validateModelClass()
	{
		if ($this->isReservedKeyword($this->modelClass)) {
			$this->addError('modelClass', 'Class name cannot be a reserved PHP keyword.');
		}
		if (strpos($this->tableName, '*') === false && $this->modelClass == '') {
			$this->addError('modelClass', 'Model Class cannot be blank.');
		}
	}

	public function validateTableName()
	{
		$tables = $this->getTableNames();
		if (empty($tables)) {
			$this->addError('tableName', "Table '{$this->tableName}' does not exist.'");
		} else {
			foreach ($tables as $table) {
				$class = $this->generateClassName($table);
				if ($this->isReservedKeyword($class)) {
					$this->addError('tableName', "Table '$table' would generate a class which is a reserved PHP keyword.");
					break;
				}
			}
		}
	}

	protected function getTableNames()
	{
		$db = $this->getDbConnection();
		$tableNames = array();
		if ($this->tableName[strlen($this->tableName) - 1] === '*') {
			if (($pos = strrpos($this->tableName, '.')) !== false) {
				$schema = substr($this->tableName, 0, $pos);
				$pattern = '/' . str_replace('*', '\w+', substr($this->tableName, $pos + 1)) . '/';
			} else {
				$schema = '';
				$pattern = '/' . str_replace('*', '\w+', $this->tableName) . '/';
			}

			foreach ($db->schema->getTableNames($schema) as $table) {
				if (preg_match($pattern, $table)) {
					$tableNames[] = $schema === '' ? $table : ($schema . '.' . $table);
				}
			}
		} elseif (($table = $db->getTableSchema($this->tableName, true)) !== null) {
			$tableNames[] = $this->tableName;
		}
		return $tableNames;
	}
}
