<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use yii\base\InvalidConfigException;
use yii\helpers\base\ArrayHelper;
use yii\helpers\Html;


/**
 * Dropdown renders a Tab bootstrap javascript component.
 *
 * @see http://twitter.github.io/bootstrap/javascript.html#dropdowns
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class Dropdown extends Widget
{
	/**
	 * @var array list of menu items in the dropdown. Each array element represents a single
	 * menu with the following structure:
	 *
	 * ```php
	 * array(
	 *     // required, the label of the item link
	 *     'label' => 'Menu label',
	 *     // optional, url of the item link
	 *     'url' => '',
	 *     // optional the HTML attributes of the item link
	 *     'urlOptions'=> array(...),
	 *     // optional the HTML attributes of the item
	 *     'options'=> array(...),
	 *     // optional, an array of items that configure a sub menu of the item
	 *     // note: if `items` is set, then `url` of the parent item will be ignored and automatically set to "#"
	 *     'items'=> array(...)
	 * )
	 * ```
	 * Additionally, you can also configure a dropdown item as string.
	 */
	public $items = array();


	/**
	 * Initializes the widget.
	 * If you override this method, make sure you call the parent implementation first.
	 */
	public function init()
	{
		parent::init();
		$this->addCssClass($this->options, 'dropdown-menu');
	}

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo Html::beginTag('ul', $this->options) . "\n";
		echo $this->renderContents() . "\n";
		echo Html::endTag('ul') . "\n";
		$this->registerPlugin('dropdown');
	}

	/**
	 * Renders dropdown contents as specified on [[items]].
	 * @return string the rendering result.
	 * @throws InvalidConfigException
	 */
	protected function renderContents()
	{
		$contents = array();
		foreach ($this->items as $item) {
			if (is_string($item)) {
				$contents[] = $item;
				continue;
			}
			if (!isset($item['label'])) {
				throw new InvalidConfigException("The 'label' option is required.");
			}

			$options = ArrayHelper::getValue($item, 'options', array());
			$urlOptions = ArrayHelper::getValue($item, 'urlOptions', array());
			$urlOptions['tabindex'] = '-1';

			if (isset($item['items'])) {
				$this->addCssClass($options, 'dropdown-submenu');
				$content = Html::a($item['label'], '#', $urlOptions) . $this->dropdown($item['items']);
			} else {
				$content = Html::a($item['label'], ArrayHelper::getValue($item, 'url', '#'), $urlOptions);
			}
			$contents[] = Html::tag('li', $content , $options);
		}

		return implode("\n", $contents);
	}

	/**
	 * Generates a dropdown menu.
	 * @param array $items the configuration of the dropdown items. See [[items]].
	 * @return string the generated dropdown menu
	 * @see items
	 */
	protected function dropdown($items)
	{
		return Dropdown::widget(array('items' => $items, 'clientOptions' => false));
	}
}