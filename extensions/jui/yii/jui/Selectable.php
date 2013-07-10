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
 * Selectable renders a selectable jQuery UI widget.
 *
 * For example:
 *
 * ```php
 * echo Selectable::widget(array(
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
 *         'tolerance' => 'fit',
 *     ),
 * ));
 * ```
 *
 * @see http://api.jqueryui.com/selectable/
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class Selectable extends Widget
{
	/**
	 * @var array the HTML attributes for the widget container tag. The following special options are recognized:
	 *
	 * - tag: string, defaults to "ul", the tag name of the container tag of this widget
	 */
	public $options = array();
	/**
	 * @var array list of selectable items. Each item can be a string representing the item content
	 * or an array of the following structure:
	 *
	 * ~~~
	 * array(
	 *     'content' => 'item content',
	 *     // the HTML attributes of the item container tag. This will overwrite "itemOptions".
	 *     'options' => array(),
	 * )
	 * ~~~
	 */
	public $items = array();
	/**
	 * @var array list of HTML attributes for the item container tags. This will be overwritten
	 * by the "options" set in individual [[items]]. The following special options are recognized:
	 *
	 * - tag: string, defaults to "li", the tag name of the item container tags.
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
		$this->registerWidget('selectable');
	}

	/**
	 * Renders selectable items as specified on [[items]].
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
