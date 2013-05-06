<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use yii\base\Widget;
use yii\helpers\Html;

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Menu extends Widget
{
	/**
	 * @var array list of menu items. Each menu item is specified as an array of name-value pairs.
	 * Possible option names include the following:
	 * <ul>
	 * <li>label: string, optional, specifies the menu item label. When {@link encodeLabel} is true, the label
	 * will be HTML-encoded. If the label is not specified, it defaults to an empty string.</li>
	 * <li>url: string or array, optional, specifies the URL of the menu item. It is passed to {@link Html::normalizeUrl}
	 * to generate a valid URL. If this is not set, the menu item will be rendered as a span text.</li>
	 * <li>visible: boolean, optional, whether this menu item is visible. Defaults to true.
	 * This can be used to control the visibility of menu items based on user permissions.</li>
	 * <li>items: array, optional, specifies the sub-menu items. Its format is the same as the parent items.</li>
	 * <li>active: boolean, optional, whether this menu item is in active state (currently selected).
	 * If a menu item is active and {@link activeClass} is not empty, its CSS class will be appended with {@link activeClass}.
	 * If this option is not set, the menu item will be set active automatically when the current request
	 * is triggered by {@link url}. Note that the GET parameters not specified in the 'url' option will be ignored.</li>
	 * <li>template: string, optional, the template used to render this menu item.
	 * When this option is set, it will override the global setting {@link itemTemplate}.
	 * Please see {@link itemTemplate} for more details. This option has been available since version 1.1.1.</li>
	 * <li>linkOptions: array, optional, additional HTML attributes to be rendered for the link or span tag of the menu item.</li>
	 * <li>itemOptions: array, optional, additional HTML attributes to be rendered for the container tag of the menu item.</li>
	 * <li>submenuOptions: array, optional, additional HTML attributes to be rendered for the container of the submenu if this menu item has one.
	 * When this option is set, the {@link submenuHtmlOptions} property will be ignored for this particular submenu.
	 * This option has been available since version 1.1.6.</li>
	 * </ul>
	 */
	public $items = array();
	/**
	 * @var string the template used to render an individual menu item. In this template,
	 * the token "{menu}" will be replaced with the corresponding menu link or text.
	 * If this property is not set, each menu will be rendered without any decoration.
	 * This property will be overridden by the 'template' option set in individual menu items via {@items}.
	 * @since 1.1.1
	 */
	public $itemTemplate;
	/**
	 * @var boolean whether the labels for menu items should be HTML-encoded. Defaults to true.
	 */
	public $encodeLabel = true;
	/**
	 * @var string the CSS class to be appended to the active menu item. Defaults to 'active'.
	 * If empty, the CSS class of menu items will not be changed.
	 */
	public $activeCssClass = 'active';
	/**
	 * @var boolean whether to automatically activate items according to whether their route setting
	 * matches the currently requested route. Defaults to true.
	 * @since 1.1.3
	 */
	public $activateItems = true;
	/**
	 * @var boolean whether to activate parent menu items when one of the corresponding child menu items is active.
	 * The activated parent menu items will also have its CSS classes appended with {@link activeCssClass}.
	 * Defaults to false.
	 */
	public $activateParents = false;
	/**
	 * @var boolean whether to hide empty menu items. An empty menu item is one whose 'url' option is not
	 * set and which doesn't contain visible child menu items. Defaults to true.
	 */
	public $hideEmptyItems = true;
	/**
	 * @var array HTML attributes for the menu's root container tag
	 */
	public $options = array();
	/**
	 * @var array HTML attributes for the submenu's container tag.
	 */
	public $submenuHtmlOptions = array();
	/**
	 * @var string the HTML element name that will be used to wrap the label of all menu links.
	 * For example, if this property is set as 'span', a menu item may be rendered as
	 * &lt;li&gt;&lt;a href="url"&gt;&lt;span&gt;label&lt;/span&gt;&lt;/a&gt;&lt;/li&gt;
	 * This is useful when implementing menu items using the sliding window technique.
	 * Defaults to null, meaning no wrapper tag will be generated.
	 * @since 1.1.4
	 */
	public $linkLabelWrapper;
	/**
	 * @var array HTML attributes for the links' wrap element specified in
	 * {@link linkLabelWrapper}.
	 * @since 1.1.13
	 */
	public $linkLabelWrapperHtmlOptions = array();
	/**
	 * @var string the CSS class that will be assigned to the first item in the main menu or each submenu.
	 * Defaults to null, meaning no such CSS class will be assigned.
	 * @since 1.1.4
	 */
	public $firstItemCssClass;
	/**
	 * @var string the CSS class that will be assigned to the last item in the main menu or each submenu.
	 * Defaults to null, meaning no such CSS class will be assigned.
	 * @since 1.1.4
	 */
	public $lastItemCssClass;
	/**
	 * @var string the CSS class that will be assigned to every item.
	 * Defaults to null, meaning no such CSS class will be assigned.
	 * @since 1.1.9
	 */
	public $itemCssClass;

	/**
	 * Initializes the menu widget.
	 * This method mainly normalizes the {@link items} property.
	 * If this method is overridden, make sure the parent implementation is invoked.
	 */
	public function init()
	{
		$route = $this->getController()->getRoute();
		$this->items = $this->normalizeItems($this->items, $route, $hasActiveChild);
	}

	/**
	 * Calls {@link renderMenu} to render the menu.
	 */
	public function run()
	{
		if (count($this->items)) {
			echo Html::beginTag('ul', $this->options) . "\n";
			$this->renderItems($this->items);
			echo Html::endTag('ul');
		}
	}

	/**
	 * Recursively renders the menu items.
	 * @param array $items the menu items to be rendered recursively
	 */
	protected function renderItems($items)
	{
		$count = 0;
		$n = count($items);
		foreach ($items as $item) {
			$count++;
			$options = isset($item['itemOptions']) ? $item['itemOptions'] : array();
			$class = array();
			if ($item['active'] && $this->activeCssClass != '') {
				$class[] = $this->activeCssClass;
			}
			if ($count === 1 && $this->firstItemCssClass !== null) {
				$class[] = $this->firstItemCssClass;
			}
			if ($count === $n && $this->lastItemCssClass !== null) {
				$class[] = $this->lastItemCssClass;
			}
			if ($this->itemCssClass !== null) {
				$class[] = $this->itemCssClass;
			}
			if ($class !== array()) {
				if (empty($options['class'])) {
					$options['class'] = implode(' ', $class);
				} else {
					$options['class'] .= ' ' . implode(' ', $class);
				}
			}

			echo Html::beginTag('li', $options);

			$menu = $this->renderItem($item);
			if (isset($this->itemTemplate) || isset($item['template'])) {
				$template = isset($item['template']) ? $item['template'] : $this->itemTemplate;
				echo strtr($template, array('{menu}' => $menu));
			} else {
				echo $menu;
			}

			if (isset($item['items']) && count($item['items'])) {
				echo "\n" . Html::beginTag('ul', isset($item['submenuOptions']) ? $item['submenuOptions'] : $this->submenuHtmlOptions) . "\n";
				$this->renderItems($item['items']);
				echo Html::endTag('ul') . "\n";
			}

			echo Html::endTag('li') . "\n";
		}
	}

	/**
	 * Renders the content of a menu item.
	 * Note that the container and the sub-menus are not rendered here.
	 * @param array $item the menu item to be rendered. Please see {@link items} on what data might be in the item.
	 * @return string
	 * @since 1.1.6
	 */
	protected function renderItem($item)
	{
		if (isset($item['url'])) {
			$label = $this->linkLabelWrapper === null ? $item['label'] : Html::tag($this->linkLabelWrapper, $this->linkLabelWrapperHtmlOptions, $item['label']);
			return Html::a($label, $item['url'], isset($item['linkOptions']) ? $item['linkOptions'] : array());
		} else {
			return Html::tag('span', isset($item['linkOptions']) ? $item['linkOptions'] : array(), $item['label']);
		}
	}

	/**
	 * Normalizes the {@link items} property so that the 'active' state is properly identified for every menu item.
	 * @param array $items the items to be normalized.
	 * @param string $route the route of the current request.
	 * @param boolean $active whether there is an active child menu item.
	 * @return array the normalized menu items
	 */
	protected function normalizeItems($items, $route, &$active)
	{
		foreach ($items as $i => $item) {
			if (isset($item['visible']) && !$item['visible']) {
				unset($items[$i]);
				continue;
			}
			if (!isset($item['label'])) {
				$item['label'] = '';
			}
			if ($this->encodeLabel) {
				$items[$i]['label'] = Html::encode($item['label']);
			}
			$hasActiveChild = false;
			if (isset($item['items'])) {
				$items[$i]['items'] = $this->normalizeItems($item['items'], $route, $hasActiveChild);
				if (empty($items[$i]['items']) && $this->hideEmptyItems) {
					unset($items[$i]['items']);
					if (!isset($item['url'])) {
						unset($items[$i]);
						continue;
					}
				}
			}
			if (!isset($item['active'])) {
				if ($this->activateParents && $hasActiveChild || $this->activateItems && $this->isItemActive($item, $route)) {
					$active = $items[$i]['active'] = true;
				} else {
					$items[$i]['active'] = false;
				}
			} elseif ($item['active']) {
				$active = true;
			}
		}
		return array_values($items);
	}

	/**
	 * Checks whether a menu item is active.
	 * This is done by checking if the currently requested URL is generated by the 'url' option
	 * of the menu item. Note that the GET parameters not specified in the 'url' option will be ignored.
	 * @param array $item the menu item to be checked
	 * @param string $route the route of the current request
	 * @return boolean whether the menu item is active
	 */
	protected function isItemActive($item, $route)
	{
		if (isset($item['url']) && is_array($item['url']) && !strcasecmp(trim($item['url'][0], '/'), $route)) {
			unset($item['url']['#']);
			if (count($item['url']) > 1) {
				foreach (array_splice($item['url'], 1) as $name => $value) {
					if (!isset($_GET[$name]) || $_GET[$name] != $value) {
						return false;
					}
				}
			}
			return true;
		}
		return false;
	}

}
