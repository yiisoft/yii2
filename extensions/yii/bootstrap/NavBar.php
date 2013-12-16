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
 * NavBar::begin(['brandLabel' => 'NavBar Test']);
 * echo Nav::widget([
 *     'items' => [
 *         ['label' => 'Home', 'url' => ['/site/index']],
 *         ['label' => 'About', 'url' => ['/site/about']],
 *     ],
 * ]);
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
	 * @var string the text of the brand. Note that this is not HTML-encoded.
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
	public $brandOptions = [];

	public $screenReaderToggleText = 'Toggle navigation';

	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		parent::init();
		$this->clientOptions = false;
		Html::addCssClass($this->options, 'navbar navbar-default');
		Html::addCssClass($this->brandOptions, 'navbar-brand');
		if (empty($this->options['role'])) {
			$this->options['role'] = 'navigation';
		}

		echo Html::beginTag('nav', $this->options);
		echo Html::beginTag('div', ['class' => 'container']);

		echo Html::beginTag('div', ['class' => 'navbar-header']);
		echo $this->renderToggleButton();
		if ($this->brandLabel !== null) {
			echo Html::a($this->brandLabel, $this->brandUrl, $this->brandOptions);
		}
		echo Html::endTag('div');

		echo Html::beginTag('div', ['class' => "collapse navbar-collapse navbar-{$this->options['id']}-collapse"]);
	}

	/**
	 * Renders the widget.
	 */
	public function run()
	{

		echo Html::endTag('div');
		echo Html::endTag('div');
		echo Html::endTag('nav');
		BootstrapPluginAsset::register($this->getView());
	}

	/**
	 * Renders collapsible toggle button.
	 * @return string the rendering toggle button.
	 */
	protected function renderToggleButton()
	{
		$bar = Html::tag('span', '', ['class' => 'icon-bar']);
		$screenReader = '<span class="sr-only">'.$this->screenReaderToggleText.'</span>';
		return Html::button("{$screenReader}\n{$bar}\n{$bar}\n{$bar}", [
			'class' => 'navbar-toggle',
			'data-toggle' => 'collapse',
			'data-target' => ".navbar-{$this->options['id']}-collapse",
		]);
	}
}
