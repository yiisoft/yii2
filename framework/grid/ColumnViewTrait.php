<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\grid;

use Yii;
use yii\i18n\Formatter;
use yii\base\InvalidConfigException;

/**
 * ColumnViewTrait provides functionality common for GridView and DetailView and
 * could be used for other classes containing [[DataColumn]]s
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 * @since 2.0
 */
trait ColumnViewTrait
{
    /**
     * @var string the default data column class if the class name is not explicitly specified when configuring a data column.
     * Defaults to 'yii\grid\DataColumn'.
     */
    public $dataColumnClass;
    /**
     * @var array grid column configuration. Each array element represents the configuration
     * for one particular grid column. For example,
     *
     * ```php
     * [
     *     ['class' => SerialColumn::className()],
     *     [
     *         'class' => DataColumn::className(), // this line is optional
     *         'attribute' => 'name',
     *         'format' => 'text',
     *         'label' => 'Name',
     *     ],
     *     ['class' => CheckboxColumn::className()],
     * ]
     * ```
     *
     * If a column is of class [[DataColumn]], the "class" element can be omitted.
     *
     * As a shortcut format, a string may be used to specify the configuration of a data column
     * which only contains [[DataColumn::attribute|attribute]], [[DataColumn::format|format]],
     * and/or [[DataColumn::label|label]] options: `"attribute:format:label"`.
     * For example, the above "name" column can also be specified as: `"name:text:Name"`.
     * Both "format" and "label" are optional. They will take default values if absent.
     *
     * Using the shortcut format the configuration for columns in simple cases would look like this:
     *
     * ```php
     * [
     *     'id',
     *     'amount:currency:Total Amount',
     *     'created_at:datetime',
     * ]
     * ```
     *
     * When using a [[dataProvider]] with active records, you can also display values from related records,
     * e.g. the `name` attribute of the `author` relation:
     *
     * ```php
     * // shortcut syntax
     * 'author.name',
     * // full syntax
     * [
     *     'attribute' => 'author.name',
     *     // ...
     * ]
     * ```
     */
    public $columns = [];
    /**
     * @var array|Formatter the formatter used to format model attribute values into displayable texts.
     * This can be either an instance of [[Formatter]] or an configuration array for creating the [[Formatter]]
     * instance. If this property is not set, the "formatter" application component will be used.
     */
    public $formatter;

    /**
     * Initializes $formatter.
     */
    public function initFormatter()
    {
        if ($this->formatter === null) {
            $this->formatter = Yii::$app->getFormatter();
        } elseif (is_array($this->formatter)) {
            $this->formatter = Yii::createObject($this->formatter);
        }
        if (!$this->formatter instanceof Formatter) {
            throw new InvalidConfigException('The "formatter" property must be either a Format object or a configuration array.');
        }
    }

    /**
     * Creates column objects and initializes them.
     */
    protected function initColumns()
    {
        if (empty($this->columns)) {
            $this->guessColumns();
        }
        foreach ($this->columns as $i => $column) {
            if (is_string($column)) {
                $column = $this->createDataColumn($column);
            } else {
                $column = $this->createColumnObject($column);
            }
            if (!$column->visible) {
                unset($this->columns[$i]);
                continue;
            }
            $this->columns[$i] = $column;
        }
    }

    /**
     * Creates a [[DataColumn]] object based on a string in the format of "attribute:format:label".
     * @param string $text the column specification string
     * @return DataColumn the column instance
     * @throws InvalidConfigException if the column specification is invalid
     */
    protected function createDataColumn($text)
    {
        if (!preg_match('/^([^:]+)(:(\w*))?(:(.*))?$/', $text, $matches)) {
            throw new InvalidConfigException('The column must be specified in the format of "attribute", "attribute:format" or "attribute:format:label"');
        }

        return $this->createColumnObject([
            'attribute' => $matches[1],
            'format' => isset($matches[3]) ? $matches[3] : 'text',
            'label' => isset($matches[5]) ? $matches[5] : null,
        ]);
    }

    /**
     * Creates a [[DataColumn]] object with given additional config
     * @param array $config additional config for [[DataColumn]]
     * @return DataColumn the column instance
     */
    protected function createColumnObject(array $config = []) {
        return Yii::createObject(array_merge([
            'class' => $this->dataColumnClass ? : DataColumn::className(),
            'grid' => $this,
        ], $config));
    }

    /**
     * This function tries to guess the columns to show from the given data
     * if [[columns]] are not explicitly specified.
     */
    protected function guessColumns()
    {
        $models = $this->dataProvider->getModels();
        $model = reset($models);
        if (is_array($model) || is_object($model)) {
            foreach ($model as $name => $value) {
                $this->columns[] = $name;
            }
        }
    }

}
