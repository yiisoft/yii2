<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use Yii;
use yii\helpers\Html;

/**
 * Panel renders a bootstrap panel component
 *
 * Example
 * ~~~php
 * Panel::begin(array(
 *     'title' => array(
 *         'label' => 'Panel Title'
 *     )
 * );
 *
 * echo 'Panel Content';
 *
 * Panel::end();
 * ~~~
 *
 * @see http://getbootstrap.com/components/#panels
 * @author Niko Wicaksono <wicaksono@nodews.com>
 * @since 2.0
 */
class Panel extends Widget {
	/**
	 * @var string the heading content in the panel component.
	 * If this and panel title null, no panel heading will be rendered.
	 */
	public $heading;
	/**
	 * @var string the footer content in the panel component.
	 * If this is null, no panel footer will be rendered.
	 */
	public $footer;
	/**
	 * @var array the options for rendering the title tag.
	 * The panel title is displayed in the header of the panel component.
	 * If this is null, no panel title will be rendered.
	 *
	 * The following special options are supported:
	 *
	 * - tag: string, the tag name of panel title. Defaults to 'h3'.
	 * - label: string, the label of panel title. Defaults to null.
	 *
	 * The rest of the options will be rendered as the HTML attributes of the panel tag.
	 */
	public $title = array();

	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		parent::init();
		Html::addCssClass($this->options, 'panel');
		$this->initTitle();

		echo Html::beginTag('div', $this->options) . "\n";
		echo $this->renderHeading() . "\n";
	}

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo "\n" . $this->renderFooter();
		echo "\n" . Html::endTag('div');
	}

	/**
	 * Initializes panel title.
	 */
	protected function initTitle()
	{
		if (isset($this->title['tag']) === false) {
			$this->title['tag'] = 'h3';
		}
		if (isset($this->title['label']) === false) {
			$this->title['label'] = null;
		}
	}

	/**
	 * Renders panel header
	 * @return string the rendering result
	 */
	protected function renderHeading()
	{
		if ($this->title['label'] !== null) {
			$this->heading = $this->renderTitle() . "\n" . $this->heading;
		}
		if ($this->heading !== null) {
			return Html::tag('div', "\n" . $this->heading . "\n", array(
				'class' => 'panel-heading'
			));
		}

		return null;
	}

	/**
	 * Renders panel footer
	 * @return string the rendering result
	 */
	protected  function renderFooter()
	{
		if ($this->title !== null) {
			return Html::tag('div', "\n" . $this->footer . "\n", array(
				'class' => 'panel-footer'
			));
		}

		return null;
	}

	/**
	 * Renders panel title
	 * @return string the rendering result
	 */
	protected function renderTitle()
	{
		return Html::tag($this->title['tag'], "\n" . $this->title['label'] . "\n", array(
			'class' => 'panel-title'
		));
	}
}
