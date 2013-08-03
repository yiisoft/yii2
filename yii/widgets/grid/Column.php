<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets\grid;

use Closure;
use yii\base\Object;
use yii\helpers\Html;
use yii\widgets\GridView;

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Column extends Object
{
	/**
	 * @var string the ID of this column. This value should be unique among all grid view columns.
	 * If this is not set, it will be assigned one automatically.
	 */
	public $id;
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
	 * @var callable
	 */
	public $content;
	/**
	 * @var boolean whether this column is visible. Defaults to true.
	 */
	public $visible = true;
	public $options = array();
	public $headerOptions = array();
	/**
	 * @var array|\Closure
	 */
	public $bodyOptions = array();
	public $footerOptions = array();
	/**
	 * @var array the HTML attributes for the filter cell tag.
	 */
	public $filterOptions=array();


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
	 * @param integer $index the zero-based index of the data item among the item array returned by [[dataProvider]].
	 * @return string the rendering result
	 */
	public function renderDataCell($model, $index)
	{
		if ($this->bodyOptions instanceof Closure) {
			$options = call_user_func($this->bodyOptions, $model, $index, $this);
		} else {
			$options = $this->bodyOptions;
		}
		return Html::tag('td', $this->renderDataCellContent($model, $index), $options);
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
	 * The default implementation simply renders {@link header}.
	 * This method may be overridden to customize the rendering of the header cell.
	 * @return string the rendering result
	 */
	protected function renderHeaderCellContent()
	{
		return trim($this->header) !== '' ? $this->header : $this->grid->emptyCell;
	}

	/**
	 * Renders the footer cell content.
	 * The default implementation simply renders {@link footer}.
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
	 * @param integer $index the zero-based index of the data model among the models array returned by [[dataProvider]].
	 * @return string the rendering result
	 */
	protected function renderDataCellContent($model, $index)
	{
		if ($this->content !== null) {
			return call_user_func($this->content, $model, $index, $this);
		} else {
			return $this->grid->emptyCell;
		}
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
