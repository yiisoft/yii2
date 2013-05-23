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
 * Tabs renders an tabs jQuery UI widget.
 *
 * For example:
 *
 * ```php
 * echo Tabs::widget(array(
 *     'items' => array(
 *         'One' => array(
 *             'content' => 'Mauris mauris ante, blandit et, ultrices a, suscipit eget...',
 *             'contentOptions' => array(...),
 *         ),
 *         'Two' => array(
 *             'content' => 'Sed non urna. Phasellus eu ligula. Vestibulum sit amet purus...',
 *             'headerOptions' => array(...),
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
	 * // item key is the actual tab header label
	 * 'Tab header label' => array(
	 *     // required, the content (HTML) of the tab
	 *     'content' => 'Mauris mauris ante, blandit et, ultrices a, suscipit eget...',
	 *     // optional the HTML attributes of the tab content container
	 *     'contentOptions'=> array(...),
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
		$headers = array();
		$contents = array();
		$index = 0;
		foreach ($this->items as $header => $item) {
			$id = $this->options['id'] . '-tab' . ++$index;
			$headerOptions = ArrayHelper::getValue($item, 'headerOptions', array());
			$headers[] = Html::tag('li', Html::tag('a', $header, array('href' => "#$id")), $headerOptions);
			if (isset($item['content'])) {
				$contentOptions = ArrayHelper::getValue($item, 'contentOptions', array());
				$contentOptions['id'] = $id;
				$contents[] = Html::tag('div', $item['content'], $contentOptions);
			} else {
				throw new InvalidConfigException("The 'content' option is required.");
			}
		}
		echo Html::tag('ul', implode("\n", $headers)) . "\n";
		echo implode("\n", $contents) . "\n";
		echo Html::endTag('div') . "\n";
		$this->registerWidget('tabs');
	}
}
