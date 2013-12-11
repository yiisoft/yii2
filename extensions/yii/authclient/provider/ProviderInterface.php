<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\provider;

/**
 * Class ProviderInterface
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
interface ProviderInterface
{
	/**
	 * @param string $id service id.
	 */
	public function setId($id);

	/**
	 * @return string service id
	 */
	public function getId();

	/**
	 * @return string service name.
	 */
	public function getName();

	/**
	 * @param string $name service name.
	 */
	public function setName($name);

	/**
	 * @return string service title.
	 */
	public function getTitle();

	/**
	 * @param string $title service title.
	 */
	public function setTitle($title);

	/**
	 * @param string $url successful URL.
	 */
	public function setSuccessUrl($url);

	/**
	 * @return string successful URL.
	 */
	public function getSuccessUrl();

	/**
	 * @param string $url cancel URL.
	 */
	public function setCancelUrl($url);

	/**
	 * @return string cancel URL.
	 */
	public function getCancelUrl();

	/**
	 * Authenticate the user.
	 * @return boolean whether user was successfully authenticated.
	 */
	public function authenticate();
}