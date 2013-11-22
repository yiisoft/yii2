<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap\helpers;

/**
 * Provides implementation for various bootstrap HTML helper functions
 *
 * @see http://getbootstrap.com/css
 * @see http://getbootstrap.com/components
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 2.0
 */
class Html extends \yii\helpers\Html
{
	/**
	 * CSS Class constants that can be used directly
	 */
	const FLOAT_LEFT 	     = 'pull-left';
	const FLOAT_RIGHT 	     = 'pull-right';
	const FLOAT_CENTER 	     = 'center-block';
	const NAVBAR_FLOAT_LEFT  = 'navbar-left';
	const NAVBAR_FLOAT_RIGHT = 'navbar-right';
	const SHOWN              = 'show';
	const HIDDEN             = 'hidden';
	const INVISIBLE          = 'invisible';
	const SCREEN_READER      = 'sr-only';
	const IMAGE_REPLACE 	 = 'text-hide';
	
	/**
	 * Generates a bootstrap icon.
	 * @param string $icon the bootstrap icon name without prefix (e.g. 'plus', 'pencil', 'trash')
	 * @param array $options html options for the icon container
	 * @param string $tag the icon container tag (usually 'span' or 'i') - defaults to 'span'
	 * @param string $prefix the css class prefix - defaults to 'glyphicon glyphicon-'
	 */
	public static function icon($icon, $options = [], $tag = 'span', $prefix = 'glyphicon glyphicon-')
	{
		$class = isset($options['class']) ? ' ' . $options['class'] : '';
		$options['class'] = $prefix . $icon . $class;
		return static::tag($tag, '', $options);
	}
	
	/**
	 * Generates a bootstrap generic close icon button for 
	 * dismissing content like modals and alerts.
	 * @param array $options html options for the close icon button
	 * @param string $label the close icon label - defaults to '&times;'
	 * @param string $tag the html tag for rendering the close icon - defaults to 'button'
	 * @see http://getbootstrap.com/css/#helper-classes-close
	 */
	public static function closeButton($options = [], $label = '&times;', $tag = 'button')
	{
		static::addCssClass($options, 'close');
		$options['type'] = 'button';
		$options['aria-hidden'] = 'true';
		return static::tag('button', $label, $options);
	}
	
	/**
	 * Generates a bootstrap caret.
	 * @param array $options html options for the caret container.
	 * @param string $tag the html tag for rendering the caret - defaults to 'span'
	 * @see http://getbootstrap.com/css/#helper-classes-carets
	 */
	public static function caret($options = [], $tag = 'span')
	{
		static::addCssClass($options, 'caret');
		return static::tag($tag, '', $options);
	}
}
