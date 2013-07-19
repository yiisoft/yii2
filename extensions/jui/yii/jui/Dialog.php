<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jui;

use yii\helpers\Html;

/**
 * Dialog renders an dialog jQuery UI widget.
 *
 * For example:
 *
 * ```php
 * Dialog::begin(array(
 *     'clientOptions' => array(
 *         'modal' => true,
 *     ),
 * ));
 *
 * echo 'Dialog contents here...';
 *
 * Dialog::end();
 * ```
 *
 * @see http://api.jqueryui.com/dialog/
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class Dialog extends Widget
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
		$this->registerWidget('dialog', DialogAsset::className());
	}
}
