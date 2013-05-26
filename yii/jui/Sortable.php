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
 *         'Item 2',
 *         'Item 3',
 *     ),
 *     'clientOptions' => array(
 *         'cursor' => 'move',
 *     ),
 * ));
 * ```
 *
 * The following example will show the content enclosed between the [[begin()]]
 * and [[end()]] calls within the sortable widget:
 *
 * ```php
 * Sortable::begin(array(
 *     'options' => array(
 *         'tag' => 'div',
 *     ),
 *     'itemOptions' => array(
 *         'tag' => 'div',
 *     ),
 *     'clientOptions' => array(
 *         'cursor' => 'move',
 *     ),
 * ));
 *
 * echo 'Item 1';
 * echo 'Item 2';
 * echo 'Item 3';
 *
 * Sortable::end();
 * ```
 *
 * @see http://api.jqueryui.com/sortable/
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class Sortable extends Widget
{
	/**
	 * @var array.
	 * @todo comments
	 */
	public $items = array();
	/**
	 * @var array.
	 * @todo comments
	 */
	public $itemOptions = array();


	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		parent::init();
		$options = $this->options;
		$tag = ArrayHelper::remove($options, 'tag', 'ul');
		echo Html::beginTag($tag, $options) . "\n";
	}

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo $this->renderItems() . "\n";
		$tag = ArrayHelper::getValue($this->options, 'tag', 'ul');
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
