<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongodb\gii\model;

use Yii;
use yii\mongodb\ActiveRecord;
use yii\mongodb\Connection;
use yii\gii\CodeFile;
use yii\helpers\Inflector;

/**
 * This generator will generate ActiveRecord class for the specified MongoDB collection.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{
    public $db = 'mongodb';
    public $ns = 'app\models';
    public $collectionName;
    public $databaseName;
    public $attributeList;
    public $modelClass;
    public $baseClass = 'yii\mongodb\ActiveRecord';


    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'MongoDB Model Generator';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator generates an ActiveRecord class for the specified MongoDB collection.';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['db', 'ns', 'collectionName', 'databaseName', 'attributeList', 'modelClass', 'baseClass'], 'filter', 'filter' => 'trim'],
            [['ns'], 'filter', 'filter' => function($value) { return trim($value, '\\'); }],

            [['db', 'ns', 'collectionName', 'baseClass'], 'required'],
            [['db', 'modelClass'], 'match', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'],
            [['ns', 'baseClass'], 'match', 'pattern' => '/^[\w\\\\]+$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['collectionName'], 'match', 'pattern' => '/^[^$ ]+$/', 'message' => 'Collection name can not contain spaces or "$" symbols.'],
            [['databaseName'], 'match', 'pattern' => '/^[^\\/\\\\\\. "*:?\\|<>]+$/', 'message' => 'Database name can not contain spaces or any of "/\."*<>:|?" symbols.'],
            [['db'], 'validateDb'],
            [['ns'], 'validateNamespace'],
            [['collectionName'], 'validateCollectionName'],
            [['attributeList'], 'match', 'pattern' => '/^(\w+\,[ ]*)*([\w]+)$/', 'message' => 'Attributes should contain only word characters, and should be separated by coma.'],
            [['modelClass'], 'validateModelClass', 'skipOnEmpty' => false],
            [['baseClass'], 'validateClass', 'params' => ['extends' => ActiveRecord::className()]],
            [['enableI18N'], 'boolean'],
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
            'db' => 'MongoDB Connection ID',
            'collectionName' => 'Collection Name',
            'databaseName' => 'Database Name',
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
            'db' => 'This is the ID of the MongoDB application component.',
            'collectionName' => 'This is the name of the MongoDB collection that the new ActiveRecord class is associated with, e.g. <code>post</code>.',
            'databaseName' => 'This is the name of the MongoDB database, which contains the collection that the new ActiveRecord class is associated with.
                You may leave this field blank, if your application uses single MongoDB database.',
            'attributeList' => 'List of the collection attribute names separated by coma.
                You do not need to specify "_id" attribute here - it will be added automatically.',
            'modelClass' => 'This is the name of the ActiveRecord class to be generated. The class name should not contain
                the namespace part as it is specified in "Namespace". You may leave this field blank - in this case class name
                will be generated automatically.',
            'baseClass' => 'This is the base class of the new ActiveRecord class. It should be a fully qualified namespaced class name.',
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
                'collectionName' => function () use ($db) {
                    return $db->getDatabase()->mongoDb->getCollectionNames();
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
        $collectionName = $this->collectionName;

        $attributes = ['_id'];
        if (!empty($this->attributeList)) {
            $customAttributes = explode(',', $this->attributeList);
            $customAttributes = array_map('trim', $customAttributes);
            $attributes = array_merge(['_id'], $customAttributes);
        }

        $className = $this->generateClassName($collectionName);
        $params = [
            'collectionName' => $collectionName,
            'className' => $className,
            'attributes' => $attributes,
            'labels' => $this->generateLabels($attributes),
            'rules' => $this->generateRules($attributes),
        ];
        $files[] = new CodeFile(
            Yii::getAlias('@' . str_replace('\\', '/', $this->ns)) . '/' . $className . '.php',
            $this->render('model.php', $params)
        );

        return $files;
    }

    /**
     * Generates the attribute labels for the specified attributes list.
     * @param array $attributes the list of attributes
     * @return array the generated attribute labels (name => label)
     */
    public function generateLabels($attributes)
    {
        $labels = [];
        foreach ($attributes as $attribute) {
            if (!strcasecmp($attribute, '_id')) {
                $label = 'ID';
            } else {
                $label = Inflector::camel2words($attribute);
                if (substr_compare($label, ' id', -3, null, true) === 0) {
                    $label = substr($label, 0, -3) . ' ID';
                }
            }
            $labels[$attribute] = $label;
        }

        return $labels;
    }

    /**
     * Generates validation rules for the specified table.
     * @param array $attributes the list of attributes
     * @return array the generated validation rules
     */
    public function generateRules($attributes)
    {
        $rules = [];
        $safeAttributes = [];
        foreach ($attributes as $attribute) {
            if ($attribute == '_id') {
                continue;
            }
            $safeAttributes[] = $attribute;
        }
        if (!empty($safeAttributes)) {
            $rules[] = "[['" . implode("', '", $safeAttributes) . "'], 'safe']";
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
            $this->addError('db', 'The "' . $this->db . '" application component must be a MongoDB connection instance.');
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
    }

    /**
     * Validates the [[collectionName]] attribute.
     */
    public function validateCollectionName()
    {
        if (empty($this->modelClass)) {
            $class = $this->generateClassName($this->collectionName);
            if ($this->isReservedKeyword($class)) {
                $this->addError('collectionName', "Collection '{$this->collectionName}' will generate a class which is a reserved PHP keyword.");
            }
        }
    }

    /**
     * Generates a class name from the specified table name.
     * @param string $collectionName the table name (which may contain schema prefix)
     * @return string the generated class name
     */
    protected function generateClassName($collectionName)
    {
        $className = preg_replace('/[^\\w]+/is', '_', $collectionName);
        return Inflector::id2camel($className, '_');
    }

    /**
     * @return Connection the DB connection as specified by [[db]].
     */
    protected function getDbConnection()
    {
        return Yii::$app->get($this->db, false);
    }
}
