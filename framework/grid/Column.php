<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\grid;

use Closure;
use yii\base\BaseObject;
use yii\helpers\Html;

/**
 * Column 是所有 [[GridView]] 列类的基类。
 *
 * 有关 Column 的更多细节和用法，请参阅 [guide article on data widgets](guide:output-data-widgets)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Column extends BaseObject
{
    /**
     * @var GridView 拥有此列的网格视图对象。
     */
    public $grid;
    /**
     * @var string 标题单元格内容。注意，它不是 HTML 编码的。
     */
    public $header;
    /**
     * @var string 页脚单元格内容。注意，它不是 HTML 编码的。
     */
    public $footer;
    /**
     * @var callable 这是用于生成每个单元格内容的回调函数。
     * 函数的写法如下：`function ($model, $key, $index, $column)`。
     * 其中，`$model`，`$key` 和 `$index` 表示当前渲染行的模型，键和索引，
     * `$column` 是对 [[Column]] 对象的引用。
     */
    public $content;
    /**
     * @var bool 列是否可见。默认为 true。
     */
    public $visible = true;
    /**
     * @var array 列组标签的 HTML 属性。
     * @see \yii\helpers\Html::renderTagAttributes() 有关如何渲染属性的详细信息。
     */
    public $options = [];
    /**
     * @var array 标题单元格标签的 HTML 属性。
     * @see \yii\helpers\Html::renderTagAttributes() 有关如何渲染属性的详细信息。
     */
    public $headerOptions = [];
    /**
     * @var array|\Closure 数据单元标签的 HTML 属性。
     * 这可以是属性数组或返回此类数组的匿名函数（[[Closure]]）。
     * 函数的写法应该如下：`function ($model, $key, $index, $column)`。
     * 其中，`$model`，`$key` 和 `$index` 表示当前渲染行的模型、键和索引，
     * `$column` 是对 [[Column]] 对象的引用。
     * 函数可用于根据该行中的数据将不同的属性分配给不同的行。
     *
     * @see \yii\helpers\Html::renderTagAttributes() 有关如何渲染属性的详细信息。
     */
    public $contentOptions = [];
    /**
     * @var array the 页脚单元格标签的 HTML 属性。
     * @see \yii\helpers\Html::renderTagAttributes() 有关如何渲染属性的详细信息。
     */
    public $footerOptions = [];
    /**
     * @var array 筛选单元格标签的 HTML 属性。
     * @see \yii\helpers\Html::renderTagAttributes() 有关如何渲染属性的详细信息。
     */
    public $filterOptions = [];


    /**
     * Renders the header cell.
     */
    public function renderHeaderCell()
    {
        return Html::tag('th', $this->renderHeaderCellContent(), $this->headerOptions);
    }

    /**
     * 渲染标题单元格。
     */
    public function renderFooterCell()
    {
        return Html::tag('td', $this->renderFooterCellContent(), $this->footerOptions);
    }

    /**
     * 渲染数据单元格。
     * @param mixed $model 数据模型
     * @param mixed $key 与数据模型相关的键
     * @param int $index 由 [[GridView::dataProvider]] 返回的模型数组中的数据模型的从零开始的索引。
     * @return string 渲染结果
     */
    public function renderDataCell($model, $key, $index)
    {
        if ($this->contentOptions instanceof Closure) {
            $options = call_user_func($this->contentOptions, $model, $key, $index, $this);
        } else {
            $options = $this->contentOptions;
        }

        return Html::tag('td', $this->renderDataCellContent($model, $key, $index), $options);
    }

    /**
     * Renders the filter cell.
     */
    public function renderFilterCell()
    {
        return Html::tag('td', $this->renderFilterCellContent(), $this->filterOptions);
    }

    /**
     * 渲染过滤单元格。
     * 默认实现只是渲染 [[header]]。
     * 可以重写此方法以自定义标题单元格的渲染。
     * @return string 渲染结果
     */
    protected function renderHeaderCellContent()
    {
        return trim($this->header) !== '' ? $this->header : $this->getHeaderCellLabel();
    }

    /**
     * 返回标题单元格标签。
     * 可以重写此方法以自定义标题单元格的标签。
     * @return string label
     * @since 2.0.8
     */
    protected function getHeaderCellLabel()
    {
        return $this->grid->emptyCell;
    }

    /**
     * 渲染页脚单元格内容。
     * 默认实现只是渲染 [[footer]]。
     * 可以重写此方法以自定义页脚单元格的渲染。
     * @return string 渲染结果
     */
    protected function renderFooterCellContent()
    {
        return trim($this->footer) !== '' ? $this->footer : $this->grid->emptyCell;
    }

    /**
     * 渲染数据单元格内容。
     * @param mixed $model 数据模型
     * @param mixed $key 与数据模型相关的键
     * @param int $index 由 [[GridView::dataProvider]] 返回的模型数组中的数据模型的从零开始的索引。
     * @return string 渲染结果
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->content !== null) {
            return call_user_func($this->content, $model, $key, $index, $this);
        }

        return $this->grid->emptyCell;
    }

    /**
     * 渲染过滤单元格内容。
     * 默认实现只是渲染一个空格。
     * 可以重写此方法以自定义过滤器单元的渲染（如果有）。
     * @return string 渲染结果
     */
    protected function renderFilterCellContent()
    {
        return $this->grid->emptyCell;
    }
}
