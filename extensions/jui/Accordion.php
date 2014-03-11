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
 * echo Accordion::widget([
 *     'items' => [
 *         [
 *             'header' => 'Section 1',
 *             'content' => 'Mauris mauris ante, blandit et, ultrices a, suscipit eget...',
 *         ],
 *         [
 *             'header' => 'Section 2',
 *             'headerOptions' => ['tag' => 'h3'],
 *             'content' => 'Sed non urna. Phasellus eu ligula. Vestibulum sit amet purus...',
 *             'options' => ['tag' => 'div'],
 *         ],
 *     ],
 *     'options' => ['tag' => 'div'],
 *     'itemOptions' => ['tag' => 'div'],
 *     'headerOptions' => ['tag' => 'h3'],
 *     'clientOptions' => ['collapsible' => false],
 * ]);
 * ```
 *
 * @see http://api.jqueryui.com/accordion/
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class Accordion extends Widget
{
	/**
	 * @var array the HTML attributes for the widget container tag. The following special options are recognized:
	 *
	 * - tag: string, defaults to "div", the tag name of the container tag of this widget
	 *
	 * See [[\yii\helpers\Html::renderTagAttributes()]] for details on how attributes are being rendered.
	 */
	public $options = [];
	/**
	 * @var array list of collapsible items. Each item can be an array of the following structure:
	 *
	 * ~~~
	 * [
	 *     'header' => 'Item header',
	 *     'content' => 'Item content',
	 *     // the HTML attributes of the item header container tag. This will overwrite "headerOptions".
	 *     'headerOptions' => [],
	 *     // the HTML attributes of the item container tag. This will overwrite "itemOptions".
	 *     'options' => [],
	 * ]
	 * ~~~
	 */
	public $items = [];
	/**
	 * @var array list of HTML attributes for the item container tags. This will be overwritten
	 * by the "options" set in individual [[items]]. The following special options are recognized:
	 *
	 * - tag: string, defaults to "div", the tag name of the item container tags.
	 *
	 * See [[\yii\helpers\Html::renderTagAttributes()]] for details on how attributes are being rendered.
	 */
	public $itemOptions = [];
	/**
	 * @var array list of HTML attributes for the item header container tags. This will be overwritten
	 * by the "headerOptions" set in individual [[items]]. The following special options are recognized:
	 *
	 * - tag: string, defaults to "h3", the tag name of the item container tags.
	 *
	 * See [[\yii\helpers\Html::renderTagAttributes()]] for details on how attributes are being rendered.
	 */
	public $headerOptions = [];


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
		$this->registerWidget('accordion', AccordionAsset::className());
	}

	/**
	 * Renders collapsible items as specified on [[items]].
	 * @return string the rendering result.
	 * @throws InvalidConfigException.
	 */
	protected function renderItems()
	{
		$items = [];
		foreach ($this->items as $item) {
			if (!isset($item['header'])) {
				throw new InvalidConfigException("The 'header' option is required.");
			}
			if (!isset($item['content'])) {
				throw new InvalidConfigException("The 'content' option is required.");
			}
			$headerOptions = array_merge($this->headerOptions, ArrayHelper::getValue($item, 'headerOptions', []));
			$headerTag = ArrayHelper::remove($headerOptions, 'tag', 'h3');
			$items[] = Html::tag($headerTag, $item['header'], $headerOptions);
			$options = array_merge($this->itemOptions, ArrayHelper::getValue($item, 'options', []));
			$tag = ArrayHelper::remove($options, 'tag', 'div');
			$items[] = Html::tag($tag, $item['content'], $options);
		}

		return implode("\n", $items);
	}
}
