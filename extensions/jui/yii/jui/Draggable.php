<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jui;

use yii\helpers\Html;

/**
 * Draggable renders an draggable jQuery UI widget.
 *
 * For example:
 *
 * ```php
 * Draggable::begin(array(
 *     'clientOptions' => array(
 *         'grid' => array(50, 20),
 *     ),
 * ));
 *
 * echo 'Draggable contents here...';
 *
 * Draggable::end();
 * ```
 *
 * @see http://api.jqueryui.com/draggable/
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class Draggable extends Widget
{
	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		parent::init();
		echo Html::beginTag('div', $this->options) . "\n";
	}

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo Html::endTag('div') . "\n";
		$this->registerWidget('draggable', false);
	}
}
