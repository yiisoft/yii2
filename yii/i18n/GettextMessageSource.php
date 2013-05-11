<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use Yii;

class GettextMessageSource extends MessageSource
{
	const MO_FILE_EXT = '.mo';
	const PO_FILE_EXT = '.po';

	/**
	 * @var string
	 */
	public $basePath = '@app/messages';
	/**
	 * @var string
	 */
	public $catalog = 'messages';
	/**
	 * @var boolean
	 */
	public $useMoFile = true;
	/**
	 * @var boolean
	 */
	public $useBigEndian = false;

	protected function loadMessages($category, $language)
	{
		$messageFile = Yii::getAlias($this->basePath) . '/' . $language . '/' . $this->catalog;
		if ($this->useMoFile) {
			$messageFile .= static::MO_FILE_EXT;
		} else {
			$messageFile .= static::PO_FILE_EXT;
		}

		if (is_file($messageFile)) {
			if ($this->useMoFile) {
				$gettextFile = new GettextMoFile(array('useBigEndian' => $this->useBigEndian));
			} else {
				$gettextFile = new GettextPoFile();
			}
			$messages = $gettextFile->load($messageFile, $category);
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
