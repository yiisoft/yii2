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
 * Accordion renders an accordion jQuery UI widget.
 *
 * For example:
 *
 * ```php
 * echo Accordion::widget(array(
 *     'items' => array(
 *         array(
 *             'header' => 'Section 1',
 *             'content' => 'Mauris mauris ante, blandit et, ultrices a, suscipit eget...',
 *         ),
 *         array(
 *             'header' => 'Section 2',
 *             'headerOptions' => array(...),
 *             'content' => 'Sed non urna. Phasellus eu ligula. Vestibulum sit amet purus...',
 *             'options' => array(...),
 *         ),
 *     ),
 * ));
 * ```
 *
 * @see http://api.jqueryui.com/accordion/
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class Accordion extends Widget
{
	/**
	 * @var array.
	 * @todo comments.
	 */
	public $items = array();
	/**
	 * @var array.
	 * @todo comments.
	 */
	public $itemOptions = array();
	/**
	 * @var array.
	 * @todo comments.
	 */
	public $headerOptions = array();


	/**
	 * Renders the widget.
	 */
	public function run()
	{
		$options = $this->options;
		$tag = ArrayHelper::remove($options, 'tag', 'div');
		echo Html::beginTag($tag, $options) . "\n";
		echo $this->renderItems() . "\n";
		echo Html::endTag($tag) . "\n";
		$this->registerWidget('accordion');
	}

	/**
	 * Renders collapsible sections as specified on [[items]].
	 * @return string the rendering result.
	 * @throws InvalidConfigException.
	 */
	protected function renderItems()
	{
		$items = array();
		foreach ($this->items as $item) {
			if (!isset($item['header'])) {
				throw new InvalidConfigException("The 'header' option is required.");
			}
			if (!isset($item['content'])) {
				throw new InvalidConfigException("The 'content' option is required.");
			}
			$headerOptions = array_merge($this->headerOptions, ArrayHelper::getValue($item, 'headerOptions', array()));
			$headerTag = ArrayHelper::remove($headerOptions, 'tag', ArrayHelper::remove($headerOptions, 'tag', 'h3'));
			$items[] = Html::tag($headerTag, $item['header'], $headerOptions);
			$options = array_merge($this->itemOptions, ArrayHelper::getValue($item, 'options', array()));
			$tag = ArrayHelper::remove($options, 'tag', ArrayHelper::remove($options, 'tag', 'div'));
			$items[] = Html::tag($tag, $item['content'], $options);;
		}

		return implode("\n", $items);
	}
}
