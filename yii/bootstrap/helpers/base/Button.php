<?php
/**
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @link http://www.yiiframework.com/
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap\helpers\base;

use yii\bootstrap\enum\AlertEnum;
use yii\bootstrap\enum\ButtonEnum;
use yii\bootstrap\enum\BootstrapEnum;

/**
 * Button provides methods to make use of bootstrap buttons in your application.
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class Button
{
	/**
	 * Returns a dismissal alert link
	 * @param string $text the text to use for the close link
	 * @return string the dismissal alert link
	 */
	public static function dismissLink($text = '&times;')
	{
		return \CHtml::link($text, '#', array('data-dismiss' => AlertEnum::CLASS_DEFAULT));
	}

	/**
	 * Returns a dismissal alert button
	 * @param string $text the text to use for the close button
	 * @return string the dismissal button
	 */
	public static function dismissButton($text = '&times')
	{
		return \CHtml::button($text, array(
			'type' => 'button',
			'class' => BootstrapEnum::CLASS_CLOSE,
			'data-dismiss' => Alert::CLASS_DEFAULT
		));
	}

	/**
	 * Returns a link button
	 * @param string $label the button label
	 * @param array $htmlOptions the HTML attributes of the button
	 * @return string the generated button
	 */
	public static function link($label, $htmlOptions = array())
	{
		// TODO: consider method add or append to ArrayHelper class
		if (isset($htmlOptions['class']))
			$htmlOptions['class'] .= ' ' . ButtonEnum::TYPE_LINK;
		else
			$htmlOptions['class'] = AlertEnum::TYPE_LINK;

		return \CHtml::link($label, '#', $htmlOptions);
	}
}