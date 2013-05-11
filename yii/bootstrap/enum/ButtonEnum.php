<?php
/**
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @link http://www.yiiframework.com/
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap\enum;

/**
 * ButtonEnum provides easy access to all predefined button set of named values
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class ButtonEnum
{
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

}