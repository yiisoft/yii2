<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use yii\base\Event;

/**
 * MissingTranslationEvent represents the parameter for the [[MessageSource::EVENT_MISSING_TRANSLATION]] event.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MissingTranslationEvent extends Event
{
	/**
	 * @var string the message to be translated. An event handler may overwrite this property
	 * with a translated version if possible.
	 */
	public $message;
	/**
	 * @var string the category that the message belongs to
	 */
	public $category;
	/**
	 * @var string the language ID (e.g. en-US) that the message is to be translated to
	 */
	public $language;
}
