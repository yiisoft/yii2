<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use yii\base\InvalidConfigException;
use yii\helpers\base\ArrayHelper;
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
 *             'options' => array(...),
 *             'active' => true,
 *         ),
 *         array(
 *             'label' => 'Dropdown',
 *             'dropdown' => array(
 *                  array(
 *                      'label' => 'DropdownA',
 *                      'url' => '#',
 *                  ),
 *                  array(
 *                      'label' => 'DropdownB',
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
	 * ```php
	 * array(
	 *     // required, the menu item label.
	 *     'label' => 'Nav item label',
	 *     // optional, the URL of the menu item. Defaults to "#"
	 *     'url'=> '#',
	 *     // optional, the HTML options of the URL.
	 *     'urlOptions' => array(...),
	 *     // optional the HTML attributes of the item container (LI).
	 *     'options' => array(...),
	 *     // optional, an array of [[Dropdown]] widget items so to display a dropdown menu on the tab header.
	 *     // important: there is an issue with sub-dropdown menus, and as of 3.0, bootstrap won't support sub-dropdown
	 *     // @see https://github.com/twitter/bootstrap/issues/5050#issuecomment-11741727
	 *     'dropdown'=> array(...)
	 * )
	 * ```
	 *
	 * Optionally, you can also use a plain string instead of an array element.
	 */
	public $items = array();


	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		$this->addCssClass($this->options, 'nav');
	}

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo $this->renderItems();
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
		$label = $item['label'];
		$url = ArrayHelper::getValue($item, 'url', '#');
		$options = ArrayHelper::getValue($item, 'options', array());
		$urlOptions = ArrayHelper::getValue($item, 'urlOptions', array());
		$dropdown = null;

		// does it has a dropdown widget?
		if (isset($item['dropdown'])) {
			$urlOptions['data-toggle'] = 'dropdown';
			$this->addCssClass($options, 'dropdown');
			$this->addCssClass($urlOptions, 'dropdown-toggle');
			$label .= ' ' . Html::tag('b', '', array('class' => 'caret'));
			$dropdown = Dropdown::widget(array('items' => $item['dropdown'], 'clientOptions' => false));
		}

		return Html::tag('li', Html::a($label, $url, $urlOptions) . $dropdown, $options);
	}
}