<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\grid;

use Yii;
use Closure;
use yii\base\Formatter;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\widgets\ListViewBase;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class GridView extends ListViewBase
{
	const FILTER_POS_HEADER = 'header';
	const FILTER_POS_FOOTER = 'footer';
	const FILTER_POS_BODY = 'body';

	/**
	 * @var string the default data column class if the class name is not explicitly specified when configuring a data column.
	 * Defaults to 'yii\grid\DataColumn'.
	 */
	public $dataColumnClass;
	/**
	 * @var string the caption of the grid table
	 * @see captionOptions
	 */
	public $caption;
	/**
	 * @var array the HTML attributes for the caption element
	 * @see caption
	 */
	public $captionOptions = array();
	/**
	 * @var array the HTML attributes for the grid table element
	 */
	public $tableOptions = array('class' => 'table table-striped table-bordered');
	/**
	 * @var array the HTML attributes for the table header row
	 */
	public $headerRowOptions = array();
	/**
	 * @var array the HTML attributes for the table footer row
	 */
	public $footerRowOptions = array();
	/**
	 * @var array|Closure the HTML attributes for the table body rows. This can be either an array
	 * specifying the common HTML attributes for all body rows, or an anonymous function that
	 * returns an array of the HTML attributes. The anonymous function will be called once for every
	 * data model returned by [[dataProvider]]. It should have the following signature:
	 *
	 * ~~~php
	 * function ($model, $key, $index, $grid)
	 * ~~~
	 *
	 * - `$model`: the current data model being rendered
	 * - `$key`: the key value associated with the current data model
	 * - `$index`: the zero-based index of the data model in the model array returned by [[dataProvider]]
	 * - `$grid`: the GridView object
	 */
	public $rowOptions = array();
	/**
	 * @var Closure an anonymous function that is called once BEFORE rendering each data model.
	 * It should have the similar signature as [[rowOptions]]. The return result of the function
	 * will be rendered directly.
	 */
	public $beforeRow;
	/**
	 * @var Closure an anonymous function that is called once AFTER rendering each data model.
	 * It should have the similar signature as [[rowOptions]]. The return result of the function
	 * will be rendered directly.
	 */
	public $afterRow;
	/**
	 * @var boolean whether to show the header section of the grid table.
	 */
	public $showHeader = true;
	/**
	 * @var boolean whether to show the footer section of the grid table.
	 */
	public $showFooter = false;
	/**
	 * @var array|Formatter the formatter used to format model attribute values into displayable texts.
	 * This can be either an instance of [[Formatter]] or an configuration array for creating the [[Formatter]]
	 * instance. If this property is not set, the "formatter" application component will be used.
	 */
	public $formatter;
	/**
	 * @var array grid column configuration. Each array element represents the configuration
	 * for one particular grid column. For example,
	 *
	 * ~~~php
	 * array(
	 *     array(
	 *         'class' => SerialColumn::className(),
	 *     ),
	 *     array(
	 *         'class' => DataColumn::className(),
	 *         'attribute' => 'name',
	 *         'format' => 'text',
	 *         'header' => 'Name',
	 *     ),
	 *     array(
	 *         'class' => CheckboxColumn::className(),
	 *     ),
	 * )
	 * ~~~
	 *
	 * If a column is of class [[DataColumn]], the "class" element can be omitted.
	 *
	 * As a shortcut format, a string may be used to specify the configuration of a data column
	 * which only contains "attribute", "format", and/or "header" options: `"attribute:format:header"`.
	 * For example, the above "name" column can also be specified as: `"name:text:Name"`.
	 * Both "format" and "header" are optional. They will take default values if absent.
	 */
	public $columns = array();
	/**
	 * @var string the layout that determines how different sections of the list view should be organized.
	 * The following tokens will be replaced with the corresponding section contents:
	 *
	 * - `{summary}`: the summary section. See [[renderSummary()]].
	 * - `{items}`: the list items. See [[renderItems()]].
	 * - `{sorter}`: the sorter. See [[renderSorter()]].
	 * - `{pager}`: the pager. See [[renderPager()]].
	 */
	public $layout = "{items}\n{summary}\n{pager}";
	public $emptyCell = '&nbsp;';
	/**
	 * @var \yii\base\Model the model that keeps the user-entered filter data. When this property is set,
	 * the grid view will enable column-based filtering. Each data column by default will display a text field
	 * at the top that users can fill in to filter the data.
	 *
	 * Note that in order to show an input field for filtering, a column must have its [[DataColumn::attribute]]
	 * property set or have [[DataColumn::filter]] set as the HTML code for the input field.
	 *
	 * When this property is not set (null) the filtering feature is disabled.
	 */
	public $filterModel;
	/**
	 * @var string whether the filters should be displayed in the grid view. Valid values include:
	 *
	 * - [[FILTER_POS_HEADER]]: the filters will be displayed on top of each column's header cell.
	 * - [[FILTER_POS_BODY]]: the filters will be displayed right below each column's header cell.
	 * - [[FILTER_POS_FOOTER]]: the filters will be displayed below each column's footer cell.
	 */
	public $filterPosition = self::FILTER_POS_BODY;
	/**
	 * @var array the HTML attributes for the filter row element
	 */
	public $filterRowOptions = array('class' => 'filters');

	/**
	 * Initializes the grid view.
	 * This method will initialize required property values and instantiate {@link columns} objects.
	 */
	public function init()
	{
		parent::init();
		if ($this->formatter == null) {
			$this->formatter = Yii::$app->getFormatter();
		} elseif (is_array($this->formatter)) {
			$this->formatter = Yii::createObject($this->formatter);
		}
		if (!$this->formatter instanceof Formatter) {
			throw new InvalidConfigException('The "formatter" property must be either a Format object or a configuration array.');
		}
		if (!isset($this->options['id'])) {
			$this->options['id'] = $this->getId();
		}

		$this->initColumns();
	}

	/**
	 * Runs the widget.
	 */
	public function run()
	{
		$id = $this->options['id'];
		$view = $this->getView();
		GridViewAsset::register($view);
		$view->registerJs("jQuery('#$id').yiiGridView();");
		parent::run();
	}

	/**
	 * Renders the data models for the grid view.
	 */
	public function renderItems()
	{
		$content = array_filter(array(
			$this->renderCaption(),
			$this->renderColumnGroup(),
			$this->showHeader ? $this->renderTableHeader() : false,
			$this->showFooter ? $this->renderTableFooter() : false,
			$this->renderTableBody(),
		));
		return Html::tag('table', implode("\n", $content), $this->tableOptions);
	}

	public function renderCaption()
	{
		if (!empty($this->caption)) {
			return Html::tag('caption', $this->caption, $this->captionOptions);
		} else {
			return false;
		}
	}

	public function renderColumnGroup()
	{
		$requireColumnGroup = false;
		foreach ($this->columns as $column) {
			/** @var Column $column */
			if (!empty($column->options)) {
				$requireColumnGroup = true;
				break;
			}
		}
		if ($requireColumnGroup) {
			$cols = array();
			foreach ($this->columns as $column) {
				$cols[] = Html::tag('col', '', $column->options);
			}
			return Html::tag('colgroup', implode("\n", $cols));
		} else {
			return false;
		}
	}

	/**
	 * Renders the table header.
	 * @return string the rendering result
	 */
	public function renderTableHeader()
	{
		$cells = array();
		foreach ($this->columns as $column) {
			/** @var Column $column */
			$cells[] = $column->renderHeaderCell();
		}
		$content = implode('', $cells);
		if ($this->filterPosition == self::FILTER_POS_HEADER) {
			$content = $this->renderFilters() . $content;
		} elseif ($this->filterPosition == self::FILTER_POS_BODY) {
			$content .= $this->renderFilters();
		}
		return "<thead>\n" . Html::tag('tr', $content, $this->headerRowOptions) . "\n</thead>";
	}

	/**
	 * Renders the table footer.
	 * @return string the rendering result
	 */
	public function renderTableFooter()
	{
		$cells = array();
		foreach ($this->columns as $column) {
			/** @var Column $column */
			$cells[] = $column->renderFooterCell();
		}
		$content = implode('', $cells);
		if ($this->filterPosition == self::FILTER_POS_FOOTER) {
			$content .= $this->renderFilters();
		}
		return "<tfoot>\n" . Html::tag('tr', $content, $this->footerRowOptions) . "\n</tfoot>";
	}

	/**
	 * Renders the filter.
	 */
	public function renderFilters()
	{
		if ($this->filterModel !== null) {
			$cells = array();
			foreach ($this->columns as $column) {
				/** @var Column $column */
				$cells[] = $column->renderFilterCell();
			}
			return Html::tag('tr', implode('', $cells), $this->filterRowOptions);
		} else {
			return '';
		}
	}

	/**
	 * Renders the table body.
	 * @return string the rendering result
	 */
	public function renderTableBody()
	{
		$models = array_values($this->dataProvider->getModels());
		$keys = $this->dataProvider->getKeys();
		$rows = array();
		foreach ($models as $index => $model) {
			$key = $keys[$index];
			if ($this->beforeRow !== null) {
				$row = call_user_func($this->beforeRow, $model, $key, $index, $this);
				if (!empty($row)) {
					$rows[] = $row;
				}
			}

			$rows[] = $this->renderTableRow($model, $key, $index);

			if ($this->afterRow !== null) {
				$row = call_user_func($this->afterRow, $model, $key, $index, $this);
				if (!empty($row)) {
					$rows[] = $row;
				}
			}
		}
		return "<tbody>\n" . implode("\n", $rows) . "\n</tbody>";
	}

	/**
	 * Renders a table row with the given data model and key.
	 * @param mixed $model the data model to be rendered
	 * @param mixed $key the key associated with the data model
	 * @param integer $index the zero-based index of the data model among the model array returned by [[dataProvider]].
	 * @return string the rendering result
	 */
	public function renderTableRow($model, $key, $index)
	{
		$cells = array();
		/** @var Column $column */
		foreach ($this->columns as $column) {
			$cells[] = $column->renderDataCell($model, $index);
		}
		if ($this->rowOptions instanceof Closure) {
			$options = call_user_func($this->rowOptions, $model, $key, $index, $this);
		} else {
			$options = $this->rowOptions;
		}
		$options['data-key'] = $key;
		return Html::tag('tr', implode('', $cells), $options);
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
				$column = Yii::createObject(array_merge(array(
					'class' => $this->dataColumnClass ?: DataColumn::className(),
					'grid' => $this,
				), $column));
			}
			if (!$column->visible) {
				unset($this->columns[$i]);
				continue;
			}
			$this->columns[$i] = $column;
		}
	}

	/**
	 * Creates a [[DataColumn]] object based on a string in the format of "attribute:format:header".
	 * @param string $text the column specification string
	 * @return DataColumn the column instance
	 * @throws InvalidConfigException if the column specification is invalid
	 */
	protected function createDataColumn($text)
	{
		if (!preg_match('/^([\w\.]+)(:(\w*))?(:(.*))?$/', $text, $matches)) {
			throw new InvalidConfigException('The column must be specified in the format of "attribute", "attribute:format" or "attribute:format:header');
		}
		return Yii::createObject(array(
			'class' => $this->dataColumnClass ?: DataColumn::className(),
			'grid' => $this,
			'attribute' => $matches[1],
			'format' => isset($matches[3]) ? $matches[3] : 'text',
			'header' => isset($matches[5]) ? $matches[5] : null,
		));
	}

	protected function guessColumns()
	{
		$models = $this->dataProvider->getModels();
		$model = reset($models);
		if (is_array($model) || is_object($model)) {
			foreach ($model as $name => $value) {
				$this->columns[] = $name;
			}
		} else {
			throw new InvalidConfigException('Unable to generate columns from data.');
		}
	}
}
