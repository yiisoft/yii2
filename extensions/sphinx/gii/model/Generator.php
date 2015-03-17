<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\sphinx\gii\model;

use Yii;
use yii\sphinx\ActiveRecord;
use yii\sphinx\Connection;
use yii\sphinx\Schema;
use yii\gii\CodeFile;
use yii\helpers\Inflector;

/**
 * This generator will generate one or multiple ActiveRecord classes for the specified Sphinx index.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{
    public $db = 'sphinx';
    public $ns = 'app\models';
    public $indexName;
    public $modelClass;
    public $baseClass = 'yii\sphinx\ActiveRecord';
    public $useIndexPrefix = false;


    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Sphinx Model Generator';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator generates an ActiveRecord class for the specified Sphinx index.';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['db', 'ns', 'indexName', 'modelClass', 'baseClass'], 'filter', 'filter' => 'trim'],
            [['ns'], 'filter', 'filter' => function($value) { return trim($value, '\\'); }],

            [['db', 'ns', 'indexName', 'baseClass'], 'required'],
            [['db', 'modelClass'], 'match', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'],
            [['ns', 'baseClass'], 'match', 'pattern' => '/^[\w\\\\]+$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['indexName'], 'match', 'pattern' => '/^(\w+\.)?([\w\*]+)$/', 'message' => 'Only word characters, and optionally an asterisk and/or a dot are allowed.'],
            [['db'], 'validateDb'],
            [['ns'], 'validateNamespace'],
            [['indexName'], 'validateIndexName'],
            [['modelClass'], 'validateModelClass', 'skipOnEmpty' => false],
            [['baseClass'], 'validateClass', 'params' => ['extends' => ActiveRecord::className()]],
            [['enableI18N'], 'boolean'],
            [['useIndexPrefix'], 'boolean'],
            [['messageCategory'], 'validateMessageCategory', 'skipOnEmpty' => false],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'ns' => 'Namespace',
            'db' => 'Sphinx Connection ID',
            'indexName' => 'Index Name',
            'modelClass' => 'Model Class',
            'baseClass' => 'Base Class',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return array_merge(parent::hints(), [
            'ns' => 'This is the namespace of the ActiveRecord class to be generated, e.g., <code>app\models</code>',
            'db' => 'This is the ID of the Sphinx application component.',
            'indexName' => 'This is the name of the Sphinx index that the new ActiveRecord class is associated with, e.g. <code>post</code>.
                The index name may end with asterisk to match multiple table names, e.g. <code>idx_*</code>
                will match indexes, which name starts with <code>idx_</code>. In this case, multiple ActiveRecord classes
                will be generated, one for each matching index name; and the class names will be generated from
                the matching characters. For example, index <code>idx_post</code> will generate <code>Post</code>
                class.',
            'modelClass' => 'This is the name of the ActiveRecord class to be generated. The class name should not contain
                the namespace part as it is specified in "Namespace". You do not need to specify the class name
                if "Index Name" ends with asterisk, in which case multiple ActiveRecord classes will be generated.',
            'baseClass' => 'This is the base class of the new ActiveRecord class. It should be a fully qualified namespaced class name.',
            'useIndexPrefix' => 'This indicates whether the index name returned by the generated ActiveRecord class
                should consider the <code>tablePrefix</code> setting of the Sphinx connection. For example, if the
                index name is <code>idx_post</code> and <code>tablePrefix=idx_</code>, the ActiveRecord class
                will return the table name as <code>{{%post}}</code>.',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function autoCompleteData()
    {
        $db = $this->getDbConnection();
        if ($db !== null) {
            return [
                'indexName' => function () use ($db) {
                    return $db->getSchema()->getIndexNames();
                },
            ];
        } else {
            return [];
        }
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['model.php'];
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), ['ns', 'db', 'baseClass']);
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $files = [];
        $db = $this->getDbConnection();
        foreach ($this->getIndexNames() as $indexName) {
            $className = $this->generateClassName($indexName);
            $indexSchema = $db->getIndexSchema($indexName);
            $params = [
                'indexName' => $indexName,
                'className' => $className,
                'indexSchema' => $indexSchema,
                'labels' => $this->generateLabels($indexSchema),
                'rules' => $this->generateRules($indexSchema),
            ];
            $files[] = new CodeFile(
                Yii::getAlias('@' . str_replace('\\', '/', $this->ns)) . '/' . $className . '.php',
                $this->render('model.php', $params)
            );
        }

        return $files;
    }

    /**
     * Generates the attribute labels for the specified table.
     * @param \yii\db\TableSchema $table the table schema
     * @return array the generated attribute labels (name => label)
     */
    public function generateLabels($table)
    {
        $labels = [];
        foreach ($table->columns as $column) {
            if (!strcasecmp($column->name, 'id')) {
                $labels[$column->name] = 'ID';
            } else {
                $label = Inflector::camel2words($column->name);
                if (substr_compare($label, ' id', -3, 3, true) === 0) {
                    $label = substr($label, 0, -3) . ' ID';
                }
                $labels[$column->name] = $label;
            }
        }

        return $labels;
    }

    /**
     * Generates validation rules for the specified index.
     * @param \yii\sphinx\IndexSchema $index the index schema
     * @return array the generated validation rules
     */
    public function generateRules($index)
    {
        $types = [];
        foreach ($index->columns as $column) {
            if ($column->isMva) {
                $types['safe'][] = $column->name;
                continue;
            }
            if ($column->isPrimaryKey) {
                $types['required'][] = $column->name;
                $types['unique'][] = $column->name;
            }
            switch ($column->type) {
                case Schema::TYPE_PK:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                    $types['integer'][] = $column->name;
                    break;
                case Schema::TYPE_BOOLEAN:
                    $types['boolean'][] = $column->name;
                    break;
                case Schema::TYPE_FLOAT:
                    $types['number'][] = $column->name;
                    break;
                case Schema::TYPE_TIMESTAMP:
                    $types['safe'][] = $column->name;
                    break;
                default: // strings
                    $types['string'][] = $column->name;
            }
        }
        $rules = [];
        foreach ($types as $type => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
        }

        return $rules;
    }

    /**
     * Validates the [[db]] attribute.
     */
    public function validateDb()
    {
        if (!Yii::$app->has($this->db)) {
            $this->addError('db', 'There is no application component named "' . $this->db . '".');
        } elseif (!Yii::$app->get($this->db) instanceof Connection) {
            $this->addError('db', 'The "' . $this->db . '" application component must be a Sphinx connection instance.');
        }
    }

    /**
     * Validates the [[ns]] attribute.
     */
    public function validateNamespace()
    {
        $this->ns = ltrim($this->ns, '\\');
        $path = Yii::getAlias('@' . str_replace('\\', '/', $this->ns), false);
        if ($path === false) {
            $this->addError('ns', 'Namespace must be associated with an existing directory.');
        }
    }

    /**
     * Validates the [[modelClass]] attribute.
     */
    public function validateModelClass()
    {
        if ($this->isReservedKeyword($this->modelClass)) {
            $this->addError('modelClass', 'Class name cannot be a reserved PHP keyword.');
        }
        if ((empty($this->indexName) || substr_compare($this->indexName, '*', -1, 1)) && $this->modelClass == '') {
            $this->addError('modelClass', 'Model Class cannot be blank if table name does not end with asterisk.');
        }
    }

    /**
     * Validates the [[indexName]] attribute.
     */
    public function validateIndexName()
    {
        if (strpos($this->indexName, '*') !== false && substr_compare($this->indexName, '*', -1, 1)) {
            $this->addError('indexName', 'Asterisk is only allowed as the last character.');

            return;
        }
        $tables = $this->getIndexNames();
        if (empty($tables)) {
            $this->addError('indexName', "Table '{$this->indexName}' does not exist.");
        } else {
            foreach ($tables as $table) {
                $class = $this->generateClassName($table);
                if ($this->isReservedKeyword($class)) {
                    $this->addError('indexName', "Table '$table' will generate a class which is a reserved PHP keyword.");
                    break;
                }
            }
        }
    }

    private $_indexNames;
    private $_classNames;

    /**
     * @return array the index names that match the pattern specified by [[indexName]].
     */
    protected function getIndexNames()
    {
        if ($this->_indexNames !== null) {
            return $this->_indexNames;
        }
        $db = $this->getDbConnection();
        if ($db === null) {
            return [];
        }
        $indexNames = [];
        if (strpos($this->indexName, '*') !== false) {
            $indexNames = $db->getSchema()->getIndexNames();
        } elseif (($index = $db->getIndexSchema($this->indexName, true)) !== null) {
            $indexNames[] = $this->indexName;
            $this->_classNames[$this->indexName] = $this->modelClass;
        }

        return $this->_indexNames = $indexNames;
    }

    /**
     * Generates the table name by considering table prefix.
     * If [[useIndexPrefix]] is false, the table name will be returned without change.
     * @param string $indexName the table name (which may contain schema prefix)
     * @return string the generated table name
     */
    public function generateIndexName($indexName)
    {
        if (!$this->useIndexPrefix) {
            return $indexName;
        }

        $db = $this->getDbConnection();
        if (preg_match("/^{$db->tablePrefix}(.*?)$/", $indexName, $matches)) {
            $indexName = '{{%' . $matches[1] . '}}';
        } elseif (preg_match("/^(.*?){$db->tablePrefix}$/", $indexName, $matches)) {
            $indexName = '{{' . $matches[1] . '%}}';
        }
        return $indexName;
    }

    /**
     * Generates a class name from the specified table name.
     * @param string $indexName the table name (which may contain schema prefix)
     * @return string the generated class name
     */
    protected function generateClassName($indexName)
    {
        if (isset($this->_classNames[$indexName])) {
            return $this->_classNames[$indexName];
        }

        if (($pos = strrpos($indexName, '.')) !== false) {
            $indexName = substr($indexName, $pos + 1);
        }

        $db = $this->getDbConnection();
        $patterns = [];
        $patterns[] = "/^{$db->tablePrefix}(.*?)$/";
        $patterns[] = "/^(.*?){$db->tablePrefix}$/";
        if (strpos($this->indexName, '*') !== false) {
            $pattern = $this->indexName;
            if (($pos = strrpos($pattern, '.')) !== false) {
                $pattern = substr($pattern, $pos + 1);
            }
            $patterns[] = '/^' . str_replace('*', '(\w+)', $pattern) . '$/';
        }
        $className = $indexName;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $indexName, $matches)) {
                $className = $matches[1];
                break;
            }
        }

        return $this->_classNames[$indexName] = Inflector::id2camel($className, '_');
    }

    /**
     * @return Connection the Sphinx connection as specified by [[db]].
     */
    protected function getDbConnection()
    {
        return Yii::$app->get($this->db, false);
    }
}
