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
	 * @var array list of sections in the accordion widget. Each array element represents a single
	 * section with the following structure:
	 *
	 * ```php
	 * array(
	 *     // required, the header (HTML) of the section
	 *     'header' => 'Section label',
	 *     // required, the content (HTML) of the section
	 *     'content' => 'Mauris mauris ante, blandit et, ultrices a, suscipit eget...',
	 *     // optional the HTML attributes of the section content container
	 *     'options'=> array(...),
	 *     // optional the HTML attributes of the section header container
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
		echo $this->renderSections() . "\n";
		echo Html::endTag('div') . "\n";
		$this->registerWidget('accordion');
	}

	/**
	 * Renders collapsible sections as specified on [[items]].
	 * @return string the rendering result.
	 * @throws InvalidConfigException.
	 */
	protected function renderSections()
	{
		$sections = array();
		foreach ($this->items as $item) {
			if (!isset($item['header'])) {
				throw new InvalidConfigException("The 'header' option is required.");
			}
			if (!isset($item['content'])) {
				throw new InvalidConfigException("The 'content' option is required.");
			}
			$headerOptions = ArrayHelper::getValue($item, 'headerOptions', array());
			$sections[] = Html::tag('h3', $item['header'], $headerOptions);
			$options = ArrayHelper::getValue($item, 'options', array());
			$sections[] = Html::tag('div', $item['content'], $options);;
		}

		return implode("\n", $sections);
	}
}
