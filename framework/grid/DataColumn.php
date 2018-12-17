<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\grid;

use Closure;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\ActiveQueryInterface;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;

/**
 * DataColumn 是 [[GridView]] 小部件的默认列类型。
 *
 * 它用于显示数据列并且可以使用 [[enableSorting|sorting]] 和 [[filter|filtering]]。
 *
 * 简单的数据列定义是指 GridView 数据提供者的数据模型中的属性。
 * 属性的名称由 [[attribute]] 来指定。
 *
 * 通过设置 [[value]] 和 [[label]]，标题和单元格内容可以自定义。
 *
 * 数据列区分 [[getDataCellValue|data cell value]] 和 [[renderDataCellContent|data cell content]]。
 * 单元格值是可以用于计算的未格式化的值，
 * 但实际单元格内容是该值的 [[format|formatted]] 版本，
 * 其可以包含 HTML 标签。
 *
 * 有关于 DataColumn 更多的细节和用法，请参阅 [guide article on data widgets](guide:output-data-widgets)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DataColumn extends Column
{
    /**
     * @var string 与此列关联的属性名称。
     * 如果没有指定 [[content]] 也没有指定 [[value]]，则将从每个数据模型中检索并显示指定属性的值。
     *
     * 此外，如果 [[label]] 没有指定，将显示与该属性关联的标签。
     */
    public $attribute;
    /**
     * @var string label 在 [[header|header cell]] 中显示，
     * 并且未在此列启用排序时用作排序链接的标签。
     * 如果没有设置，并且 GridView 数据提供器是 [[\yii\db\ActiveRecord]] 的实例，
     * 标签将使用 [[\yii\db\ActiveRecord::getAttributeLabel()]] 来确定。
     * 否则 [[\yii\helpers\Inflector::camel2words()]] 将用于获取标签。
     */
    public $label;
    /**
     * @var bool 标题标签是否是 HTML 编码的。
     * @see label
     * @since 2.0.1
     */
    public $encodeLabel = true;
    /**
     * @var string|Closure 匿名函数或用于确定要在当前列中显示的值的字符串。
     *
     * 如果这是一个匿名函数, 每一行都将调用它，返回值将用作为每个数据模型显示的值。
     * 函数的写法应该为：`function ($model, $key, $index, $column)`。
     * 其中，`$model`，`$key` 和 `$index` 表示当前渲染行的模型，键和索引，
     * `$column` 是对 [[DataColumn]] 对象的引用。
     *
     * 你还可以将此属性设置为表示要在此列中显示的属性名称的字符串。
     * 当要显示的属性与用于排序和过滤的 [[attribute]] 不同时，
     * 可以使用此选项。
     *
     * 如果没有设置，将使用 `$model[$attribute]` 来获取值，其中 `$attribute` 是 [[attribute]] 的值。
     */
    public $value;
    /**
     * @var string|array|Closure 每个数据模型的值应该以何种格式显示（e.g. `"raw"`，`"text"`，`"html"`，`['date', 'php:Y-m-d']`）。
     * 支持的格式由 [[GridView]] 使用的 [[GridView::formatter|formatter]] 来确定。
     * 默认的格式是 "text"，当 GridView 的 [[\yii\i18n\Formatter]] 用作 [[GridView::$formatter|formatter]] 时，
     * 该值将格式化为 HTML 编码的纯文本。
     * @see \yii\i18n\Formatter::format()
     */
    public $format = 'text';
    /**
     * @var bool 是否允许按此列排序。如果为 true，
     * 并且在 [[GridView::dataProvider]] 的排序定义中找到 [[attribute]]，
     * 则此列的标题单元格将包含可能在单击时触发排序的链接。
     */
    public $enableSorting = true;
    /**
     * @var array 当此列启用排序时，
     * 由 [[\yii\data\Sort::link]] 生成的标题单元格中的链接标记的 HMTL 属性。
     * @see \yii\helpers\Html::renderTagAttributes() 有关如何渲染属性的详细信息。
     */
    public $sortLinkOptions = [];
    /**
     * @var string|array|null|false HTML 代码表示用于此数据列的过滤器输入（e.g. 文本字段，下拉列表）。
     * 仅当 [[GridView::filterModel]] 设置时，此属性才有效。
     *
     * - 如果未设置此属性，将生成一个文本字段作为过滤器输入，
     *   其属性由 [[filterInputOptions]] 来定义。
     *   有关于如何生成输入标记的详细信息，请参阅 [[\yii\helpers\BaseHtml::activeInput]]。
     * - 如果属性是一个数组，
     *   将生成一个下拉列表，该列表使用此属性值作为列表选项。
     * - 如果你不想要此数据列的过滤器，请将此值设置为false。
     */
    public $filter;
    /**
     * @var array 过滤器输入字段的 HTML 属性。
     * 此属性与 [[filter]] 属性结合使用。当 [[filter]] 没有设置或者是一个数组，
     * 此属性将用于渲染生成的过滤器输入字段的 HTML 属性。
     *
     * Empty `id` in the default value ensures that id would not be obtained from the model attribute thus
     * providing better performance.
     *
     * @see \yii\helpers\Html::renderTagAttributes() 有关如何渲染属性的详细信息。
     */
    public $filterInputOptions = ['class' => 'form-control', 'id' => null];


    /**
     * {@inheritdoc}
     */
    protected function renderHeaderCellContent()
    {
        if ($this->header !== null || $this->label === null && $this->attribute === null) {
            return parent::renderHeaderCellContent();
        }

        $label = $this->getHeaderCellLabel();
        if ($this->encodeLabel) {
            $label = Html::encode($label);
        }

        if ($this->attribute !== null && $this->enableSorting &&
            ($sort = $this->grid->dataProvider->getSort()) !== false && $sort->hasAttribute($this->attribute)) {
            return $sort->link($this->attribute, array_merge($this->sortLinkOptions, ['label' => $label]));
        }

        return $label;
    }

    /**
     * {@inheritdoc]
     * @since 2.0.8
     */
    protected function getHeaderCellLabel()
    {
        $provider = $this->grid->dataProvider;

        if ($this->label === null) {
            if ($provider instanceof ActiveDataProvider && $provider->query instanceof ActiveQueryInterface) {
                /* @var $modelClass Model */
                $modelClass = $provider->query->modelClass;
                $model = $modelClass::instance();
                $label = $model->getAttributeLabel($this->attribute);
            } elseif ($provider instanceof ArrayDataProvider && $provider->modelClass !== null) {
                /* @var $modelClass Model */
                $modelClass = $provider->modelClass;
                $model = $modelClass::instance();
                $label = $model->getAttributeLabel($this->attribute);
            } elseif ($this->grid->filterModel !== null && $this->grid->filterModel instanceof Model) {
                $label = $this->grid->filterModel->getAttributeLabel($this->attribute);
            } else {
                $models = $provider->getModels();
                if (($model = reset($models)) instanceof Model) {
                    /* @var $model Model */
                    $label = $model->getAttributeLabel($this->attribute);
                } else {
                    $label = Inflector::camel2words($this->attribute);
                }
            }
        } else {
            $label = $this->label;
        }

        return $label;
    }

    /**
     * {@inheritdoc}
     */
    protected function renderFilterCellContent()
    {
        if (is_string($this->filter)) {
            return $this->filter;
        }

        $model = $this->grid->filterModel;

        if ($this->filter !== false && $model instanceof Model && $this->attribute !== null && $model->isAttributeActive($this->attribute)) {
            if ($model->hasErrors($this->attribute)) {
                Html::addCssClass($this->filterOptions, 'has-error');
                $error = ' ' . Html::error($model, $this->attribute, $this->grid->filterErrorOptions);
            } else {
                $error = '';
            }
            if (is_array($this->filter)) {
                $options = array_merge(['prompt' => ''], $this->filterInputOptions);
                return Html::activeDropDownList($model, $this->attribute, $this->filter, $options) . $error;
            } elseif ($this->format === 'boolean') {
                $options = array_merge(['prompt' => ''], $this->filterInputOptions);
                return Html::activeDropDownList($model, $this->attribute, [
                    1 => $this->grid->formatter->booleanFormat[1],
                    0 => $this->grid->formatter->booleanFormat[0],
                ], $options) . $error;
            }

            return Html::activeTextInput($model, $this->attribute, $this->filterInputOptions) . $error;
        }

        return parent::renderFilterCellContent();
    }

    /**
     * 返回数据单元格值。
     * @param mixed $model 数据模型
     * @param mixed $key 与数据模型相关的键
     * @param int $index 由 [[GridView::dataProvider]] 返回的模型数组中的数据模型的从零开始的索引。
     * @return string 数据单元格值
     */
    public function getDataCellValue($model, $key, $index)
    {
        if ($this->value !== null) {
            if (is_string($this->value)) {
                return ArrayHelper::getValue($model, $this->value);
            }

            return call_user_func($this->value, $model, $key, $index, $this);
        } elseif ($this->attribute !== null) {
            return ArrayHelper::getValue($model, $this->attribute);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->content === null) {
            return $this->grid->formatter->format($this->getDataCellValue($model, $key, $index), $this->format);
        }

        return parent::renderDataCellContent($model, $key, $index);
    }
}
