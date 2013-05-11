<?php
/**
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @link http://www.yiiframework.com/
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap\enum;

/**
 * AlertEnum provides easy access to all predefined alert set of named values
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class AlertEnum
{
	public static function display($message, $htmlOptions = array(), $dismiss = true)
	{
		if (isset($htmlOptions['class']))
			$htmlOptions['class'] .= ' ' . AlertEnum::CLASS_DEFAULT;
		else
			$htmlOptions['class'] = AlertEnum::CLASS_DEFAULT;

		if ($dismiss) {
			Button::dismissLink();
		}
		ob_start();
		echo \CHtml::openTag('div', $htmlOptions);
		echo $message;
		echo \CHtml::closeTag('div');
		return ob_get_clean();
	}
}