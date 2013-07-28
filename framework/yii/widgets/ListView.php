<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ListView extends ListViewBase
{
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
}
