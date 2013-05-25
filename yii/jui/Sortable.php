<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jui;

use yii\helpers\Html;

/**
 * AutoComplete renders a sortable jQuery UI widget.
 *
 * For example:
 *
 * ```php
 * Sortable::begin(array(
 *     'clientOptions' => array(
 *         'cursor' => 'move',
 *     ),
 *     'options' => array(
 *         'tag' => 'ul',
 *     ),
 * ));
 *
 * echo '<li>Item 1</li>';
 * echo '<li>Item 2</li>';
 * echo '<li>Item 3</li>';
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
	 * Initializes the widget.
	 */
	public function init()
	{
		parent::init();
		$options = $this->options;
		$tag = isset($options['tag']) ? $options['tag'] : 'div';
		unset($options['tag']);
		echo Html::beginTag($tag, $options) . "\n";
	}

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		$options = $this->options;
		$tag = isset($options['tag']) ? $options['tag'] : 'div';
		unset($options['tag']);
		echo Html::endTag($tag) . "\n";
		$this->registerWidget('sortable', false);
	}
}
