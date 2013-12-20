<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OpenId;

/**
 * Class YandexOpenId
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class YandexOpenId extends OpenId
{
	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		$this->setIdentity('http://openid.yandex.ru');
		$this->requiredAttributes = [
			'namePerson',
			'contact/email',
		];
	}

	/**
	 * @inheritdoc
	 */
	protected function defaultViewOptions()
	{
		return [
			'popupWidth' => 900,
			'popupHeight' => 550,
		];
	}
}