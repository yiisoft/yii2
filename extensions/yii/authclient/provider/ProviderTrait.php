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

	/**
	 * Redirect to the given URL or simply close the popup window.
	 * @param mixed $url URL to redirect, could be a string or array config to generate a valid URL.
	 * @param boolean $enforceRedirect indicates if redirect should be performed even in case of popup window.
	 * @return \yii\web\Response response instance.
	 */
	public function redirect($url, $enforceRedirect = true)
	{
		$viewData = [
			'url' => $url,
			'enforceRedirect' => $enforceRedirect,
		];
		$viewFile = __DIR__ . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'redirect.php';

		$response = Yii::$app->getResponse();
		$response->content = Yii::$app->getView()->renderFile($viewFile, $viewData);
		return $response;
	}

	/**
	 * Redirect to the URL. If URL is null, {@link successUrl} will be used.
	 * @param string $url URL to redirect.
	 * @return \yii\web\Response response instance.
	 */
	public function redirectSuccess($url = null)
	{
		if ($url === null) {
			$url = $this->getSuccessUrl();
		}
		return $this->redirect($url);
	}

	/**
	 * Redirect to the {@link cancelUrl} or simply close the popup window.
	 * @param string $url URL to redirect.
	 * @return \yii\web\Response response instance.
	 */
	public function redirectCancel($url = null)
	{
		if ($url === null) {
			$url = $this->getCancelUrl();
		}
		return $this->redirect($url, false);
	}
}