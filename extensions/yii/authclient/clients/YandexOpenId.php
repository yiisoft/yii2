<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OpenId;

/**
 * YandexOpenId allows authentication via Yandex OpenId.
 * Unlike Yandex OAuth you do not need to register your application anywhere in order to use Yandex OpenId.
 *
 * Example application configuration:
 *
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'yandex' => [
 *                 'class' => 'yii\authclient\clients\YandexOpenId'
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class YandexOpenId extends OpenId
{
	/**
	 * @inheritdoc
	 */
	public $authUrl = 'http://openid.yandex.ru';
	/**
	 * @inheritdoc
	 */
	public $requiredAttributes = [
		'namePerson',
		'contact/email',
	];

	/**
	 * @inheritdoc
	 */
	protected function defaultNormalizeUserAttributeMap()
	{
		return [
			'name' => 'namePerson',
			'email' => 'contact/email',
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

	/**
	 * @inheritdoc
	 */
	protected function defaultName()
	{
		return 'yandex';
	}

	/**
	 * @inheritdoc
	 */
	protected function defaultTitle()
	{
		return 'Yandex';
	}
}
