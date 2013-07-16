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
 * Nav renders a nav HTML component.
 *
 * For example:
 *
 * ```php
 * echo Nav::widget(array(
 *     'items' => array(
 *         array(
 *             'label' => 'Home',
 *             'url' => '/',
 *             'linkOptions' => array(...),
 *             'active' => true,
 *         ),
 *         array(
 *             'label' => 'Dropdown',
 *             'items' => array(
 *                  array(
 *                      'label' => 'Level 1 -DropdownA',
 *                      'url' => '#',
 *                      'items' => array(
 *                          array(
 *                              'label' => 'Level 2 -DropdownA',
 *                              'url' => '#',
 *                          ),
 *                      ),
 *                  ),
 *                  array(
 *                      'label' => 'Level 1 -DropdownB',
 *                      'url' => '#',
 *                  ),
 *             ),
 *         ),
 *     ),
 * ));
 * ```
 *
 * @see http://twitter.github.io/bootstrap/components.html#nav
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class Nav extends Widget
{
	/**
	 * @var array list of items in the nav widget. Each array element represents a single
	 * menu item with the following structure:
	 *
	 * - label: string, required, the nav item label.
	 * - url: optional, the item's URL. Defaults to "#".
	 * - linkOptions: array, optional, the HTML attributes of the item's link.
	 * - options: array, optional, the HTML attributes of the item container (LI).
	 * - active: boolean, optional, whether the item should be on active state or not.
	 * - dropdown: array|string, optional, the configuration array for creating a [[Dropdown]] widget,
	 *   or a string representing the dropdown menu. Note that Bootstrap does not support sub-dropdown menus.
	 */
	public $items = array();
	/**
	 * @var boolean whether the nav items labels should be HTML-encoded.
	 */
	public $encodeLabels = true;


	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		parent::init();
		Html::addCssClass($this->options, 'nav');
	}

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo $this->renderItems();
		BootstrapAsset::register($this->getView());
	}

	/**
	 * Renders widget items.
	 */
	public function renderItems()
	{
		$items = array();
		foreach ($this->items as $item) {
			$items[] = $this->renderItem($item);
		}

		return Html::tag('ul', implode("\n", $items), $this->options);
	}

	/**
	 * Renders a widget's item.
	 * @param mixed $item the item to render.
	 * @return string the rendering result.
	 * @throws InvalidConfigException
	 */
	public function renderItem($item)
	{
		if (is_string($item)) {
			return $item;
		}
		if (!isset($item['label'])) {
			throw new InvalidConfigException("The 'label' option is required.");
		}
		$label = $this->encodeLabels ? Html::encode($item['label']) : $item['label'];
		$options = ArrayHelper::getValue($item, 'options', array());
		$items = ArrayHelper::getValue($item, 'items');
		$url = Html::url(ArrayHelper::getValue($item, 'url', '#'));
		$linkOptions = ArrayHelper::getValue($item, 'linkOptions', array());

		if (ArrayHelper::getValue($item, 'active')) {
			Html::addCssClass($options, 'active');
		}

		if ($items !== null) {
			$linkOptions['data-toggle'] = 'dropdown';
			Html::addCssClass($options, 'dropdown');
			Html::addCssClass($urlOptions, 'dropdown-toggle');
			$label .= ' ' . Html::tag('b', '', array('class' => 'caret'));
			if (is_array($items)) {
				$items = Dropdown::widget(array(
					'items' => $items,
					'clientOptions' => false,
				));
			}
		}

		return Html::tag('li', Html::a($label, $url, $linkOptions) . $items, $options);
	}
}
