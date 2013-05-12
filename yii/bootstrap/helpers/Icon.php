<?php
/**
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @link http://www.yiiframework.com/
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap\helpers;

use yii\helpers\Html;

/**
 * Icon allows you to render Bootstrap Glyphicons sets
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class Icon extends base\Icon
{
	/**
	 * Generates an icon.
	 * @param string $icon the icon type.
	 * @param array $htmlOptions additional HTML attributes.
	 * @return string the generated icon.
	 */
	public static function i($icon, $htmlOptions = array())
	{
		if (is_string($icon))
		{
			if (strpos($icon, 'icon-') === false)
				$icon = 'icon-' . implode(' icon-', explode(' ', $icon));

			// TODO: this method may should be added to ArrayHelper::add or ArrayHelper::append?
			if (isset($htmlOptions['class']))
				$htmlOptions['class'] .= ' ' . $icon;
			else
				$htmlOptions['class'] = $icon;

			return Html::tag('i', '', $htmlOptions);
		}
		return '';
	}
}