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
 * Dropdown renders a Tab bootstrap javascript component.
 *
 * @see http://twitter.github.io/bootstrap/javascript.html#dropdowns
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class Dropdown extends Widget
{
	/**
	 * @var array list of menu items in the dropdown. Each array element represents a single
	 * menu with the following structure:
	 * - label: string, required, the label of the item link
	 * - url: string, optional, the url of the item link. Defaults to "#".
	 * - linkOptions: array, optional, the HTML attributes of the item link.
	 * - options: array, optional, the HTML attributes of the item.
	 * - items: array, optional, the dropdown items configuration array. if `items` is set, then `url` of the parent
	 *   item will be ignored and automatically set to "#"
	 *
	 * @see https://github.com/twitter/bootstrap/issues/5050#issuecomment-11741727
	 */
	public $items = array();
	/**
	 * @var boolean whether the labels for header items should be HTML-encoded.
	 */
	public $encodeLabels = true;


	/**
	 * Initializes the widget.
	 * If you override this method, make sure you call the parent implementation first.
	 */
	public function init()
	{
		parent::init();
		$this->addCssClass($this->options, 'dropdown-menu');
	}

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo $this->renderItems() . "\n";
		$this->registerPlugin('dropdown');
	}

	/**
	 * Renders dropdown items as specified on [[items]].
	 * @return string the rendering result.
	 * @throws InvalidConfigException
	 */
	protected function renderItems()
	{
		$items = array();
		foreach ($this->items as $item) {
			if (is_string($item)) {
				$items[] = $item;
				continue;
			}
			if (!isset($item['label'])) {
				throw new InvalidConfigException("The 'label' option is required.");
			}
			$label = $this->encodeLabels ? Html::encode($item['label']) : $item['label'];
			$options = ArrayHelper::getValue($item, 'options', array());
			$linkOptions = ArrayHelper::getValue($item, 'linkOptions', array());
			$linkOptions['tabindex'] = '-1';

			if (isset($item['items'])) {
				$this->addCssClass($options, 'dropdown-submenu');
				$content = Html::a($label, '#', $linkOptions) . $this->dropdown($item['items']);
			} else {
				$content = Html::a($label, ArrayHelper::getValue($item, 'url', '#'), $linkOptions);
			}
			$items[] = Html::tag('li', $content , $options);
		}

		return Html::tag('ul', implode("\n", $items), $this->options);
	}

	/**
	 * Generates a dropdown menu.
	 * @param array $items the configuration of the dropdown items. See [[items]].
	 * @return string the generated dropdown menu
	 * @see items
	 */
	protected function dropdown($items)
	{
		return Dropdown::widget(array('items' => $items, 'clientOptions' => false));
	}
}