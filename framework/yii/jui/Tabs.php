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
 * Tabs renders a tabs jQuery UI widget.
 *
 * For example:
 *
 * ```php
 * echo Tabs::widget(array(
 *     'items' => array(
 *         array(
 *             'header' => 'One',
 *             'content' => 'Mauris mauris ante, blandit et, ultrices a, suscipit eget...',
 *         ),
 *         array(
 *             'header' => 'Two',
 *             'headerOptions' => array(...),
 *             'content' => 'Sed non urna. Phasellus eu ligula. Vestibulum sit amet purus...',
 *             'options' => array(...),
 *         ),
 *     ),
 * ));
 * ```
 *
 * @see http://api.jqueryui.com/tabs/
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class Tabs extends Widget
{
	/**
	 * @var array list of tabs in the tabs widget. Each array element represents a single
	 * tab with the following structure:
	 *
	 * ```php
	 * array(
	 *     // required, the header (HTML) of the tab
	 *     'header' => 'Tab label',
	 *     // required, the content (HTML) of the tab
	 *     'content' => 'Mauris mauris ante, blandit et, ultrices a, suscipit eget...',
	 *     // optional the HTML attributes of the tab content container
	 *     'options'=> array(...),
	 *     // optional the HTML attributes of the tab header container
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
		echo $this->renderHeaders() . "\n";
		echo $this->renderContents() . "\n";
		echo Html::endTag('div') . "\n";
		$this->registerWidget('tabs');
	}

	/**
	 * Renders tabs headers as specified on [[items]].
	 * @return string the rendering result.
	 * @throws InvalidConfigException.
	 */
	protected function renderHeaders()
	{
		$headers = array();
		foreach ($this->items as $n => $item) {
			if (!isset($item['header'])) {
				throw new InvalidConfigException("The 'header' option is required.");
			}
			$options = ArrayHelper::getValue($item, 'options', array());
			$id = isset($options['id']) ? $options['id'] : $this->options['id'] . '-tab' . $n;
			$headerOptions = ArrayHelper::getValue($item, 'headerOptions', array());
			$headers[] = Html::tag('li', Html::a($item['header'], "#$id"), $headerOptions);
		}

		return Html::tag('ul', implode("\n", $headers));
	}

	/**
	 * Renders tabs contents as specified on [[items]].
	 * @return string the rendering result.
	 * @throws InvalidConfigException.
	 */
	protected function renderContents()
	{
		$contents = array();
		foreach ($this->items as $n => $item) {
			if (!isset($item['content'])) {
				throw new InvalidConfigException("The 'content' option is required.");
			}
			$options = ArrayHelper::getValue($item, 'options', array());
			if (!isset($options['id'])) {
				$options['id'] = $this->options['id'] . '-tab' . $n;
			}
			$contents[] = Html::tag('div', $item['content'], $options);
		}

		return implode("\n", $contents);
	}
}
