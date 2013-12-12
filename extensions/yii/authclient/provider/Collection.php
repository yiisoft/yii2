<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\provider;

use yii\base\Component;
use yii\base\InvalidParamException;
use Yii;

/**
 * Collection is a storage for all auth providers in the application.
 *
 * Example application configuration:
 *
 * ~~~
 * 'components' => [
 *     'auth' => [
 *         'class' => 'yii\authclient\provider\Collection',
 *         'providers' => [
 *             'google' => [
 *                 'class' => 'yii\authclient\provider\GoogleOpenId'
 *             ],
 *             'facebook' => [
 *                 'class' => 'yii\authclient\provider\Facebook',
 *                 'clientId' => 'facebook_client_id',
 *                 'clientSecret' => 'facebook_client_secret',
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
class Collection extends Component
{
	/**
	 * @var array list of Auth providers with their configuration in format: 'providerId' => [...]
	 */
	private $_providers = [];

	/**
	 * @param array $providers list of auth providers
	 */
	public function setProviders(array $providers)
	{
		$this->_providers = $providers;
	}

	/**
	 * @return ProviderInterface[] list of auth providers.
	 */
	public function getProviders()
	{
		$providers = [];
		foreach ($this->_providers as $id => $provider) {
			$providers[$id] = $this->getProvider($id);
		}
		return $providers;
	}

	/**
	 * @param string $id service id.
	 * @return ProviderInterface auth service instance.
	 * @throws InvalidParamException on non existing provider request.
	 */
	public function getProvider($id)
	{
		if (!array_key_exists($id, $this->_providers)) {
			throw new InvalidParamException("Unknown auth provider '{$id}'.");
		}
		if (!is_object($this->_providers[$id])) {
			$this->_providers[$id] = $this->createProvider($id, $this->_providers[$id]);
		}
		return $this->_providers[$id];
	}

	/**
	 * Checks if provider exists in the hub.
	 * @param string $id provider id.
	 * @return boolean whether provider exist.
	 */
	public function hasProvider($id)
	{
		return array_key_exists($id, $this->_providers);
	}

	/**
	 * Creates auth provider instance from its array configuration.
	 * @param string $id auth provider id.
	 * @param array $config auth provider instance configuration.
	 * @return ProviderInterface auth provider instance.
	 */
	protected function createProvider($id, array $config)
	{
		$config['id'] = $id;
		return Yii::createObject($config);
	}
}