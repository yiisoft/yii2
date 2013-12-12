<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\widgets;

use yii\base\Widget;
use Yii;
use yii\helpers\Html;
use yii\authclient\provider\ProviderInterface;

/**
 * Class Choice
 *
 * @property ProviderInterface[] $providers auth providers list.
 * @property array $baseAuthUrl configuration for the external services base authentication URL.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Choice extends Widget
{
	/**
	 * @var ProviderInterface[] auth providers list.
	 */
	private $_providers;
	/**
	 * @var string name of the auth provider collection application component.
	 * This component will be used to fetch {@link services} value if it is not set.
	 */
	public $providerCollection;
	/**
	 * @var array configuration for the external services base authentication URL.
	 */
	private $_baseAuthUrl;
	/**
	 * @var string name of the GET param , which should be used to passed auth provider id to URL
	 * defined by {@link baseAuthUrl}.
	 */
	public $providerIdGetParamName = 'provider';
	/**
	 * @var array the HTML attributes that should be rendered in the div HTML tag representing the container element.
	 */
	public $mainContainerHtmlOptions = [
		'class' => 'services'
	];
	/**
	 * @var boolean indicates if popup window should be used instead of direct links.
	 */
	public $popupMode = true;
	/**
	 * @var boolean indicates if widget content, should be rendered automatically.
	 * Note: this value automatically set to 'false' at the first call of [[createProviderUrl()]]
	 */
	public $autoRender = true;

	/**
	 * @param ProviderInterface[] $providers auth providers
	 */
	public function setProviders(array $providers)
	{
		$this->_providers = $providers;
	}

	/**
	 * @return ProviderInterface[] auth providers
	 */
	public function getProviders()
	{
		if ($this->_providers === null) {
			$this->_providers = $this->defaultProviders();
		}
		return $this->_providers;
	}

	/**
	 * @param array $baseAuthUrl base auth URL configuration.
	 */
	public function setBaseAuthUrl(array $baseAuthUrl)
	{
		$this->_baseAuthUrl = $baseAuthUrl;
	}

	/**
	 * @return array base auth URL configuration.
	 */
	public function getBaseAuthUrl()
	{
		if (!is_array($this->_baseAuthUrl)) {
			$this->_baseAuthUrl = $this->defaultBaseAuthUrl();
		}
		return $this->_baseAuthUrl;
	}

	/**
	 * Returns default auth providers list.
	 * @return ProviderInterface[] auth providers list.
	 */
	protected function defaultProviders()
	{
		/** @var $collection \yii\authclient\provider\Collection */
		$collection = Yii::$app->getComponent($this->providerCollection);
		return $collection->getProviders();
	}

	/**
	 * Composes default base auth URL configuration.
	 * @return array base auth URL configuration.
	 */
	protected function defaultBaseAuthUrl()
	{
		$baseAuthUrl = [
			Yii::$app->controller->getRoute()
		];
		$params = $_GET;
		unset($params[$this->providerIdGetParamName]);
		$baseAuthUrl = array_merge($baseAuthUrl, $params);
		return $baseAuthUrl;
	}

	/**
	 * Outputs external service auth link.
	 * @param ProviderInterface $service external auth service instance.
	 * @param string $text link text, if not set - default value will be generated.
	 * @param array $htmlOptions link HTML options.
	 */
	public function providerLink($service, $text = null, array $htmlOptions = [])
	{
		if ($text === null) {
			$text = Html::tag('span', ['class' => 'auth-icon ' . $service->getName()], '');
			$text .= Html::tag('span', ['class' => 'auth-title'], $service->getTitle());
		}
		if (!array_key_exists('class', $htmlOptions)) {
			$htmlOptions['class'] = 'auth-link ' . $service->getName();
		}
		if ($this->popupMode) {
			if (isset($service->popupWidth)) {
				$htmlOptions['data-popup-width'] = $service->popupWidth;
			}
			if (isset($service->popupHeight)) {
				$htmlOptions['data-popup-height'] = $service->popupHeight;
			}
		}
		echo Html::a($text, $this->createProviderUrl($service), $htmlOptions);
	}

	/**
	 * Composes external service auth URL.
	 * @param ProviderInterface $provider external auth service instance.
	 * @return string auth URL.
	 */
	public function createProviderUrl($provider)
	{
		$this->autoRender = false;
		$url = $this->getBaseAuthUrl();
		$url[$this->providerIdGetParamName] = $provider->getId();
		return Html::url($url);
	}

	/**
	 * Renders the main content, which includes all external services links.
	 */
	protected function renderMainContent()
	{
		echo Html::beginTag('ul', ['class' => 'auth-services clear']);
		foreach ($this->getProviders() as $externalService) {
			echo Html::beginTag('li', ['class' => 'auth-service']);
			$this->providerLink($externalService);
			echo Html::endTag('li');
		}
		echo Html::endTag('ul');
	}

	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		if ($this->popupMode) {
			$view = Yii::$app->getView();
			ChoiceAsset::register($view);
			$view->registerJs("\$('#" . $this->getId() . "').authchoice();");
		}
		$this->mainContainerHtmlOptions['id'] = $this->getId();
		echo Html::beginTag('div', $this->mainContainerHtmlOptions);
	}

	/**
	 * Runs the widget.
	 */
	public function run()
	{
		if ($this->autoRender) {
			$this->renderMainContent();
		}
		echo Html::endTag('div');
	}
}