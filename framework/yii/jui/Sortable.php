<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jui;

use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Sortable renders a sortable jQuery UI widget.
 *
 * For example:
 *
 * ```php
 * echo Sortable::widget(array(
 *     'items' => array(
 *         'Item 1',
 *         array(
 *             'content' => 'Item2',
 *         ),
 *         array(
 *             'content' => 'Item3',
 *             'options' => array(
 *                 'tag' => 'li',
 *             ),
 *         ),
 *     ),
 *     'options' => array(
 *         'tag' => 'ul',
 *     ),
 *     'itemOptions' => array(
 *         'tag' => 'li',
 *     ),
 *     'clientOptions' => array(
 *         'cursor' => 'move',
 *     ),
 * ));
 * ```
 *
 * @see http://api.jqueryui.com/sortable/
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class Sortable extends Widget
{
	/**
	 * @var array list of sortable containers.
	 */
	public $items = array();
	/**
	 * @var array list of individual sortable container default options.
	 */
	public $itemOptions = array();


	/**
	 * Renders the widget.
	 */
	public function run()
	{
		$options = $this->options;
		$tag = ArrayHelper::remove($options, 'tag', 'ul');
		echo Html::beginTag($tag, $options) . "\n";
		echo $this->renderItems() . "\n";
		echo Html::endTag($tag) . "\n";
		$this->registerWidget('sortable', false);
	}

	/**
	 * Renders sortable items as specified on [[items]].
	 * @return string the rendering result.
	 * @throws InvalidConfigException.
	 */
	public function renderItems()
	{
		$items = array();
		foreach ($this->items as $item) {
			$options = $this->itemOptions;
			$tag = ArrayHelper::remove($options, 'tag', 'li');
			if (is_array($item)) {
				if (!isset($item['content'])) {
					throw new InvalidConfigException("The 'content' option is required.");
				}
				$options = array_merge($options, ArrayHelper::getValue($item, 'options', array()));
				$tag = ArrayHelper::remove($options, 'tag', $tag);
				$items[] = Html::tag($tag, $item['content'], $options);
			} else {
				$items[] = Html::tag($tag, $item, $options);
			}
		}
		return implode("\n", $items);
	}
}
