<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OpenId;

/**
 * Class GoogleOpenId
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class GoogleOpenId extends OpenId
{
	public function init()
	{
		parent::init();
		$this->setIdentity('https://www.google.com/accounts/o8/id');
		$this->requiredAttributes = [
			'namePerson/first',
			'namePerson/last',
			'contact/email',
			'pref/language',
		];
	}
}