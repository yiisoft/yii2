<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\test;

use Yii;
use yii\base\ArrayAccessTrait;
use yii\base\InvalidConfigException;

/**
 * BaseActiveFixture is the base class for fixture classes that support accessing fixture data as ActiveRecord objects.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class BaseActiveFixture extends DbFixture implements \IteratorAggregate, \ArrayAccess, \Countable
{
    use ArrayAccessTrait;

    /**
     * @var string the AR model class associated with this fixture.
     */
    public $modelClass;
    /**
     * @var array the data rows. Each array element represents one row of data (column name => column value).
     */
    public $data = [];
    /**
     * @var string|boolean the file path or path alias of the data file that contains the fixture data
     * to be returned by [[getData()]]. You can set this property to be false to prevent loading any data.
     */
    public $dataFile;

    /**
     * @var \yii\db\ActiveRecord[] the loaded AR models
     */
    private $_models = [];


    /**
     * Returns the AR model by the specified model name.
     * A model name is the key of the corresponding data row in [[data]].
     * @param string $name the model name.
     * @return null|\yii\db\ActiveRecord the AR model, or null if the model cannot be found in the database
     * @throws \yii\base\InvalidConfigException if [[modelClass]] is not set.
     */
    public function getModel($name)
    {
        if (!isset($this->data[$name])) {
            return null;
        }
        if (array_key_exists($name, $this->_models)) {
            return $this->_models[$name];
        }

        if ($this->modelClass === null) {
            throw new InvalidConfigException('The "modelClass" property must be set.');
        }
        $row = $this->data[$name];
        /* @var $modelClass \yii\db\ActiveRecord */
        $modelClass = $this->modelClass;
        /* @var $model \yii\db\ActiveRecord */
        $model = new $modelClass;
        $keys = [];
        foreach ($model->primaryKey() as $key) {
            $keys[$key] = isset($row[$key]) ? $row[$key] : null;
        }

        return $this->_models[$name] = $modelClass::findOne($keys);
    }

    /**
     * Loads the fixture.
     *
     * The default implementation simply stores the data returned by [[getData()]] in [[data]].
     * You should usually override this method by putting the data into the underlying database.
     */
    public function load()
    {
        $this->data = $this->getData();
    }

    /**
     * Returns the fixture data.
     *
     * The default implementation will try to return the fixture data by including the external file specified by [[dataFile]].
     * The file should return the data array that will be stored in [[data]] after inserting into the database.
     *
     * @return array the data to be put into the database
     * @throws InvalidConfigException if the specified data file does not exist.
     */
    protected function getData()
    {
        if ($this->dataFile === false || $this->dataFile === null) {
            return [];
        }
        $dataFile = Yii::getAlias($this->dataFile);
        if (is_file($dataFile)) {
            return require($dataFile);
        } else {
            throw new InvalidConfigException("Fixture data file does not exist: {$this->dataFile}");
        }
    }

    /**
     * @inheritdoc
     */
    public function unload()
    {
        parent::unload();
        $this->data = [];
        $this->_models = [];
    }
}
