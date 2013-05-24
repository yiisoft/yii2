<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jui;

use yii\helpers\base\ArrayHelper;
use yii\helpers\Html;

/**
 * ProgressBar renders an progressbar jQuery UI widget.
 *
 * For example:
 *
 * ```php
 * echo Progressbar::widget(array(
 *     'clientOptions' => array(
 *         'value' => 75,
 *     ),
 * ));
 * ```
 *
 * @see http://api.jqueryui.com/progressbar/
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class ProgressBar extends Widget
{
	/**
	 * Renders the widget.
	 */
	public function run()
	{
		echo Html::beginTag('div', $this->options) . "\n";
		echo Html::endTag('div') . "\n";
		$this->registerWidget('progressbar');
	}
}
