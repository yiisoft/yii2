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
 * Column is the base class of all [[GridView]] column classes.
 *
 * For more details and usage information on Column, see the [guide article on data widgets](guide:output-data-widgets).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Column extends BaseObject
{
    /**
     * @var GridView the grid view object that owns this column.
     */
    public $grid;
    /**
     * @var string the header cell content. Note that it will not be HTML-encoded.
     */
    public $header;
    /**
     * @var string the footer cell content. Note that it will not be HTML-encoded.
     */
    public $footer;
    /**
     * @var callable This is a callable that will be used to generate the content of each cell.
     * The signature of the function should be the following: `function ($model, $key, $index, $column)`.
     * Where `$model`, `$key`, and `$index` refer to the model, key and index of the row currently being rendered
     * and `$column` is a reference to the [[Column]] object.
     */
    public $content;
    /**
     * @var bool whether this column is visible. Defaults to true.
     */
    public $visible = true;
    /**
     * @var array the HTML attributes for the column group tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = [];
    /**
     * @var array the HTML attributes for the header cell tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $headerOptions = [];
    /**
     * @var array|\Closure the HTML attributes for the data cell tag. This can either be an array of
     * attributes or an anonymous function ([[Closure]]) that returns such an array.
     * The signature of the function should be the following: `function ($model, $key, $index, $column)`.
     * Where `$model`, `$key`, and `$index` refer to the model, key and index of the row currently being rendered
     * and `$column` is a reference to the [[Column]] object.
     * A function may be used to assign different attributes to different rows based on the data in that row.
     *
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $contentOptions = [];
    /**
     * @var array the HTML attributes for the footer cell tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $footerOptions = [];
    /**
     * @var array the HTML attributes for the filter cell tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
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
     * Renders the footer cell.
     */
    public function renderFooterCell()
    {
        return Html::tag('td', $this->renderFooterCellContent(), $this->footerOptions);
    }

    /**
     * Renders a data cell.
     * @param mixed $model the data model being rendered
     * @param mixed $key the key associated with the data model
     * @param int $index the zero-based index of the data item among the item array returned by [[GridView::dataProvider]].
     * @return string the rendering result
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
     * Renders the header cell content.
     * The default implementation simply renders [[header]].
     * This method may be overridden to customize the rendering of the header cell.
     * @return string the rendering result
     */
    protected function renderHeaderCellContent()
    {
        return trim($this->header) !== '' ? $this->header : $this->getHeaderCellLabel();
    }

    /**
     * Returns header cell label.
     * This method may be overridden to customize the label of the header cell.
     * @return string label
     * @since 2.0.8
     */
    protected function getHeaderCellLabel()
    {
        return $this->grid->emptyCell;
    }

    /**
     * Renders the footer cell content.
     * The default implementation simply renders [[footer]].
     * This method may be overridden to customize the rendering of the footer cell.
     * @return string the rendering result
     */
    protected function renderFooterCellContent()
    {
        return trim($this->footer) !== '' ? $this->footer : $this->grid->emptyCell;
    }

    /**
     * Renders the data cell content.
     * @param mixed $model the data model
     * @param mixed $key the key associated with the data model
     * @param int $index the zero-based index of the data model among the models array returned by [[GridView::dataProvider]].
     * @return string the rendering result
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->content !== null) {
            return call_user_func($this->content, $model, $key, $index, $this);
        }

        return $this->grid->emptyCell;
    }

    /**
     * Renders the filter cell content.
     * The default implementation simply renders a space.
     * This method may be overridden to customize the rendering of the filter cell (if any).
     * @return string the rendering result
     */
    protected function renderFilterCellContent()
    {
        return $this->grid->emptyCell;
    }
}
