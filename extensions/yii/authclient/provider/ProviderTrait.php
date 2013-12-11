<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\provider;

use Yii;
use yii\helpers\StringHelper;

/**
 * Class ProviderTrait
 *
 * @see ProviderInterface
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
trait ProviderTrait
{
	/**
	 * @var string service id.
	 * This value mainly used as HTTP request parameter.
	 */
	private $_id;
	/**
	 * @var string service unique name.
	 * This value may be used in database records, CSS files and so on.
	 */
	private $_name;
	/**
	 * @var string service title to display in views.
	 */
	private $_title;
	/**
	 * @var string the redirect url after successful authorization.
	 */
	private $_successUrl = '';
	/**
	 * @var string the redirect url after unsuccessful authorization (e.g. user canceled).
	 */
	private $_cancelUrl = '';

	/**
	 * @param string $id service id.
	 */
	public function setId($id)
	{
		$this->_id = $id;
	}

	/**
	 * @return string service id
	 */
	public function getId()
	{
		if (empty($this->_id)) {
			$this->_id = $this->getName();
		}
		return $this->_id;
	}

	/**
	 * @return string service name.
	 */
	public function getName()
	{
		if ($this->_name === null) {
			$this->_name = $this->defaultName();
		}
		return $this->_name;
	}

	/**
	 * @param string $name service name.
	 */
	public function setName($name)
	{
		$this->_name = $name;
	}

	/**
	 * @return string service title.
	 */
	public function getTitle()
	{
		if ($this->_title === null) {
			$this->_title = $this->defaultTitle();
		}
		return $this->_title;
	}

	/**
	 * @param string $title service title.
	 */
	public function setTitle($title)
	{
		$this->_title = $title;
	}

	/**
	 * @param string $url successful URL.
	 */
	public function setSuccessUrl($url)
	{
		$this->_successUrl = $url;
	}

	/**
	 * @return string successful URL.
	 */
	public function getSuccessUrl()
	{
		if (empty($this->_successUrl)) {
			$this->_successUrl = $this->defaultSuccessUrl();
		}
		return $this->_successUrl;
	}

	/**
	 * @param string $url cancel URL.
	 */
	public function setCancelUrl($url)
	{
		$this->_cancelUrl = $url;
	}

	/**
	 * @return string cancel URL.
	 */
	public function getCancelUrl()
	{
		if (empty($this->_cancelUrl)) {
			$this->_cancelUrl = $this->defaultCancelUrl();
		}
		return $this->_cancelUrl;
	}

	/**
	 * Generates service name.
	 * @return string service name.
	 */
	protected function defaultName()
	{
		return StringHelper::basename(get_class($this));
	}

	/**
	 * Generates service title.
	 * @return string service title.
	 */
	protected function defaultTitle()
	{
		return StringHelper::basename(get_class($this));
	}

	/**
	 * Creates default {@link successUrl} value.
	 * @return string success URL value.
	 */
	protected function defaultSuccessUrl()
	{
		return Yii::$app->getUser()->getReturnUrl();
	}

	/**
	 * Creates default {@link cancelUrl} value.
	 * @return string cancel URL value.
	 */
	protected function defaultCancelUrl()
	{
		return Yii::$app->getRequest()->getAbsoluteUrl();
	}
}