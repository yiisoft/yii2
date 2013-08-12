<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use yii\helpers\Html;

/**
 * NavBar renders a navbar HTML component.
 *
 * Any content enclosed between the [[begin()]] and [[end()]] calls of NavBar
 * is treated as the content of the navbar. You may use widgets such as [[Nav]]
 * or [[\yii\widgets\Menu]] to build up such content. For example,
 *
 * ```php
 * use yii\bootstrap\NavBar;
 * use yii\widgets\Menu;
 *
 * NavBar::begin(array('brandLabel' => 'NavBar Test'));
 * echo Nav::widget(array(
 *     'items' => array(
 *         array('label' => 'Home', 'url' => array('/site/index')),
 *         array('label' => 'About', 'url' => array('/site/about')),
 *     ),
 * ));
 * NavBar::end();
 * ```
 *
 * @see http://twitter.github.io/bootstrap/components.html#navbar
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class NavBar extends Widget
{
	/**
	 * @var boolean whether to enable a collapsing responsive navbar.
	 */
	public $responsive = true;
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
	 * Initializes the widget.
	 */
	public function init()
	{
		parent::init();
		$this->clientOptions = false;
		Html::addCssClass($this->options, 'navbar');
		Html::addCssClass($this->brandOptions, 'navbar-brand');

		echo Html::beginTag('div', $this->options);
		if ($this->responsive) {
			echo Html::beginTag('div', array('class' => 'container'));
			echo $this->renderToggleButton();
			echo Html::beginTag('div', array('class' => 'nav-collapse collapse navbar-responsive-collapse'));
		}
		if ($this->brandLabel !== null) {
			echo Html::a($this->brandLabel, $this->brandUrl, $this->brandOptions);
		}
	}

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		if ($this->responsive) {
			echo Html::endTag('div');
			echo Html::endTag('div');
		}
		echo Html::endTag('div');
		BootstrapPluginAsset::register($this->getView());
	}

	/**
	 * Renders collapsible toggle button.
	 * @return string the rendering toggle button.
	 */
	protected function renderToggleButton()
	{
		$bar = Html::tag('span', '', array('class' => 'icon-bar'));
		return Html::button("{$bar}\n{$bar}\n{$bar}", array(
			'class' => 'navbar-toggle',
			'data-toggle' => 'collapse',
			'data-target' => '.navbar-responsive-collapse',
		));
	}
}
