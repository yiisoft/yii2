<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient;

use yii\base\Object;

/**
 * Token represents OAuth token.
 *
 * @property integer $expireDuration Token expiration duration. Note that the type of this property differs in
 * getter and setter. See [[getExpireDuration()]] and [[setExpireDuration()]] for details.
 * @property string $expireDurationParamKey Expire duration param key.
 * @property boolean $isExpired Is token expired. This property is read-only.
 * @property boolean $isValid Is token valid. This property is read-only.
 * @property array $params This property is read-only.
 * @property string $token Token value.
 * @property string $tokenSecret Token secret value.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class OAuthToken extends Object
{
	/**
	 * @var string key in {@link _params} array, which stores token key.
	 */
	public $tokenParamKey = 'oauth_token';
	/**
	 * @var string key in {@link _params} array, which stores token secret key.
	 */
	public $tokenSecretParamKey = 'oauth_token_secret';
	/**
	 * @var string key in {@link _params} array, which stores token expiration duration.
	 * If not set will attempt to fetch its value automatically.
	 */
	private $_expireDurationParamKey;
	/**
	 * @var array token parameters.
	 */
	private $_params = [];
	/**
	 * @var integer object creation timestamp.
	 */
	public $createTimestamp;

	public function init()
	{
		if ($this->createTimestamp === null) {
			$this->createTimestamp = time();
		}
	}

	/**
	 * @param string $expireDurationParamKey expire duration param key.
	 */
	public function setExpireDurationParamKey($expireDurationParamKey) {
		$this->_expireDurationParamKey = $expireDurationParamKey;
	}

	/**
	 * @return string expire duration param key.
	 */
	public function getExpireDurationParamKey() {
		if ($this->_expireDurationParamKey === null) {
			$this->_expireDurationParamKey = $this->defaultExpireDurationParamKey();
		}
		return $this->_expireDurationParamKey;
	}

	/**
	 * @return array
	 */
	public function getParams() {
		return $this->_params;
	}

	/**
	 * @param array $params
	 */
	public function setParams(array $params) {
		$this->_params = $params;
	}

	/**
	 * Sets param by name.
	 * @param string $name param name.
	 * @param mixed $value param value,
	 */
	public function setParam($name, $value) {
		$this->_params[$name] = $value;
	}

	/**
	 * Returns param by name.
	 * @param string $name param name.
	 * @return mixed param value.
	 */
	public function getParam($name) {
		return isset($this->_params[$name]) ? $this->_params[$name] : null;
	}

	/**
	 * Sets token value.
	 * @param string $token token value.
	 * @return static self reference.
	 */
	public function setToken($token) {
		$this->setParam($this->tokenParamKey, $token);
	}

	/**
	 * Returns token value.
	 * @return string token value.
	 */
	public function getToken() {
		return $this->getParam($this->tokenParamKey);
	}

	/**
	 * Sets the token secret value.
	 * @param string $tokenSecret token secret.
	 */
	public function setTokenSecret($tokenSecret) {
		$this->setParam($this->tokenSecretParamKey, $tokenSecret);
	}

	/**
	 * Returns the token secret value.
	 * @return string token secret value.
	 */
	public function getTokenSecret() {
		return $this->getParam($this->tokenSecretParamKey);
	}

	/**
	 * Sets token expire duration.
	 * @param string $expireDuration token expiration duration.
	 */
	public function setExpireDuration($expireDuration) {
		$this->setParam($this->getExpireDurationParamKey(), $expireDuration);
	}

	/**
	 * Returns the token expiration duration.
	 * @return integer token expiration duration.
	 */
	public function getExpireDuration() {
		return $this->getParam($this->getExpireDurationParamKey());
	}

	/**
	 * Fetches default expire duration param key.
	 * @return string expire duration param key.
	 */
	protected function defaultExpireDurationParamKey() {
		$expireDurationParamKey = 'expires_in';
		foreach ($this->getParams() as $name => $value) {
			if (strpos($name, 'expir') !== false) {
				$expireDurationParamKey = $name;
				break;
			}
		}
		return $expireDurationParamKey;
	}

	/**
	 * Checks if token has expired.
	 * @return boolean is token expired.
	 */
	public function getIsExpired() {
		$expirationDuration = $this->getExpireDuration();
		if (empty($expirationDuration)) {
			return false;
		}
		return (time() >= ($this->createTimestamp + $expirationDuration));
	}

	/**
	 * Checks if token is valid.
	 * @return boolean is token valid.
	 */
	public function getIsValid() {
		$token = $this->getToken();
		return (!empty($token) && !$this->getIsExpired());
	}
}