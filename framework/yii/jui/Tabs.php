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
 * Tabs renders a tabs jQuery UI widget.
 *
 * For example:
 *
 * ```php
 * echo Tabs::widget(array(
 *     'items' => array(
 *         array(
 *             'label' => 'Tab one',
 *             'content' => 'Mauris mauris ante, blandit et, ultrices a, suscipit eget...',
 *         ),
 *         array(
 *             'label' => 'Tab two',
 *             'content' => 'Sed non urna. Phasellus eu ligula. Vestibulum sit amet purus...',
 *             'options' => array(
 *                 'tag' => 'div',
 *             ),
 *         ),
 *         array(
 *             'label' => 'Tab three',
 *             'content' => 'Specific content...',
 *             'options' => array(
 *                 'id' => 'my-tab',
 *             ),
 *         ),
 *         array(
 *             'label' => 'Ajax tab',
 *             'url' => 'http://www.yiiframework.com',
 *         ),
 *     ),
 *     'options' => array(
 *         'tag' => 'div',
 *     ),
 *     'itemOptions' => array(
 *         'tag' => 'div',
 *     ),
 *     'clientOptions' => array(
 *         'collapsible' => false,
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
	public $options = array();
	public $items = array();
	public $itemOptions = array();
	public $headerTemplate = '<li><a href="{url}">{label}</a></li>';


	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo Html::beginTag('div', $this->options) . "\n";
		echo $this->renderItems() . "\n";
		echo Html::endTag('div') . "\n";
		$this->registerWidget('tabs');
	}

	/**
	 * Renders tab items as specified on [[items]].
	 * @return string the rendering result.
	 * @throws InvalidConfigException.
	 * @todo rework
	 */
	protected function renderItems()
	{
		$headers = array();
		$items = array();
		foreach ($this->items as $n => $item) {
			if (!isset($item['header'])) {
				throw new InvalidConfigException("The 'header' option is required.");
			}
			if (!isset($item['content'])) {
				throw new InvalidConfigException("The 'content' option is required.");
			}
			$options = ArrayHelper::getValue($item, 'options', array());
			if (!isset($options['id'])) {
				$options['id'] = $this->options['id'] . '-tab' . $n;
			}
			$headerOptions = ArrayHelper::getValue($item, 'headerOptions', array());
			$headers[] = Html::tag('li', Html::a($item['header'], '#' . $options['id']), $headerOptions);
			$items[] = Html::tag('div', $item['content'], $options);
		}
		return Html::tag('ul', implode("\n", $headers)) . "\n" . implode("\n", $items);
	}
}
