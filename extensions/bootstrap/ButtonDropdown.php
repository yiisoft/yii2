<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use yii\helpers\Html;

/**
 * ButtonDropdown renders a group or split button dropdown bootstrap component.
 *
 * For example,
 *
 * ```php
 * // a button group using Dropdown widget
 * echo ButtonDropdown::widget([
 *     'label' => 'Action',
 *     'dropdown' => [
 *         'items' => [
 *             ['label' => 'DropdownA', 'url' => '/'],
 *             ['label' => 'DropdownB', 'url' => '#'],
 *         ],
 *     ],
 * ]);
 * ```
 * @see http://getbootstrap.com/javascript/#buttons
 * @see http://getbootstrap.com/components/#btn-dropdowns
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class ButtonDropdown extends Widget
{
	/**
	 * @var string the button label
	 */
	public $label = 'Button';
	/**
	 * @var array the HTML attributes of the button.
	 * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
	 */
	public $options = [];
	/**
	 * @var array the configuration array for [[Dropdown]].
	 */
	public $dropdown = [];
	/**
	 * @var boolean whether to display a group of split-styled button group.
	 */
	public $split = false;
	/**
	 * @var string the tag to use to render the button
	 */
	public $tagName = 'button';
	/**
	 * @var boolean whether the label should be HTML-encoded.
	 */
	public $encodeLabel = true;


	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo $this->renderButton() . "\n" . $this->renderDropdown();
		$this->registerPlugin('button');
	}

	/**
	 * Generates the button dropdown.
	 * @return string the rendering result.
	 */
	protected function renderButton()
	{
		Html::addCssClass($this->options, 'btn');
		$label = $this->label;
		if ($this->encodeLabel) {
			$label = Html::encode($label);
		}
		if ($this->split) {
			$options = $this->options;
			$this->options['data-toggle'] = 'dropdown';
			Html::addCssClass($this->options, 'dropdown-toggle');
			$splitButton = Button::widget([
				'label' => '<span class="caret"></span>',
				'encodeLabel' => false,
				'options' => $this->options,
				'view' => $this->getView(),
			]);
		} else {
			$label .= ' <span class="caret"></span>';
			$options = $this->options;
			if (!isset($options['href'])) {
				$options['href'] = '#';
			}
			Html::addCssClass($options, 'dropdown-toggle');
			$options['data-toggle'] = 'dropdown';
			$splitButton = '';
		}
		return Button::widget([
			'tagName' => $this->tagName,
			'label' => $label,
			'options' => $options,
			'encodeLabel' => false,
			'view' => $this->getView(),
		]) . "\n" . $splitButton;
	}

	/**
	 * Generates the dropdown menu.
	 * @return string the rendering result.
	 */
	protected function renderDropdown()
	{
		$config = $this->dropdown;
		$config['clientOptions'] = false;
		$config['view'] = $this->getView();
		return Dropdown::widget($config);
	}
}
