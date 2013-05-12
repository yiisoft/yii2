<?php
/**
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @link http://www.yiiframework.com/
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap\helpers\base;

use yii\bootstrap\enum\AlertEnum;
use yii\bootstrap\enum\BootstrapEnum;
use yii\helpers\Html;

/**
 * Alert provides methods to make use of bootstrap alert messages in your application
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class Alert
{

	/**
	 * Generates an alert box
	 * @param $message
	 * @param array $htmlOptions
	 * @param bool $dismiss whether to display dismissal link or not
	 * @return string
	 */
	public static function create($message, $htmlOptions = array(), $dismiss = true)
	{
		// TODO: this method may should be added to ArrayHelper::add or ArrayHelper::append?
		if (isset($htmlOptions['class']))
			$htmlOptions['class'] .= ' ' . AlertEnum::CLASS_NAME;
		else
			$htmlOptions['class'] = AlertEnum::CLASS_NAME;

		ob_start();
		echo Html::beginTag('div', $htmlOptions);
		if ($dismiss)
			echo Button::closeLink('&times;', BootstrapEnum::ALERT);
		echo $message;
		echo Html::endTag('div');
		return ob_get_clean();
	}
}