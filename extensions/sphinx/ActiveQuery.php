<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\sphinx;

use yii\base\InvalidCallException;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveQueryTrait;
use yii\db\ActiveRelationTrait;

/**
 * ActiveQuery represents a Sphinx query associated with an Active Record class.
 *
 * An ActiveQuery can be a normal query or be used in a relational context.
 *
 * ActiveQuery instances are usually created by [[ActiveRecord::find()]] and [[ActiveRecord::findBySql()]].
 * Relational queries are created by [[ActiveRecord::hasOne()]] and [[ActiveRecord::hasMany()]].
 *
 * Normal Query
 * ------------
 *
 * Because ActiveQuery extends from [[Query]], one can use query methods, such as [[where()]],
 * [[orderBy()]] to customize the query options.
 *
 * ActiveQuery also provides the following additional query options:
 *
 * - [[with()]]: list of relations that this query should be performed with.
 * - [[indexBy()]]: the name of the column by which the query result should be indexed.
 * - [[asArray()]]: whether to return each record as an array.
 *
 * These options can be configured using methods of the same name. For example:
 *
 * ~~~
 * $articles = Article::find()->with('source')->asArray()->all();
 * ~~~
 *
 * ActiveQuery allows to build the snippets using sources provided by ActiveRecord.
 * You can use [[snippetByModel()]] method to enable this.
 * For example:
 *
 * ~~~
 * class Article extends ActiveRecord
 * {
 *     public function getSource()
 *     {
 *         return $this->hasOne('db', ArticleDb::className(), ['id' => 'id']);
 *     }
 *
 *     public function getSnippetSource()
 *     {
 *         return $this->source->content;
 *     }
 *
 *     ...
 * }
 *
 * $articles = Article::find()->with('source')->snippetByModel()->all();
 * ~~~
 *
 * Relational query
 * ----------------
 *
 * In relational context ActiveQuery represents a relation between two Active Record classes.
 *
 * Relational ActiveQuery instances are usually created by calling [[ActiveRecord::hasOne()]] and
 * [[ActiveRecord::hasMany()]]. An Active Record class declares a relation by defining
 * a getter method which calls one of the above methods and returns the created ActiveQuery object.
 *
 * A relation is specified by [[link]] which represents the association between columns
 * of different tables; and the multiplicity of the relation is indicated by [[multiple]].
 *
 * If a relation involves a pivot table, it may be specified by [[via()]].
 * This methods may only be called in a relational context. Same is true for [[inverseOf()]], which
 * marks a relation as inverse of another relation.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class ActiveQuery extends Query implements ActiveQueryInterface
{
    use ActiveQueryTrait;
    use ActiveRelationTrait;

    /**
     * @event Event an event that is triggered when the query is initialized via [[init()]].
     */
    const EVENT_INIT = 'init';

    /**
     * @var string the SQL statement to be executed for retrieving AR records.
     * This is set by [[ActiveRecord::findBySql()]].
     */
    public $sql;


    /**
     * Constructor.
     * @param array $modelClass the model class associated with this query
     * @param array $config configurations to be applied to the newly created query object
     */
    public function __construct($modelClass, $config = [])
    {
        $this->modelClass = $modelClass;
        parent::__construct($config);
    }

    /**
     * Initializes the object.
     * This method is called at the end of the constructor. The default implementation will trigger
     * an [[EVENT_INIT]] event. If you override this method, make sure you call the parent implementation at the end
     * to ensure triggering of the event.
     */
    public function init()
    {
        parent::init();
        $this->trigger(self::EVENT_INIT);
    }

    /**
     * Sets the [[snippetCallback]] to [[fetchSnippetSourceFromModels()]], which allows to
     * fetch the snippet source strings from the Active Record models, using method
     * [[ActiveRecord::getSnippetSource()]].
     * For example:
     *
     * ~~~
     * class Article extends ActiveRecord
     * {
     *     public function getSnippetSource()
     *     {
     *         return file_get_contents('/path/to/source/files/' . $this->id . '.txt');;
     *     }
     * }
     *
     * $articles = Article::find()->snippetByModel()->all();
     * ~~~
     *
     * Warning: this option should NOT be used with [[asArray]] at the same time!
     * @return static the query object itself
     */
    public function snippetByModel()
    {
        $this->snippetCallback([$this, 'fetchSnippetSourceFromModels']);

        return $this;
    }

    /**
     * Executes query and returns all results as an array.
     * @param Connection $db the DB connection used to create the DB command.
     * If null, the DB connection returned by [[modelClass]] will be used.
     * @return array the query results. If the query results in nothing, an empty array will be returned.
     */
    public function all($db = null)
    {
        $command = $this->createCommand($db);
        $rows = $command->queryAll();
        if (!empty($rows)) {
            $models = $this->createModels($rows);
            if (!empty($this->with)) {
                $this->findWith($this->with, $models);
            }
            $models = $this->fillUpSnippets($models);
            if (!$this->asArray) {
                foreach ($models as $model) {
                    $model->afterFind();
                }
            }

            return $models;
        } else {
            return [];
        }
    }

    /**
     * Executes query and returns a single row of result.
     * @param Connection $db the DB connection used to create the DB command.
     * If null, the DB connection returned by [[modelClass]] will be used.
     * @return ActiveRecord|array|null a single row of query result. Depending on the setting of [[asArray]],
     * the query result may be either an array or an ActiveRecord object. Null will be returned
     * if the query results in nothing.
     */
    public function one($db = null)
    {
        $command = $this->createCommand($db);
        $row = $command->queryOne();
        if ($row !== false) {
            if ($this->asArray) {
                $model = $row;
            } else {
                /* @var $class ActiveRecord */
                $class = $this->modelClass;
                $model = $class::instantiate($row);
                $class::populateRecord($model, $row);
            }
            if (!empty($this->with)) {
                $models = [$model];
                $this->findWith($this->with, $models);
                $model = $models[0];
            }
            list ($model) = $this->fillUpSnippets([$model]);
            if (!$this->asArray) {
                $model->afterFind();
            }

            return $model;
        } else {
            return null;
        }
    }

    /**
     * Creates a DB command that can be used to execute this query.
     * @param Connection $db the DB connection used to create the DB command.
     * If null, the DB connection returned by [[modelClass]] will be used.
     * @return Command the created DB command instance.
     */
    public function createCommand($db = null)
    {
        if ($this->primaryModel !== null) {
            // lazy loading a relational query
            if ($this->via instanceof self) {
                // via pivot index
                $viaModels = $this->via->findPivotRows([$this->primaryModel]);
                $this->filterByModels($viaModels);
            } elseif (is_array($this->via)) {
                // via relation
                /* @var $viaQuery ActiveQuery */
                list($viaName, $viaQuery) = $this->via;
                if ($viaQuery->multiple) {
                    $viaModels = $viaQuery->all();
                    $this->primaryModel->populateRelation($viaName, $viaModels);
                } else {
                    $model = $viaQuery->one();
                    $this->primaryModel->populateRelation($viaName, $model);
                    $viaModels = $model === null ? [] : [$model];
                }
                $this->filterByModels($viaModels);
            } else {
                $this->filterByModels([$this->primaryModel]);
            }
        }

        $this->setConnection($db);
        $db = $this->getConnection();

        if ($this->sql === null) {
            list ($sql, $params) = $db->getQueryBuilder()->build($this);
        } else {
            $sql = $this->sql;
            $params = $this->params;
        }

        return $db->createCommand($sql, $params);
    }

    /**
     * @inheritdoc
     */
    protected function defaultConnection()
    {
        $modelClass = $this->modelClass;

        return $modelClass::getDb();
    }

    /**
     * Fetches the source for the snippets using [[ActiveRecord::getSnippetSource()]] method.
     * @param ActiveRecord[] $models raw query result rows.
     * @throws \yii\base\InvalidCallException if [[asArray]] enabled.
     * @return array snippet source strings
     */
    protected function fetchSnippetSourceFromModels($models)
    {
        if ($this->asArray) {
            throw new InvalidCallException('"' . __METHOD__ . '" unable to determine snippet source from plain array. Either disable "asArray" option or use regular "snippetCallback"');
        }
        $result = [];
        foreach ($models as $model) {
            $result[] = $model->getSnippetSource();
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function callSnippets(array $source)
    {
        $from = $this->from;
        if ($from === null) {
            /* @var $modelClass ActiveRecord */
            $modelClass = $this->modelClass;
            $tableName = $modelClass::indexName();
            $from = [$tableName];
        }

        return $this->callSnippetsInternal($source, $from[0]);
    }
}
