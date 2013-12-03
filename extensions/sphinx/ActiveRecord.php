<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\sphinx;

use yii\base\InvalidConfigException;
use yii\db\BaseActiveRecord;
use yii\base\NotSupportedException;
use yii\db\ActiveRelationInterface;
use yii\db\StaleObjectException;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use Yii;

/**
 * ActiveRecord is the base class for classes representing relational data in terms of objects.
 *
 * Warning: optimistic lock will NOT work in case of updating fields (not attributes) for the
 * runtime indexes!
 *
 * @property array $dirtyAttributes The changed attribute values (name-value pairs). This property is
 * read-only.
 * @property boolean $isNewRecord Whether the record is new and should be inserted when calling [[save()]].
 * @property array $oldAttributes The old attribute values (name-value pairs).
 * @property mixed $oldPrimaryKey The old primary key value. An array (column name => column value) is
 * returned if the primary key is composite. A string is returned otherwise (null will be returned if the key
 * value is null). This property is read-only.
 * @property array $populatedRelations An array of relation data indexed by relation names. This property is
 * read-only.
 * @property mixed $primaryKey The primary key value. An array (column name => column value) is returned if
 * the primary key is composite. A string is returned otherwise (null will be returned if the key value is null).
 * This property is read-only.
 * @property string $snippet Snippet value.
 * @property string $snippetSource Snippet source string. This property is read-only.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
abstract class ActiveRecord extends BaseActiveRecord
{
	/**
	 * The insert operation. This is mainly used when overriding [[transactions()]] to specify which operations are transactional.
	 */
	const OP_INSERT = 0x01;
	/**
	 * The update operation. This is mainly used when overriding [[transactions()]] to specify which operations are transactional.
	 */
	const OP_UPDATE = 0x02;
	/**
	 * The delete operation. This is mainly used when overriding [[transactions()]] to specify which operations are transactional.
	 */
	const OP_DELETE = 0x04;
	/**
	 * All three operations: insert, update, delete.
	 * This is a shortcut of the expression: OP_INSERT | OP_UPDATE | OP_DELETE.
	 */
	const OP_ALL = 0x07;

	/**
	 * @var string current snippet value for this Active Record instance.
	 * It will be filled up automatically when instance found using [[Query::snippetCallback]]
	 * or [[ActiveQuery::snippetByModel()]].
	 */
	private $_snippet;

	/**
	 * Returns the Sphinx connection used by this AR class.
	 * By default, the "sphinx" application component is used as the Sphinx connection.
	 * You may override this method if you want to use a different Sphinx connection.
	 * @return Connection the Sphinx connection used by this AR class.
	 */
	public static function getDb()
	{
		return \Yii::$app->getComponent('sphinx');
	}

	/**
	 * Creates an [[ActiveQuery]] instance for query purpose.
	 *
	 * @param mixed $q the query parameter. This can be one of the followings:
	 *
	 *  - a string: fulltext query by a query string and return the list
	 *    of matching records.
	 *  - an array of name-value pairs: query by a set of column values and return a single record matching all of them.
	 *  - null: return a new [[ActiveQuery]] object for further query purpose.
	 *
	 * @return ActiveQuery|ActiveRecord[]|ActiveRecord|null When `$q` is null, a new [[ActiveQuery]] instance
	 * is returned; when `$q` is a string, an array of ActiveRecord objects matching it will be returned;
	 * when `$q` is an array, an ActiveRecord object matching it will be returned (null
	 * will be returned if there is no matching).
	 * @see createQuery()
	 */
	public static function find($q = null)
	{
		$query = static::createQuery();
		if (is_array($q)) {
			return $query->where($q)->one();
		} elseif ($q !== null) {
			return $query->match($q)->all();
		}
		return $query;
	}

	/**
	 * Creates an [[ActiveQuery]] instance with a given SQL statement.
	 *
	 * Note that because the SQL statement is already specified, calling additional
	 * query modification methods (such as `where()`, `order()`) on the created [[ActiveQuery]]
	 * instance will have no effect. However, calling `with()`, `asArray()` or `indexBy()` is
	 * still fine.
	 *
	 * Below is an example:
	 *
	 * ~~~
	 * $customers = Article::findBySql("SELECT * FROM `idx_article` WHERE MATCH('development')")->all();
	 * ~~~
	 *
	 * @param string $sql the SQL statement to be executed
	 * @param array $params parameters to be bound to the SQL statement during execution.
	 * @return ActiveQuery the newly created [[ActiveQuery]] instance
	 */
	public static function findBySql($sql, $params = [])
	{
		$query = static::createQuery();
		$query->sql = $sql;
		return $query->params($params);
	}

	/**
	 * Updates the whole table using the provided attribute values and conditions.
	 * For example, to change the status to be 1 for all articles which status is 2:
	 *
	 * ~~~
	 * Article::updateAll(['status' => 1], 'status = 2');
	 * ~~~
	 *
	 * @param array $attributes attribute values (name-value pairs) to be saved into the table
	 * @param string|array $condition the conditions that will be put in the WHERE part of the UPDATE SQL.
	 * Please refer to [[Query::where()]] on how to specify this parameter.
	 * @param array $params the parameters (name => value) to be bound to the query.
	 * @return integer the number of rows updated
	 */
	public static function updateAll($attributes, $condition = '', $params = [])
	{
		$command = static::getDb()->createCommand();
		$command->update(static::indexName(), $attributes, $condition, $params);
		return $command->execute();
	}

	/**
	 * Deletes rows in the index using the provided conditions.
	 *
	 * For example, to delete all articles whose status is 3:
	 *
	 * ~~~
	 * Article::deleteAll('status = 3');
	 * ~~~
	 *
	 * @param string|array $condition the conditions that will be put in the WHERE part of the DELETE SQL.
	 * Please refer to [[Query::where()]] on how to specify this parameter.
	 * @param array $params the parameters (name => value) to be bound to the query.
	 * @return integer the number of rows deleted
	 */
	public static function deleteAll($condition = '', $params = [])
	{
		$command = static::getDb()->createCommand();
		$command->delete(static::indexName(), $condition, $params);
		return $command->execute();
	}

	/**
	 * Creates an [[ActiveQuery]] instance.
	 * This method is called by [[find()]], [[findBySql()]] and [[count()]] to start a SELECT query.
	 * You may override this method to return a customized query (e.g. `ArticleQuery` specified
	 * written for querying `Article` purpose.)
	 * @return ActiveQuery the newly created [[ActiveQuery]] instance.
	 */
	public static function createQuery()
	{
		return new ActiveQuery(['modelClass' => get_called_class()]);
	}

	/**
	 * Declares the name of the Sphinx index associated with this AR class.
	 * By default this method returns the class name as the index name by calling [[Inflector::camel2id()]].
	 * For example, 'Article' becomes 'article', and 'StockItem' becomes
	 * 'stock_item'. You may override this method if the index is not named after this convention.
	 * @return string the index name
	 */
	public static function indexName()
	{
		return Inflector::camel2id(StringHelper::basename(get_called_class()), '_');
	}

	/**
	 * Returns the schema information of the Sphinx index associated with this AR class.
	 * @return IndexSchema the schema information of the Sphinx index associated with this AR class.
	 * @throws InvalidConfigException if the index for the AR class does not exist.
	 */
	public static function getIndexSchema()
	{
		$schema = static::getDb()->getIndexSchema(static::indexName());
		if ($schema !== null) {
			return $schema;
		} else {
			throw new InvalidConfigException("The index does not exist: " . static::indexName());
		}
	}

	/**
	 * Returns the primary key name for this AR class.
	 * The default implementation will return the primary key as declared
	 * in the Sphinx index, which is associated with this AR class.
	 *
	 * Note that an array should be returned even for a table with single primary key.
	 *
	 * @return string[] the primary keys of the associated Sphinx index.
	 */
	public static function primaryKey()
	{
		return [static::getIndexSchema()->primaryKey];
	}

	/**
	 * Builds a snippet from provided data and query, using specified index settings.
	 * @param string|array $source is the source data to extract a snippet from.
	 * It could be either a single string or array of strings.
	 * @param string $match the full-text query to build snippets for.
	 * @param array $options list of options in format: optionName => optionValue
	 * @return string|array built snippet in case "source" is a string, list of built snippets
	 * in case "source" is an array.
	 */
	public static function callSnippets($source, $match, $options = [])
	{
		$command = static::getDb()->createCommand();
		$command->callSnippets(static::indexName(), $source, $match, $options);
		if (is_array($source)) {
			return $command->queryColumn();
		} else {
			return $command->queryScalar();
		}
	}

	/**
	 * Returns tokenized and normalized forms of the keywords, and, optionally, keyword statistics.
	 * @param string $text the text to break down to keywords.
	 * @param boolean $fetchStatistic whether to return document and hit occurrence statistics
	 * @return array keywords and statistics
	 */
	public static function callKeywords($text, $fetchStatistic = false)
	{
		$command = static::getDb()->createCommand();
		$command->callKeywords(static::indexName(), $text, $fetchStatistic);
		return $command->queryAll();
	}

	/**
	 * @param string $snippet
	 */
	public function setSnippet($snippet)
	{
		$this->_snippet = $snippet;
	}

	/**
	 * Returns current snippet value or generates new one from given match.
	 * @param string $match snippet source query
	 * @param array $options list of options in format: optionName => optionValue
	 * @return string snippet value
	 */
	public function getSnippet($match = null, $options = [])
	{
		if ($match !== null) {
			$this->_snippet = $this->fetchSnippet($match, $options);
		}
		return $this->_snippet;
	}

	/**
	 * Builds up the snippet value from the given query.
	 * @param string $match the full-text query to build snippets for.
	 * @param array $options list of options in format: optionName => optionValue
	 * @return string snippet value.
	 */
	protected function fetchSnippet($match, $options = [])
	{
		return static::callSnippets($this->getSnippetSource(), $match, $options);
	}

	/**
	 * Returns the string, which should be used as a source to create snippet for this
	 * Active Record instance.
	 * Child classes must implement this method to return the actual snippet source text.
	 * For example:
	 * ~~~
	 * public function getSnippetSource()
	 * {
	 *     return $this->snippetSourceRelation->content;
	 * }
	 * ~~~
	 * @return string snippet source string.
	 * @throws \yii\base\NotSupportedException if this is not supported by the Active Record class
	 */
	public function getSnippetSource()
	{
		throw new NotSupportedException($this->className() . ' does not provide snippet source.');
	}

	/**
	 * Declares which operations should be performed within a transaction in different scenarios.
	 * The supported DB operations are: [[OP_INSERT]], [[OP_UPDATE]] and [[OP_DELETE]],
	 * which correspond to the [[insert()]], [[update()]] and [[delete()]] methods, respectively.
	 * By default, these methods are NOT enclosed in a transaction.
	 *
	 * In some scenarios, to ensure data consistency, you may want to enclose some or all of them
	 * in transactions. You can do so by overriding this method and returning the operations
	 * that need to be transactional. For example,
	 *
	 * ~~~
	 * return [
	 *     'admin' => self::OP_INSERT,
	 *     'api' => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
	 *     // the above is equivalent to the following:
	 *     // 'api' => self::OP_ALL,
	 *
	 * ];
	 * ~~~
	 *
	 * The above declaration specifies that in the "admin" scenario, the insert operation ([[insert()]])
	 * should be done in a transaction; and in the "api" scenario, all the operations should be done
	 * in a transaction.
	 *
	 * @return array the declarations of transactional operations. The array keys are scenarios names,
	 * and the array values are the corresponding transaction operations.
	 */
	public function transactions()
	{
		return [];
	}

	/**
	 * Creates an [[ActiveRelationInterface]] instance.
	 * This method is called by [[hasOne()]] and [[hasMany()]] to create a relation instance.
	 * You may override this method to return a customized relation.
	 * @param array $config the configuration passed to the ActiveRelation class.
	 * @return ActiveRelationInterface the newly created [[ActiveRelation]] instance.
	 */
	public static function createActiveRelation($config = [])
	{
		return new ActiveRelation($config);
	}

	/**
	 * Returns the list of all attribute names of the model.
	 * The default implementation will return all column names of the table associated with this AR class.
	 * @return array list of attribute names.
	 */
	public function attributes()
	{
		return array_keys(static::getIndexSchema()->columns);
	}

	/**
	 * Inserts a row into the associated Sphinx index using the attribute values of this record.
	 *
	 * This method performs the following steps in order:
	 *
	 * 1. call [[beforeValidate()]] when `$runValidation` is true. If validation
	 *    fails, it will skip the rest of the steps;
	 * 2. call [[afterValidate()]] when `$runValidation` is true.
	 * 3. call [[beforeSave()]]. If the method returns false, it will skip the
	 *    rest of the steps;
	 * 4. insert the record into index. If this fails, it will skip the rest of the steps;
	 * 5. call [[afterSave()]];
	 *
	 * In the above step 1, 2, 3 and 5, events [[EVENT_BEFORE_VALIDATE]],
	 * [[EVENT_BEFORE_INSERT]], [[EVENT_AFTER_INSERT]] and [[EVENT_AFTER_VALIDATE]]
	 * will be raised by the corresponding methods.
	 *
	 * Only the [[changedAttributes|changed attribute values]] will be inserted.
	 *
	 * For example, to insert an article record:
	 *
	 * ~~~
	 * $article = new Article;
	 * $article->id = $id;
	 * $article->genre_id = $genreId;
	 * $article->content = $content;
	 * $article->insert();
	 * ~~~
	 *
	 * @param boolean $runValidation whether to perform validation before saving the record.
	 * If the validation fails, the record will not be inserted.
	 * @param array $attributes list of attributes that need to be saved. Defaults to null,
	 * meaning all attributes that are loaded from index will be saved.
	 * @return boolean whether the attributes are valid and the record is inserted successfully.
	 * @throws \Exception in case insert failed.
	 */
	public function insert($runValidation = true, $attributes = null)
	{
		if ($runValidation && !$this->validate($attributes)) {
			return false;
		}
		$db = static::getDb();
		if ($this->isTransactional(self::OP_INSERT) && $db->getTransaction() === null) {
			$transaction = $db->beginTransaction();
			try {
				$result = $this->insertInternal($attributes);
				if ($result === false) {
					$transaction->rollback();
				} else {
					$transaction->commit();
				}
			} catch (\Exception $e) {
				$transaction->rollback();
				throw $e;
			}
		} else {
			$result = $this->insertInternal($attributes);
		}
		return $result;
	}

	/**
	 * @see ActiveRecord::insert()
	 */
	private function insertInternal($attributes = null)
	{
		if (!$this->beforeSave(true)) {
			return false;
		}
		$values = $this->getDirtyAttributes($attributes);
		if (empty($values)) {
			foreach ($this->getPrimaryKey(true) as $key => $value) {
				$values[$key] = $value;
			}
		}
		$db = static::getDb();
		$command = $db->createCommand()->insert($this->indexName(), $values);
		if (!$command->execute()) {
			return false;
		}
		foreach ($values as $name => $value) {
			$this->setOldAttribute($name, $value);
		}
		$this->afterSave(true);
		return true;
	}

	/**
	 * Saves the changes to this active record into the associated Sphinx index.
	 *
	 * This method performs the following steps in order:
	 *
	 * 1. call [[beforeValidate()]] when `$runValidation` is true. If validation
	 *    fails, it will skip the rest of the steps;
	 * 2. call [[afterValidate()]] when `$runValidation` is true.
	 * 3. call [[beforeSave()]]. If the method returns false, it will skip the
	 *    rest of the steps;
	 * 4. save the record into index. If this fails, it will skip the rest of the steps;
	 * 5. call [[afterSave()]];
	 *
	 * In the above step 1, 2, 3 and 5, events [[EVENT_BEFORE_VALIDATE]],
	 * [[EVENT_BEFORE_UPDATE]], [[EVENT_AFTER_UPDATE]] and [[EVENT_AFTER_VALIDATE]]
	 * will be raised by the corresponding methods.
	 *
	 * Only the [[changedAttributes|changed attribute values]] will be saved into database.
	 *
	 * For example, to update an article record:
	 *
	 * ~~~
	 * $article = Article::find(['id' => $id]);
	 * $article->genre_id = $genreId;
	 * $article->group_id = $groupId;
	 * $article->update();
	 * ~~~
	 *
	 * Note that it is possible the update does not affect any row in the table.
	 * In this case, this method will return 0. For this reason, you should use the following
	 * code to check if update() is successful or not:
	 *
	 * ~~~
	 * if ($this->update() !== false) {
	 *     // update successful
	 * } else {
	 *     // update failed
	 * }
	 * ~~~
	 *
	 * @param boolean $runValidation whether to perform validation before saving the record.
	 * If the validation fails, the record will not be inserted into the database.
	 * @param array $attributes list of attributes that need to be saved. Defaults to null,
	 * meaning all attributes that are loaded from DB will be saved.
	 * @return integer|boolean the number of rows affected, or false if validation fails
	 * or [[beforeSave()]] stops the updating process.
	 * @throws StaleObjectException if [[optimisticLock|optimistic locking]] is enabled and the data
	 * being updated is outdated.
	 * @throws \Exception in case update failed.
	 */
	public function update($runValidation = true, $attributes = null)
	{
		if ($runValidation && !$this->validate($attributes)) {
			return false;
		}
		$db = static::getDb();
		if ($this->isTransactional(self::OP_UPDATE) && $db->getTransaction() === null) {
			$transaction = $db->beginTransaction();
			try {
				$result = $this->updateInternal($attributes);
				if ($result === false) {
					$transaction->rollback();
				} else {
					$transaction->commit();
				}
			} catch (\Exception $e) {
				$transaction->rollback();
				throw $e;
			}
		} else {
			$result = $this->updateInternal($attributes);
		}
		return $result;
	}

	/**
	 * @see CActiveRecord::update()
	 * @throws StaleObjectException
	 */
	protected function updateInternal($attributes = null)
	{
		if (!$this->beforeSave(false)) {
			return false;
		}
		$values = $this->getDirtyAttributes($attributes);
		if (empty($values)) {
			$this->afterSave(false);
			return 0;
		}

		// Replace is supported only by runtime indexes and necessary only for field update
		$useReplace = false;
		$indexSchema = $this->getIndexSchema();
		if ($this->getIndexSchema()->isRuntime) {
			foreach ($values as $name => $value) {
				$columnSchema = $indexSchema->getColumn($name);
				if ($columnSchema->isField) {
					$useReplace = true;
					break;
				}
			}
		}

		if ($useReplace) {
			$values = array_merge($values, $this->getOldPrimaryKey(true));
			$command = static::getDb()->createCommand();
			$command->replace(static::indexName(), $values);
			// We do not check the return value of replace because it's possible
			// that the REPLACE statement doesn't change anything and thus returns 0.
			$rows = $command->execute();
		} else {
			$condition = $this->getOldPrimaryKey(true);
			$lock = $this->optimisticLock();
			if ($lock !== null) {
				if (!isset($values[$lock])) {
					$values[$lock] = $this->$lock + 1;
				}
				$condition[$lock] = $this->$lock;
			}
			// We do not check the return value of updateAll() because it's possible
			// that the UPDATE statement doesn't change anything and thus returns 0.
			$rows = $this->updateAll($values, $condition);

			if ($lock !== null && !$rows) {
				throw new StaleObjectException('The object being updated is outdated.');
			}
		}

		foreach ($values as $name => $value) {
			$this->setOldAttribute($name, $this->getAttribute($name));
		}
		$this->afterSave(false);
		return $rows;
	}

	/**
	 * Deletes the index entry corresponding to this active record.
	 *
	 * This method performs the following steps in order:
	 *
	 * 1. call [[beforeDelete()]]. If the method returns false, it will skip the
	 *    rest of the steps;
	 * 2. delete the record from the index;
	 * 3. call [[afterDelete()]].
	 *
	 * In the above step 1 and 3, events named [[EVENT_BEFORE_DELETE]] and [[EVENT_AFTER_DELETE]]
	 * will be raised by the corresponding methods.
	 *
	 * @return integer|boolean the number of rows deleted, or false if the deletion is unsuccessful for some reason.
	 * Note that it is possible the number of rows deleted is 0, even though the deletion execution is successful.
	 * @throws StaleObjectException if [[optimisticLock|optimistic locking]] is enabled and the data
	 * being deleted is outdated.
	 * @throws \Exception in case delete failed.
	 */
	public function delete()
	{
		$db = static::getDb();
		$transaction = $this->isTransactional(self::OP_DELETE) && $db->getTransaction() === null ? $db->beginTransaction() : null;
		try {
			$result = false;
			if ($this->beforeDelete()) {
				// we do not check the return value of deleteAll() because it's possible
				// the record is already deleted in the database and thus the method will return 0
				$condition = $this->getOldPrimaryKey(true);
				$lock = $this->optimisticLock();
				if ($lock !== null) {
					$condition[$lock] = $this->$lock;
				}
				$result = $this->deleteAll($condition);
				if ($lock !== null && !$result) {
					throw new StaleObjectException('The object being deleted is outdated.');
				}
				$this->setOldAttributes(null);
				$this->afterDelete();
			}
			if ($transaction !== null) {
				if ($result === false) {
					$transaction->rollback();
				} else {
					$transaction->commit();
				}
			}
		} catch (\Exception $e) {
			if ($transaction !== null) {
				$transaction->rollback();
			}
			throw $e;
		}
		return $result;
	}

	/**
	 * Returns a value indicating whether the given active record is the same as the current one.
	 * The comparison is made by comparing the index names and the primary key values of the two active records.
	 * If one of the records [[isNewRecord|is new]] they are also considered not equal.
	 * @param ActiveRecord $record record to compare to
	 * @return boolean whether the two active records refer to the same row in the same index.
	 */
	public function equals($record)
	{
		if ($this->isNewRecord || $record->isNewRecord) {
			return false;
		}
		return $this->indexName() === $record->indexName() && $this->getPrimaryKey() === $record->getPrimaryKey();
	}

	/**
	 * Creates an active record object using a row of data.
	 * This method is called by [[ActiveQuery]] to populate the query results
	 * into Active Records. It is not meant to be used to create new records.
	 * @param array $row attribute values (name => value)
	 * @return ActiveRecord the newly created active record.
	 */
	public static function create($row)
	{
		$record = static::instantiate($row);
		$columns = static::getIndexSchema()->columns;
		foreach ($row as $name => $value) {
			if (isset($columns[$name])) {
				$column = $columns[$name];
				if ($column->isMva) {
					$value = explode(',', $value);
				}
				$record->setAttribute($name, $value);
			} else {
				$record->$name = $value;
			}
		}
		$record->setOldAttributes($record->getAttributes());
		$record->afterFind();
		return $record;
	}

	/**
	 * Returns a value indicating whether the specified operation is transactional in the current [[scenario]].
	 * @param integer $operation the operation to check. Possible values are [[OP_INSERT]], [[OP_UPDATE]] and [[OP_DELETE]].
	 * @return boolean whether the specified operation is transactional in the current [[scenario]].
	 */
	public function isTransactional($operation)
	{
		$scenario = $this->getScenario();
		$transactions = $this->transactions();
		return isset($transactions[$scenario]) && ($transactions[$scenario] & $operation);
	}
}