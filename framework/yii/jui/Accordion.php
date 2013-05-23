<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jui;

use yii\base\InvalidConfigException;
use yii\helpers\base\ArrayHelper;
use yii\helpers\Html;

/**
 * Accordion renders an accordion jQuery UI widget.
 *
 * For example:
 *
 * ```php
 * echo Accordion::widget(array(
 *     'items' => array(
 *         'Section 1' => array(
 *             'content' => 'Mauris mauris ante, blandit et, ultrices a, suscipit eget...',
 *             'contentOptions' => array(...),
 *         ),
 *         'Section 2' => array(
 *             'content' => 'Sed non urna. Phasellus eu ligula. Vestibulum sit amet purus...',
 *             'headerOptions' => array(...),
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
	 * @var array list of groups in the collapse widget. Each array element represents a single
	 * group with the following structure:
	 *
	 * ```php
	 * // item key is the actual group header
	 * 'Section' => array(
	 *     // required, the content (HTML) of the group
	 *     'content' => 'Mauris mauris ante, blandit et, ultrices a, suscipit eget...',
	 *     // optional the HTML attributes of the content group
	 *     'contentOptions'=> array(...),
	 *     // optional the HTML attributes of the header group
	 *     'headerOptions'=> array(...),
	 * )
	 * ```
	 */
	public $items = array();


	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo Html::beginTag('div', $this->options) . "\n";
		echo $this->renderItems() . "\n";
		echo Html::endTag('div') . "\n";
		$this->registerWidget('accordion');
	}

	/**
	 * Renders collapsible items as specified on [[items]].
	 * @return string the rendering result.
	 */
	public function renderItems()
	{
		$items = array();
		foreach ($this->items as $header => $item) {
			$items[] = $this->renderItem($header, $item);
		}

		return implode("\n", $items);
	}

	/**
	 * Renders a single collapsible item group.
	 * @param string $header a label of the item group [[items]].
	 * @param array $item a single item from [[items]].
	 * @return string the rendering result.
	 * @throws InvalidConfigException.
	 */
	public function renderItem($header, $item)
	{
		if (isset($item['content'])) {
			$contentOptions = ArrayHelper::getValue($item, 'contentOptions', array());
			$content = Html::tag('div', $item['content']) . "\n";
		} else {
			throw new InvalidConfigException("The 'content' option is required.");
		}

		$group = array();
		$headerOptions = ArrayHelper::getValue($item, 'headerOptions', array());
		$group[] = Html::tag('h3', $header, $headerOptions);
		$group[] = Html::tag('div', $content, $contentOptions);

		return implode("\n", $group);
	}
}
