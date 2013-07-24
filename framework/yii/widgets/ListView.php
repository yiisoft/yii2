<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ListView extends Widget
{
	/**
	 * @var array the HTML attributes for the container tag of the list view.
	 * The "tag" element specifies the tag name of the container element and defaults to "div".
	 */
	public $options = array();
	/**
	 * @var \yii\data\IDataProvider the data provider for the view. This property is required.
	 */
	public $dataProvider;
	/**
	 * @var array the HTML attributes for the container of the rendering result of each data item.
	 * The "tag" element specifies the tag name of the container element and defaults to "div".
	 * If "tag" is false, it means no container element will be rendered.
	 */
	public $itemOptions = array();
	/**
	 * @var string|callback the name of the view for rendering each data item, or a callback (e.g. an anonymous function)
	 * for rendering each data item. If it specifies a view name, the following variables will
	 * be available in the view:
	 *
	 * - `$item`: mixed, the data item
	 * - `$key`: mixed, the key value associated with the data item
	 * - `$index`: integer, the zero-based index of the data item in the items array returned by [[dataProvider]].
	 * - `$widget`: ListView, this widget instance
	 *
	 * Note that the view name is resolved into the view file by the current context of the [[view]] object.
	 *
	 * If this property is specified as a callback, it should have the following signature:
	 *
	 * ~~~
	 * function ($item, $key, $index, $widget)
	 * ~~~
	 */
	public $itemView;
	/**
	 * @var string the HTML code to be displayed between any two consecutive items.
	 */
	public $separator = "\n";
	/**
	 * @var array the configuration for the pager widget. By default, [[LinkPager]] will be
	 * used to render the pager. You can use a different widget class by configuring the "class" element.
	 */
	public $pager = array();
	/**
	 * @var array the configuration for the sorter widget. By default, [[LinkSorter]] will be
	 * used to render the sorter. You can use a different widget class by configuring the "class" element.
	 */
	public $sorter = array();
	/**
	 * @var string the HTML content to be displayed as the summary of the list view.
	 * If you do not want to show the summary, you may set it with an empty string.
	 *
	 * The following tokens will be replaced with the corresponding values:
	 *
	 * - `{begin}`: the starting row number (1-based) currently being displayed
	 * - `{end}`: the ending row number (1-based) currently being displayed
	 * - `{count}`: the number of rows currently being displayed
	 * - `{totalCount}`: the total number of rows available
	 * - `{page}`: the page number (1-based) current being displayed
	 * - `{pageCount}`: the number of pages available
	 */
	public $summary;
	/**
	 * @var string the HTML content to be displayed when [[dataProvider]] does not have any data.
	 * If you do not want to show anything when [[dataProvider]] is empty, you may set this property with an empty string.
	 */
	public $empty;
	/**
	 * @var string the layout that determines how different sections of the list view should be organized.
	 * The following tokens will be replaced with the corresponding section contents:
	 *
	 * - `{summary}`: the summary section. See [[renderSummary()]].
	 * - `{items}`: the list items. See [[renderItems()]].
	 * - `{sorter}`: the sorter. See [[renderSorter()]].
	 * - `{pager}`: the pager. See [[renderPager()]].
	 */
	public $layout = "{summary}\n{sorter}\n{items}\n{pager}";


	/**
	 * Initializes the view.
	 */
	public function init()
	{
		if ($this->dataProvider === null) {
			throw new InvalidConfigException('The "dataProvider" property must be set.');
		}
		if ($this->empty === null) {
			$this->empty = '<div class="empty">' . Yii::t('yii', 'No results found.') . '</div>';
		}
	}

	/**
	 * Runs the widget.
	 */
	public function run()
	{
		if ($this->dataProvider->getCount() > 0) {
			$tag = ArrayHelper::remove($this->options, 'tag', 'div');
			$content = preg_replace_callback("/{\\w+}/", array($this, 'renderSection'), $this->layout);
			echo Html::tag($tag, $content, $this->options);
		} else {
			echo $this->empty;
		}
	}

	/**
	 * Renders a section of the list view.
	 * This method is invoked for every placeholder found in [[layout]].
	 * It should return the rendering result that would replace the placeholder.
	 * @param array $matches the matches, where $matches[0] represents the matching placeholder.
	 * @return string the rendering result of the section
	 */
	protected function renderSection($matches)
	{
		switch ($matches[0]) {
			case '{summary}': return $this->renderSummary();
			case '{items}': return $this->renderItems();
			case '{sorter}': return $this->renderSorter();
			case '{pager}': return $this->renderPager();
			default: return $matches[0];
		}
	}

	/**
	 * Renders the summary text.
	 */
	public function renderSummary()
	{
		$count = $this->dataProvider->getCount();
		if (($pagination = $this->dataProvider->getPagination()) !== false) {
			$totalCount = $this->dataProvider->getTotalCount();
			$begin = $pagination->getPage() * $pagination->pageSize + 1;
			$end = $begin + $count - 1;
			if ($end > $totalCount) {
				$end = $totalCount;
				$begin = $end - $count + 1;
			}
			$page = $pagination->getPage() + 1;
			$pageCount = $pagination->pageCount;
			if (($summaryContent = $this->summary) === null) {
				$summaryContent = '<div class="summary">' . Yii::t('yii', 'Total <b>1</b> result.|Showing <b>{begin}-{end}</b> of <b>{totalCount}</b> results.', $totalCount) . '</div>';
			}
		} else {
			$begin = $page = $pageCount = 1;
			$end = $totalCount = $count;
			if (($summaryContent = $this->summary) === null) {
				$summaryContent = '<div class="summary">' . Yii::t('yii', 'Total <b>1</b> result.|Total <b>{count}</b> results.', $count) . '</div>';
			}
		}
		return strtr($summaryContent, array(
			'{begin}' => $begin,
			'{end}' => $end,
			'{count}' => $count,
			'{totalCount}' => $totalCount,
			'{page}' => $page,
			'{pageCount}' => $pageCount,
		));
	}

	/**
	 * Renders all data items.
	 * @return string the rendering result
	 */
	public function renderItems()
	{
		$items = $this->dataProvider->getItems();
		$keys = $this->dataProvider->getKeys();
		$rows = array();
		foreach (array_values($items) as $index => $item) {
			$rows[] = $this->renderItem($item, $keys[$index], $index);
		}
		return implode($this->separator, $rows);
	}

	/**
	 * Renders a single data item.
	 * @param mixed $item the data item to be rendered
	 * @param mixed $key the key value associated with the data item
	 * @param integer $index the zero-based index of the data item in the item array returned by [[dataProvider]].
	 * @return string the rendering result
	 */
	public function renderItem($item, $key, $index)
	{
		if ($this->itemView === null) {
			$content = $key;
		} elseif (is_string($this->itemView)) {
			$content = $this->getView()->render($this->itemView, array(
				'item' => $item,
				'key' => $key,
				'index' => $index,
				'widget' => $this,
			));
		} else {
			$content = call_user_func($this->itemView, $item, $key, $index, $this);
		}
		$options = $this->itemOptions;
		$tag = ArrayHelper::remove($options, 'tag', 'div');
		if ($tag !== false) {
			$options['data-key'] = $key;
			return Html::tag($tag, $content, $options);
		} else {
			return $content;
		}
	}

	/**
	 * Renders the sorter.
	 * @return string the rendering result
	 */
	public function renderSorter()
	{
		$sort = $this->dataProvider->getSort();
		if ($sort === false || empty($sort->attributes) || $this->dataProvider->getCount() <= 0) {
			return '';
		}
		/** @var LinkSorter $class */
		$class = ArrayHelper::remove($this->sorter, 'class', LinkSorter::className());
		$this->sorter['sort'] = $sort;
		return $class::widget($this->sorter);
	}

	/**
	 * Renders the pager.
	 * @return string the rendering result
	 */
	public function renderPager()
	{
		$pagination = $this->dataProvider->getPagination();
		if ($pagination === false || $this->dataProvider->getCount() <= 0) {
			return '';
		}
		/** @var LinkPager $class */
		$class = ArrayHelper::remove($this->sorter, 'class', LinkPager::className());
		$this->pager['pagination'] = $pagination;
		return $class::widget($this->pager);
	}
}
