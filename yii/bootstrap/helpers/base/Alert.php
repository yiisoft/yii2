<?php
/**
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @link http://www.yiiframework.com/
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap\helpers\base;

use yii\bootstrap\enum\AlerEnum;

/**
 * Alert provides methods to make use of bootstrap alert messages in your application
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class Alert
{
	public static function display($message, $htmlOptions = array(), $dismiss = true)
	{
		if (isset($htmlOptions['class']))
			$htmlOptions['class'] .= ' ' . AlertEnum::CLASS_DEFAULT;
		else
			$htmlOptions['class'] = AlertEnum::CLASS_DEFAULT;

		ob_start();
		echo \CHtml::openTag('div', $htmlOptions);
		if ($dismiss)
			echo Button::dismissLink();
		echo $message;
		echo '</div>';
		return ob_get_clean();
	}
}