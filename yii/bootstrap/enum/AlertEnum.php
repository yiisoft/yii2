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
	const CLASS_NAME = 'alert';

	const TYPE_DEFAULT = '';
	const TYPE_SUCCESS = 'alert-success';
	const TYPE_INFORMATION = 'alert-info';
	const TYPE_ERROR = 'alert-error';

	const SIZE_BLOCK = 'alert-block';
}