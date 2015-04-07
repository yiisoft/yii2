<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\grid;

use Yii;
use yii\base\Arrayable;
use yii\i18n\Formatter;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\base\Widget;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;

/**
 * DetailView displays the detail of a single data [[Model]].
 *
 * yii\grid\DetailView is similar to [[yii\widgets\DetailView]] but uses [[Column]]
 * to show header and data part of given [[Model]].
 *
 * The contents of the detail table are configured in terms of [[Column]]
 * classes, which are configured via $columns.
 * Helps reuse your code by defining [[Column]]s once and reusing them for
 * both GridView and DetailView. Filter and footer part of [[Column]] are
 * not used.
 *
 * A typical usage of DetailView is as follows:
 *
 * ~~~
 * echo DetailView::widget([
 *     'model' => $model,
 *     'columns' => [
 *         'title',               // title attribute (in plain text)
 *         'description:html',    // description attribute in HTML
 *         [                      // the owner name of the model
 *             'label' => 'Owner',
 *             'value' => $model->owner->name,
 *         ],
 *         'created_at:datetime', // creation date formatted as datetime
 *         [
 *             'class' => ActionColumn::className(),
 *         ],
 *     ],
 * ]);
 * ~~~
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 * @since 2.0
 */
class DetailView extends Widget
{
    use ColumnViewTrait;

    /**
     * @var array|object the data model whose details are to be displayed. This can be a [[Model]] instance,
     * an associative array, an object that implements [[Arrayable]] interface or simply an object with defined
     * public accessible non-static properties.
     */
    public $model;
    /**
     * @var \yii\data\DataProviderInterface
     * automatically created from $model
     */
    private $_dataProvider;
    /**
     * @var string|callable the template used to render a single attribute. If a string, the token `{label}`
     * and `{value}` will be replaced with the label and the value of the corresponding attribute.
     * If a callback (e.g. an anonymous function), the signature must be as follows:
     *
     * ~~~
     * function ($attribute, $index, $widget)
     * ~~~
     *
     * where `$attribute` refer to the specification of the attribute being rendered, `$index` is the zero-based
     * index of the attribute in the [[attributes]] array, and `$widget` refers to this widget instance.
     */
    public $template = "<tr>{label}{value}</tr>";
    /**
     * @var array the HTML attributes for the container tag of this widget. The "tag" option specifies
     * what container tag should be used. It defaults to "table" if not set.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = ['class' => 'table table-striped table-bordered detail-view'];

    /**
     * Initializes the detail view.
     * This method will initialize required property values.
     */
    public function init()
    {
        if ($this->model === null) {
            throw new InvalidConfigException('Please specify the "model" property.');
        }
        $this->_dataProvider = new ArrayDataProvider(['allModels' => [$this->model]]);
        $this->initFormatter();
        $this->initColumns();
    }

    /**
     * Getter for _dataProvider
     */
    public function getDataProvider()
    {
        return $this->_dataProvider;
    }

    /**
     * Renders the detail view.
     * This is the main entry of the whole detail view rendering.
     */
    public function run()
    {
        $rows = [];
        $i = 0;
        foreach ($this->columns as $column) {
            $rows[] = $this->renderColumn($column, $i++);
        }

        $tag = ArrayHelper::remove($this->options, 'tag', 'table');
        echo Html::tag($tag, implode("\n", $rows), $this->options);
    }

    /**
     * Renders a single column.
     * @param array $column the specification of the column to be rendered.
     * @param integer $index the zero-based index of the column in the [[columns]] array
     * @return string the rendering result
     */
    protected function renderColumn($column, $index)
    {
        if (is_string($this->template)) {
            return strtr($this->template, [
                //'{label}' => $attribute['label'],
                //'{value}' => $this->formatter->format($attribute['value'], $attribute['format']),
                '{label}' => $column->renderHeaderCell(),
                '{value}' => $column->renderDataCell($this->model, 0, 0),
            ]);
        } else {
            return call_user_func($this->template, $column, $index, $this);
        }
    }

}
