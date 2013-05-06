<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use Yii;

/**
 * PhpMessageSource represents a message source that stores translated messages in PHP scripts.
 *
 * PhpMessageSource uses PHP arrays to keep message translations.
 *
 * - Each PHP script contains one array which stores the message translations in one particular
 *   language and for a single message category;
 * - Each PHP script is saved as a file named as `[[basePath]]/LanguageID/CategoryName.php`;
 * - Within each PHP script, the message translations are returned as an array like the following:
 *
 * ~~~
 * return array(
 *     'original message 1' => 'translated message 1',
 *     'original message 2' => 'translated message 2',
 * );
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class PhpMessageSource extends MessageSource
{
	/**
	 * @var string the base path for all translated messages. Defaults to null, meaning
	 * the "messages" subdirectory of the application directory (e.g. "protected/messages").
	 */
	public $basePath = '@app/messages';
	/**
	 * @var array mapping between message categories and the corresponding message file paths.
	 * The file paths are relative to [[basePath]]. For example,
	 *
	 * ~~~
	 * array(
	 *     'core' => 'core.php',
	 *     'ext' => 'extensions.php',
	 * )
	 * ~~~
	 */
	public $fileMap;

	/**
	 * Loads the message translation for the specified language and category.
	 * @param string $category the message category
	 * @param string $language the target language
	 * @return array the loaded messages
	 */
	protected function loadMessages($category, $language)
	{
		$messageFile = Yii::getAlias($this->basePath) . "/$language/";
		if (isset($this->fileMap[$category])) {
			$messageFile .= $this->fileMap[$category];
		} elseif (($pos = strrpos($category, '\\')) !== false) {
			$messageFile .= (substr($category, $pos) . '.php');
		} else {
			$messageFile .= "$category.php";
		}
		if (is_file($messageFile)) {
			$messages = include($messageFile);
			if (!is_array($messages)) {
				$messages = array();
			}
			return $messages;
		} else {
			Yii::error("The message file for category '$category' does not exist: $messageFile", __METHOD__);
			return array();
		}
	}
}
