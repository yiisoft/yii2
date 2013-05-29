<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Tabs renders a Tab bootstrap javascript component.
 *
 * For example:
 *
 * ```php
 * echo Tabs::widget(array(
 *     'items' => array(
 *         array(
 *             'label' => 'One',
 *             'content' => 'Anim pariatur cliche...',
 *             'active' => true
 *         ),
 *         array(
 *             'label' => 'Two',
 *             'headerOptions' => array(...),
 *             'content' => 'Anim pariatur cliche...',
 *             'options' => array('id'=>'myveryownID'),
 *         ),
 *         array(
 *             'label' => 'Dropdown',
 *             'dropdown' => array(
 *                  array(
 *                      'label' => 'DropdownA',
 *                      'content' => 'DropdownA, Anim pariatur cliche...',
 *                  ),
 *                  array(
 *                      'label' => 'DropdownB',
 *                      'content' => 'DropdownB, Anim pariatur cliche...',
 *                  ),
 *             ),
 *         ),
 *     ),
 * ));
 * ```
 *
 * @see http://twitter.github.io/bootstrap/javascript.html#tabs
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class Tabs extends Widget
{
	/**
	 * @var array list of tabs in the tabs widget. Each array element represents a single
	 * tab with the following structure:
	 *
	 * - label: string, required, the tab header label.
	 * - headerOptions: array, optional, the HTML attributes of the tab header.
	 * - content: array, required if `items` is not set. The content (HTML) of the tab pane.
	 * - options: array, optional, the HTML attributes of the tab pane container.
	 * - active: boolean, optional, whether the item tab header and pane should be visibles or not.
	 * - items: array, optional, if not set then `content` will be required. The `items` specify a dropdown items
	 *   configuration array. Items can also hold two extra keys:
	 *   - active: boolean, optional, whether the item tab header and pane should be visibles or not.
	 *   - content: string, required if `items` is not set. The content (HTML) of the tab pane.
	 *   - contentOptions: optional, array, the HTML attributes of the tab content container.
	 */
	public $items = array();
	/**
	 * @var array list of HTML attributes for the item container tags. This will be overwritten
	 * by the "options" set in individual [[items]]. The following special options are recognized:
	 *
	 * - tag: string, defaults to "div", the tag name of the item container tags.
	 */
	public $itemOptions = array();
	/**
	 * @var array list of HTML attributes for the header container tags. This will be overwritten
	 * by the "headerOptions" set in individual [[items]].
	 */
	public $headerOptions = array();
	/**
	 * @var boolean whether the labels for header items should be HTML-encoded.
	 */
	public $encodeLabels = true;


	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		parent::init();
		$this->addCssClass($this->options, 'nav nav-tabs');

	}

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo $this->renderItems();
		$this->registerPlugin('tab');
	}

	/**
	 * Renders tab items as specified on [[items]].
	 * @return string the rendering result.
	 * @throws InvalidConfigException.
	 */
	protected function renderItems()
	{
		$headers = array();
		$panes = array();
		foreach ($this->items as $n => $item) {
			if (!isset($item['label'])) {
				throw new InvalidConfigException("The 'label' option is required.");
			}
			if (!isset($item['content']) && !isset($item['items'])) {
				throw new InvalidConfigException("The 'content' option is required.");
			}
			$label = $this->label($item['label']);
			$headerOptions = $this->mergedOptions($item, 'headerOptions');

			if (isset($item['items'])) {
				$label .= ' <b class="caret"></b>';
				$this->addCssClass($headerOptions, 'dropdown');

				if ($this->normalizeItems($item['items'], $panes)) {
					$this->addCssClass($headerOptions, 'active');
				}

				$header = Html::a($label, "#", array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown')) . "\n";
				$header .= Dropdown::widget(array('items' => $item['items'], 'clientOptions' => false));

			} else {
				$options = $this->mergedOptions($item, 'itemOptions', 'options');
				$options['id'] = ArrayHelper::getValue($options, 'id', $this->options['id'] . '-tab' . $n);

				$this->addCssClass($options, 'tab-pane');
				if (ArrayHelper::remove($item, 'active')) {
					$this->addCssClass($options, 'active');
					$this->addCssClass($headerOptions, 'active');
				}
				$header = Html::a($label, '#' . $options['id'], array('data-toggle' => 'tab', 'tabindex' => '-1'));
				$panes[] = Html::tag('div', $item['content'], $options);

			}
			$headers[] = Html::tag('li', $header, array_merge($this->headerOptions, $headerOptions));
		}

		return Html::tag('ul', implode("\n", $headers), $this->options) . "\n" .
		Html::tag('div', implode("\n", $panes), array('class' => 'tab-content'));
	}

	/**
	 * Returns encoded if specified on [[encodeLabels]], original string otherwise.
	 * @param string $content the label text to encode or return
	 * @return string the resulting label.
	 */
	protected function label($content)
	{
		return $this->encodeLabels ? Html::encode($content) : $content;
	}

	/**
	 * Returns array of options merged with specified attribute array. The availabel options are:
	 *  - [[itemOptions]]
	 *  - [[headerOptions]]
	 * @param array $item the item to merge the options with
	 * @param string $name the property name.
	 * @param string $key the key to extract. If null, it is assumed to be the same as `$name`.
	 * @return array the merged array options.
	 */
	protected function mergedOptions($item, $name, $key = null)
	{
		if ($key === null) {
			$key = $name;
		}
		return array_merge($this->{$name}, ArrayHelper::getValue($item, $key, array()));
	}

	/**
	 * Normalizes dropdown item options by removing tab specific keys `content` and `contentOptions`, and also
	 * configure `panes` accordingly.
	 * @param array $items the dropdown items configuration.
	 * @param array $panes the panes reference array.
	 * @return boolean whether any of the dropdown items is `active` or not.
	 * @throws InvalidConfigException
	 */
	protected function normalizeItems(&$items, &$panes)
	{
		$itemActive = false;

		foreach ($items as $n => &$item) {
			if (is_string($item)) {
				continue;
			}
			if (!isset($item['content']) && !isset($item['items'])) {
				throw new InvalidConfigException("The 'content' option is required.");
			}

			$content = ArrayHelper::remove($item, 'content');
			$options = ArrayHelper::remove($item, 'contentOptions', array());
			$this->addCssClass($options, 'tab-pane');
			if (ArrayHelper::remove($item, 'active')) {
				$this->addCssClass($options, 'active');
				$this->addCssClass($item['options'], 'active');
				$itemActive = true;
			}

			$options['id'] = ArrayHelper::getValue($options, 'id', $this->options['id'] . '-dd-tab' . $n);
			$item['url'] = '#' . $options['id'];
			$item['linkOptions']['data-toggle'] = 'tab';

			$panes[] = Html::tag('div', $content, $options);

			unset($item);
		}
		return $itemActive;
	}
}