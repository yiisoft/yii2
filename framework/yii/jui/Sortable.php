<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jui;

use yii\helpers\Html;

/**
 * Sortable renders a sortable jQuery UI widget.
 *
 * For example:
 *
 * ```php
 * echo Sortable::widget(array(
 *     'items' => array(
 *         '<li>Item 1</li>',
 *         '<li>Item 2</li>',
 *         '<li>Item 3</li>',
 *     ),
 *     'clientOptions' => array(
 *         'cursor' => 'move',
 *     ),
 * ));
 * ```
 *
 * The following example will show the content enclosed between the [[begin()]]
 * and [[end()]] calls within the sortable widget:
 *
 * ```php
 * Sortable::begin(array(
 *     'clientOptions' => array(
 *         'cursor' => 'move',
 *     ),
 *     'options' => array(
 *         'tag' => 'div',
 *     ),
 * ));
 *
 * echo '<div>Item 1</div>';
 * echo '<div>Item 2</div>';
 * echo '<div>Item 3</div>';
 *
 * Sortable::end();
 * ```
 *
 * @see http://api.jqueryui.com/sortable/
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class Sortable extends Widget
{
	/**
	 * @var array list of sortable containers. Each array element represents a single
	 * sortable container.
	 */
	public $items = array();


	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		parent::init();
		$options = $this->options;
		$tag = isset($options['tag']) ? $options['tag'] : 'ul';
		unset($options['tag']);
		echo Html::beginTag($tag, $options) . "\n";
	}

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo $this->renderItems() . "\n";
		echo Html::endTag(isset($this->options['tag']) ? $this->options['tag'] : 'ul') . "\n";
		$this->registerWidget('sortable', false);
	}

	/**
	 * Renders sortable items as specified on [[items]].
	 * @return string the rendering result
	 */
	public function renderItems()
	{
		return implode("\n", $this->items);
	}
}
