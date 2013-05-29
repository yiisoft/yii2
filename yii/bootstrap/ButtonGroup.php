<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use yii\helpers\base\ArrayHelper;
use yii\helpers\Html;


/**
 * ButtonGroup renders a button group bootstrap component.
 *
 * For example,
 *
 * ```php
 * // a button group with items configuration
 * echo ButtonGroup::::widget(array(
 *     'items' => array(
 *         array('label'=>'A'),
 *         array('label'=>'B'),
 *     )
 * ));
 *
 * // button group with an item as a string
 * echo ButtonGroup::::widget(array(
 *     'items' => array(
 *         Button::widget(array('label'=>'A')),
 *         array('label'=>'B'),
 *     )
 * ));
 *
 * // button group with body content as string
 * ButtonGroup::beging();
 * Button::widget(array('label'=>'A')), // you can also use plain string
 * ButtonGroup::end();
 * ```
 * @see http://twitter.github.io/bootstrap/javascript.html#buttons
 * @see http://twitter.github.io/bootstrap/components.html#buttonGroups
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class ButtonGroup extends Widget
{
	/**
	 * @var array list of buttons. Each array element represents a single
	 * menu with the following structure:
	 *
	 * - label: string, required, the button label.
	 * - options: array, optional, the HTML attributes of the button.
	 */
	public $items = array();
	/**
	 * @var boolean whether the labels for dropdown items should be HTML-encoded.
	 */
	public $encodeLabels = true;


	/**
	 * Initializes the widget.
	 * If you override this method, make sure you call the parent implementation first.
	 */
	public function init()
	{
		parent::init();
		$this->clientOptions = false;
		$this->addCssClass($this->options, 'btn-group');
		echo $this->renderGroupBegin() . "\n";
	}

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo "\n" . $this->renderGroupEnd();
		$this->registerPlugin('button');
	}

	/**
	 * Renders the opening tag of the button group.
	 * @return string the rendering result.
	 */
	protected function renderGroupBegin()
	{
		return Html::beginTag('div', $this->options);
	}

	/**
	 * Renders the items and closing tag of the button group.
	 * @return string the rendering result.
	 */
	protected function renderGroupEnd()
	{
		return $this->renderItems() . "\n" . Html::endTag('div');
	}

	/**
	 * Generates the buttons that compound the group as specified on [[items]].
	 * @return string the rendering result.
	 */
	protected function renderItems()
	{
		if (is_string($this->items)) {
			return $this->items;
		}
		$buttons = array();
		foreach ($this->items as $item) {
			if (is_string($item)) {
				$buttons[] = $item;
				continue;
			}
			$label = ArrayHelper::getValue($item, 'label');
			$options = ArrayHelper::getValue($item, 'options');
			$buttons[] = Button::widget(array(
					'label' => $label,
					'options' => $options,
					'encodeLabel' => $this->encodeLabels
				)
			);
		}
		return implode("\n", $buttons);
	}
}