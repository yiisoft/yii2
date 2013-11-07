<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jui;

use yii\helpers\Html;

/**
 * Droppable renders an droppable jQuery UI widget.
 *
 * For example:
 *
 * ```php
 * Droppable::begin([
 *     'clientOptions' => ['accept' => '.special'],
 * ]);
 *
 * echo 'Droppable body here...';
 *
 * Droppable::end();
 * ```
 *
 * @see http://api.jqueryui.com/droppable/
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class Droppable extends Widget
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
		$this->registerWidget('droppable', DroppableAsset::className());
	}
}
