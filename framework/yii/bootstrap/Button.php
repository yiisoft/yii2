<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use yii\base\InvalidConfigException;
use yii\helpers\Html;


/**
 * Button renders a group or split button dropdown bootstrap component.
 *
 * For example,
 *
 * ```php
 * // a button group using Dropdown widget
 * echo \yii\bootstrap\Button::widget(array(
 *     'label' => 'Action',
 *     'items' => Dropdown::widget(array(
 *         'clientOptions' => false,
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
 *     )),
 * ));
 *
 * // split button group using `items` dropdown configuration
 * echo \yii\bootstrap\Button::widget(array(
 *     'label' => 'Action',
 *     'split' => true,
 *     'items' => array(
 *         array(
 *             'label' => 'DropdownA',
 *             'url' => '/',
 *         ),
 *         array(
 *             'label' => 'DropdownB',
 *             'url' => '#',
 *         ),
 *      ),
 * ));
 * ```
 * @see http://twitter.github.io/bootstrap/javascript.html#buttons
 * @see http://twitter.github.io/bootstrap/components.html#buttonDropdowns
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class Button extends Widget
{
	/**
	 * @var string the button label
	 */
	public $label;
	/**
	 * @var array the HTML attributes of the button.
	 */
	public $buttonOptions = array();
	/**
	 * @var array list of menu items in the dropdown. Each array element represents a single
	 * menu with the following structure:
	 *
	 * - label: string, required, the label of the item link
	 * - url: string, optional, the url of the item link. Defaults to "#".
	 * - linkOptions: array, optional, the HTML attributes of the item link.
	 * - options: array, optional, the HTML attributes of the item.
	 * - items: array, optional, the dropdown items configuration array.
	 *
	 * @see https://github.com/twitter/bootstrap/issues/5050#issuecomment-11741727
	 * @see [[Dropdown]]
	 */
	public $items = array();
	/**
	 * @var boolean whether to display a group or split styled button group.
	 */
	public $split = false;
	/**
	 * @var boolean whether the labels for dropdown items should be HTML-encoded.
	 */
	public $encodeLabels = true;


	/**
	 * Initializes the widget.
	 * If you override this method, make sure you call the parent implementation first.
	 * @throws InvalidConfigException
	 */
	public function init()
	{
		if ($this->label === null) {
			throw new InvalidConfigException("The 'label' option is required.");
		}
		parent::init();
		$this->addCssClass($this->options, 'btn-group');
	}

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo Html::beginTag('div', $this->options) . "\n";
		echo $this->renderLabel() . "\n";
		echo $this->renderItems() . "\n";
		echo Html::endTag('div') . "\n";
		$this->registerPlugin('button');
	}

	/**
	 * Generates the button label.
	 * @return string the rendering result.
	 */
	protected function renderLabel()
	{
		$label = $this->encodeLabels ? Html::encode($this->label) : $this->label;
		$this->addCssClass($this->buttonOptions, 'btn');
		$splitButton = '';
		if ($this->split) {
			$tag = 'button';
			$options = $this->buttonOptions;
			$this->buttonOptions['data-toggle'] = 'dropdown';
			$this->addCssClass($this->buttonOptions, 'dropdown-toggle');
			$splitButton = Html::tag('button', '<span class="caret"></span>', $this->buttonOptions);
		} else {
			$tag = 'a';
			$label .= ' <span class="caret"></span>';
			$options = $this->buttonOptions;
			if (!isset($options['href'])) {
				$options['href'] = '#';
			}
			$this->addCssClass($options, 'dropdown-toggle');
			$options['data-toggle'] = 'dropdown';
		}
		return Html::tag($tag, $label, $options) . "\n" . $splitButton;
	}

	/**
	 * Generates the dropdown menu as specified on [[items]].
	 * @return string the rendering result.
	 */
	protected function renderItems()
	{
		if (is_string($this->items)) {
			return $this->items;
		}
		$config = array('items' => $this->items, 'clientOptions' => false);
		return Dropdown::widget($config);
	}
}