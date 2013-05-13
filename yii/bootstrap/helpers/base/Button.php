<?php
/**
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @link http://www.yiiframework.com/
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap\helpers\base;

use yii\bootstrap\enum\Enum;
use yii\helpers\Html;

/**
 * Button provides methods to make use of bootstrap buttons in your application.
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class Button
{
	/**
	 * constants
	 */
	const TYPE_DEFAULT = 'btn';
	const TYPE_PRIMARY = 'btn-primary';
	const TYPE_INFO = 'btn-info';
	const TYPE_SUCCESS = 'btn-success';
	const TYPE_WARNING = 'btn-warning';
	const TYPE_DANGER = 'btn-danger';
	const TYPE_INVERSE = 'btn-inverse';
	const TYPE_LINK = 'btn-link';

	const SIZE_DEFAULT = '';
	const SIZE_LARGE = 'btn-large';
	const SIZE_SMALL = 'btn-small';
	const SIZE_MINI = 'btn-mini';
	const SIZE_BLOCK = 'btn-block';

	/**
	 * Returns a dismissal alert link
	 * @param string $text
	 * @param string $dismiss what to dismiss (alert or modal)
	 * @return string the dismissal alert link
	 */
	public static function closeLink($text = '&times;', $dismiss = null)
	{
		$options = array('class' => Enum::CLOSE);
		if(null !== $dismiss)
			$options['data-dismiss'] = $dismiss;
		return Html::a($text, '#', $options);
	}

	/**
	 * Returns a dismissal button
	 * @param string $text the text to use for the close button
	 * @param string $dismiss what to dismiss (alert or modal)
	 * @return string the dismissal button
	 */
	public static function closeButton($text = '&times', $dismiss = null)
	{
		$options = array('type' => 'button', 'class' => Enum::CLOSE);
		if(null !== $dismiss)
			$options['data-dismiss'] = $dismiss;

		return Html::button($text, null, null, $options);
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
			$htmlOptions['class'] .= ' ' . static::TYPE_LINK;
		else
			$htmlOptions['class'] = static::TYPE_LINK;

		return Html::a($label, '#', $htmlOptions);
	}
}