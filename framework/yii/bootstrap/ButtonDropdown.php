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
 * echo ButtonDropdown::widget(array(
 *     'label' => 'Action',
 *     'dropdown' => array(
 *         'items' => array(
 *             array(
 *                 'label' => 'DropdownA',
 *                 'url' => '/',
 *             ),
 *             array(
 *                 'label' => 'DropdownB',
 *                 'url' => '#',
 *             ),
 *         ),
 *     ),
 * ));
 * ```
 * @see http://twitter.github.io/bootstrap/javascript.html#buttons
 * @see http://twitter.github.io/bootstrap/components.html#buttonDropdowns
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
	 */
	public $options = array();
	/**
	 * @var array the configuration array for [[Dropdown]].
	 */
	public $dropdown = array();
	/**
	 * @var boolean whether to display a group of split-styled button group.
	 */
	public $split = false;


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
		if ($this->split) {
			$tag = 'button';
			$options = $this->options;
			$this->options['data-toggle'] = 'dropdown';
			Html::addCssClass($this->options, 'dropdown-toggle');
			$splitButton = Button::widget(array(
				'label' => '<span class="caret"></span>',
				'encodeLabel' => false,
				'options' => $this->options,
			));
		} else {
			$tag = 'a';
			$this->label .= ' <span class="caret"></span>';
			$options = $this->options;
			if (!isset($options['href'])) {
				$options['href'] = '#';
			}
			Html::addCssClass($options, 'dropdown-toggle');
			$options['data-toggle'] = 'dropdown';
			$splitButton = '';
		}
		return Button::widget(array(
			'tagName' => $tag,
			'label' => $this->label,
			'options' => $options,
			'encodeLabel' => false,
		)) . "\n" . $splitButton;
	}

	/**
	 * Generates the dropdown menu.
	 * @return string the rendering result.
	 */
	protected function renderDropdown()
	{
		$config = $this->dropdown;
		$config['clientOptions'] = false;
		return Dropdown::widget($config);
	}
}
