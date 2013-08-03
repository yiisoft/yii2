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
 * NavBar renders a navbar HTML component.
 *
 * For example:
 *
 * ```php
 * echo NavBar::widget(array(
 *     'brandLabel' => 'NavBar Test',
 *     'items' => array(
 *         // a Nav widget
 *         array(
 *             // defaults to Nav anyway.
 *             'class' => 'yii\bootstrap\Nav',
 *             // widget configuration
 *             'options' => array(
 *                 'items' => array(
 *                     array(
 *                         'label' => 'Home',
 *                         'url' => '/',
 *                         'options' => array('class' => 'active'),
 *                     ),
 *                     array(
 *                         'label' => 'Dropdown',
 *                         'content' => new Dropdown(array(
 *                             'items' => array(
 *                                 array(
 *                                     'label' => 'DropdownA',
 *                                     'url' => '#',
 *                                 ),
 *                                 array(
 *                                     'label' => 'DropdownB',
 *                                     'url' => '#'
 *                                 ),
 *                             )
 *                         ),
 *                     ),
 *                 )
 *             ),
 *         ),
 *         // you can also use strings
 *         '<form class="navbar-search pull-left" action="">' .
 *         '<input type="text" class="search-query" placeholder="Search">' .
 *         '</form>',
 *     ),
 * ));
 * ```
 *
 * @see http://twitter.github.io/bootstrap/components.html#navbar
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class NavBar extends Widget
{
	/**
	 * @var string the text of the brand.
	 * @see http://twitter.github.io/bootstrap/components.html#navbar
	 */
	public $brandLabel;
	/**
	 * @param array|string $url the URL for the brand's hyperlink tag. This parameter will be processed by [[Html::url()]]
	 * and will be used for the "href" attribute of the brand link. Defaults to site root.
	 */
	public $brandUrl = '/';
	/**
	 * @var array the HTML attributes of the brand link.
	 */
	public $brandOptions = array();
	/**
	 * @var array list of menu items in the navbar widget. Each array element represents a single
	 * menu item with the following structure:
	 *
	 * ```php
	 * array(
	 *     // optional, the menu item class type of the widget to render. Defaults to "Nav" widget.
	 *     'class' => 'Menu item class type',
	 *     // required, the configuration options of the widget.
	 *     'options' => array(...),
	 * ),
	 * // optionally, you can pass a string
	 * '<form class="navbar-search pull-left" action="">' .
	 * '<input type="text" class="search-query span2" placeholder="Search">' .
	 * '</form>',
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
		parent::init();
		$this->clientOptions = false;
		Html::addCssClass($this->options, 'navbar');
		Html::addCssClass($this->brandOptions, 'brand');
	}

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo Html::beginTag('div', $this->options);
		echo $this->renderItems();
		echo Html::endTag('div');
		if (self::$responsive) {
			ResponsiveAsset::register($this->getView());
		} else {
			BootstrapAsset::register($this->getView());
		}
	}

	/**
	 * Renders the items.
	 * @return string the rendering items.
	 */
	protected function renderItems()
	{
		$items = array();
		foreach ($this->items as $item) {
			$items[] = $this->renderItem($item);
		}
		$contents = implode("\n", $items);
		$brand = Html::a($this->brandLabel, $this->brandUrl, $this->brandOptions);

		if (self::$responsive) {
			CollapseAsset::register($this->getView());
			$contents = Html::tag('div',
					$this->renderToggleButton() .
					$brand . "\n" .
					Html::tag('div', $contents, array('class' => 'nav-collapse collapse navbar-collapse')),
					array('class' => 'container'));
		} else {
			$contents = $brand . "\n" . $contents;
		}

		return Html::tag('div', $contents, array('class' => 'navbar-inner'));
	}

	/**
	 * Renders a item. The item can be a string, a custom class or a Nav widget (defaults if no class specified.
	 * @param mixed $item the item to render. If array, it is assumed the configuration of a widget being `class`
	 * required and if not specified, then defaults to `yii\bootstrap\Nav`.
	 * @return string the rendering result.
	 * @throws InvalidConfigException
	 */
	protected function renderItem($item)
	{
		if (is_string($item)) {
			return $item;
		}
		$config = ArrayHelper::getValue($item, 'options', array());
		$config['clientOptions'] = false;

		$class = ArrayHelper::getValue($item, 'class', 'yii\bootstrap\Nav');

		return $class::widget($config);
	}

	/**
	 * Renders collapsible toggle button.
	 * @return string the rendering toggle button.
	 */
	protected function renderToggleButton()
	{
		$items = array();
		for ($i = 0; $i < 3; $i++) {
			$items[] = Html::tag('span', '', array('class' => 'icon-bar'));
		}
		return Html::a(implode("\n", $items), null, array(
			'class' => 'btn btn-navbar',
			'data-toggle' => 'collapse',
			'data-target' => 'div.navbar-collapse',
		));
	}
}
