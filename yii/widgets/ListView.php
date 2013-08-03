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
	 * @var array the HTML attributes for the container of the rendering result of each data model.
	 * The "tag" element specifies the tag name of the container element and defaults to "div".
	 * If "tag" is false, it means no container element will be rendered.
	 */
	public $itemOptions = array();
	/**
	 * @var string|callback the name of the view for rendering each data item, or a callback (e.g. an anonymous function)
	 * for rendering each data item. If it specifies a view name, the following variables will
	 * be available in the view:
	 *
	 * - `$model`: mixed, the data model
	 * - `$key`: mixed, the key value associated with the data item
	 * - `$index`: integer, the zero-based index of the data item in the items array returned by [[dataProvider]].
	 * - `$widget`: ListView, this widget instance
	 *
	 * Note that the view name is resolved into the view file by the current context of the [[view]] object.
	 *
	 * If this property is specified as a callback, it should have the following signature:
	 *
	 * ~~~
	 * function ($model, $key, $index, $widget)
	 * ~~~
	 */
	public $itemView;
	/**
	 * @var string the HTML code to be displayed between any two consecutive items.
	 */
	public $separator = "\n";


	/**
	 * Renders all data models.
	 * @return string the rendering result
	 */
	public function renderItems()
	{
		$models = $this->dataProvider->getModels();
		$keys = $this->dataProvider->getKeys();
		$rows = array();
		foreach (array_values($models) as $index => $model) {
			$rows[] = $this->renderItem($model, $keys[$index], $index);
		}
		return implode($this->separator, $rows);
	}

	/**
	 * Renders a single data model.
	 * @param mixed $model the data model to be rendered
	 * @param mixed $key the key value associated with the data model
	 * @param integer $index the zero-based index of the data model in the model array returned by [[dataProvider]].
	 * @return string the rendering result
	 */
	public function renderItem($model, $key, $index)
	{
		if ($this->itemView === null) {
			$content = $key;
		} elseif (is_string($this->itemView)) {
			$content = $this->getView()->render($this->itemView, array(
				'model' => $model,
				'key' => $key,
				'index' => $index,
				'widget' => $this,
			));
		} else {
			$content = call_user_func($this->itemView, $model, $key, $index, $this);
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
