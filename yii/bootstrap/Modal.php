<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Modal renders a modal window that can be toggled by clicking on a button.
 *
 * For example,
 *
 * ~~~php
 * echo Modal::widget(array(
 *     'header' => '<h2>Hello world</h2>',
 *     'body' => 'Say hello...',
 *     'toggleButton' => array(
 *         'label' => 'click me',
 *     ),
 * ));
 * ~~~
 *
 * The following example will show the content enclosed between the [[begin()]]
 * and [[end()]] calls within the modal window:
 *
 * ~~~php
 * Modal::begin(array(
 *     'header' => '<h2>Hello world</h2>',
 *     'toggleButton' => array(
 *         'label' => 'click me',
 *     ),
 * ));
 *
 * echo 'Say hello...';
 *
 * Modal::end();
 * ~~~
 *
 * @see http://twitter.github.io/bootstrap/javascript.html#modals
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Modal extends Widget
{
	/**
	 * @var string the header content in the modal window.
	 */
	public $header;
	/**
	 * @var string the body content in the modal window. Note that anything between
	 * the [[begin()]] and [[end()]] calls of the Modal widget will also be treated
	 * as the body content, and will be rendered before this.
	 */
	public $body;
	/**
	 * @var string the footer content in the modal window.
	 */
	public $footer;
	/**
	 * @var array the options for rendering the close button tag.
	 * The close button is displayed in the header of the modal window. Clicking
	 * on the button will hide the modal window. If this is null, no close button will be rendered.
	 *
	 * The following special options are supported:
	 *
	 * - tag: string, the tag name of the button. Defaults to 'button'.
	 * - label: string, the label of the button. Defaults to '&times;'.
	 *
	 * The rest of the options will be rendered as the HTML attributes of the button tag.
	 * Please refer to the [Modal plugin help](http://twitter.github.com/bootstrap/javascript.html#modals)
	 * for the supported HTML attributes.
	 */
	public $closeButton = array();
	/**
	 * @var array the options for rendering the toggle button tag.
	 * The toggle button is used to toggle the visibility of the modal window.
	 * If this property is null, no toggle button will be rendered.
	 *
	 * The following special options are supported:
	 *
	 * - tag: string, the tag name of the button. Defaults to 'button'.
	 * - label: string, the label of the button. Defaults to 'Show'.
	 *
	 * The rest of the options will be rendered as the HTML attributes of the button tag.
	 * Please refer to the [Modal plugin help](http://twitter.github.com/bootstrap/javascript.html#modals)
	 * for the supported HTML attributes.
	 */
	public $toggleButton;


	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		parent::init();

		$this->options = array_merge(array(
			'class' => 'modal hide',
		), $this->options);
		$this->addCssClass($this->options, 'modal');

		$this->pluginOptions = array_merge(array(
			'show' => false,
		), $this->pluginOptions);

		if ($this->closeButton !== null) {
			$this->closeButton = array_merge(array(
				'data-dismiss' => 'modal',
				'aria-hidden' => 'true',
				'class' => 'close',
			), $this->closeButton);
		}

		if ($this->toggleButton !== null) {
			$this->toggleButton = array_merge(array(
				'data-toggle' => 'modal',
			), $this->toggleButton);
			if (!isset($this->toggleButton['data-target']) && !isset($this->toggleButton['href'])) {
				$this->toggleButton['data-target'] = '#' . $this->options['id'];
			}
		}

		ob_start();
		ob_implicit_flush(false);
	}

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		$this->body = ob_get_clean() . $this->body;

		echo $this->renderToggleButton();

		$html = $this->renderHeader() . "\n"
			. $this->renderBody() . "\n"
			. $this->renderFooter();
		echo Html::tag('div', "\n" . $html . "\n", $this->options);

		$this->registerPlugin('modal');
	}

	/**
	 * Renders the header HTML markup of the modal
	 * @return string the rendering result
	 */
	protected function renderHeader()
	{
		$button = $this->renderCloseButton();
		if ($button !== null) {
			$this->header = $button . "\n" . $this->header;
		}
		if ($this->header !== null) {
			return Html::tag('div', "\n" . $this->header . "\n", array('class' => 'modal-header'));
		} else {
			return null;
		}
	}

	/**
	 * Renders the HTML markup for the body of the modal
	 * @return string the rendering result
	 */
	protected function renderBody()
	{
		return Html::tag('div', $this->body, array('class' => 'modal-body'));
	}

	/**
	 * Renders the HTML markup for the footer of the modal
	 * @return string the rendering result
	 */
	protected function renderFooter()
	{
		if ($this->footer !== null) {
			return Html::tag('div', $this->footer, array('class' => 'modal-footer'));
		} else {
			return null;
		}
	}

	/**
	 * Renders the toggle button.
	 * @return string the rendering result
	 */
	protected function renderToggleButton()
	{
		if ($this->toggleButton !== null) {
			$tag = ArrayHelper::remove($this->toggleButton, 'tag', 'button');
			$label = ArrayHelper::remove($this->toggleButton, 'label', 'Show');
			if ($tag === 'button' && !isset($this->toggleButton['type'])) {
				$this->toggleButton['type'] = 'button';
			}
			return Html::tag($tag, $label, $this->toggleButton) . "\n";
		} else {
			return null;
		}
	}

	/**
	 * Renders the close button.
	 * @return string the rendering result
	 */
	protected function renderCloseButton()
	{
		if ($this->closeButton !== null) {
			$tag = ArrayHelper::remove($this->closeButton, 'tag', 'button');
			$label = ArrayHelper::remove($this->closeButton, 'label', '&times;');
			if ($tag === 'button' && !isset($this->closeButton['type'])) {
				$this->closeButton['type'] = 'button';
			}
			return Html::tag($tag, $label, $this->closeButton);
		} else {
			return null;
		}
	}
}
