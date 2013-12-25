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
use yii\authclient\ClientInterface;

/**
 * Choice prints buttons for authentication via various auth clients.
 * By default this widget relies on presence of [[\yii\authclient\Collection]] among application components
 * to get auth clients information.
 *
 * Example:
 * ~~~
 * <?= yii\authclient\Choice::widget([
 *     'baseAuthUrl' => ['site/auth']
 * ]); ?>
 * ~~~
 *
 * You can customize the widget appearance by using [[beginWidget()]] and [[endWidget()]] syntax
 * along with using method {@link clientLink()} or {@link createClientUrl()}.
 * For example:
 *
 * ~~~
 * <?php $authChoice = yii\authclient\Choice::beginWidget(); ?>
 * <ul>
 * <?php foreach ($authChoice->getClients() as $client): ?>
 *     <li><?= $authChoice->clientLink($client); ?></li>
 * <?php endforeach; ?>
 * </ul>
 * <?php yii\authclient\Choice::endWidget(); ?>
 * ~~~
 *
 * @property ClientInterface[] $providers auth providers list.
 * @property array $baseAuthUrl configuration for the external services base authentication URL.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Choice extends Widget
{
	/**
	 * @var ClientInterface[] auth providers list.
	 */
	private $_clients;
	/**
	 * @var string name of the auth client collection application component.
	 * This component will be used to fetch {@link services} value if it is not set.
	 */
	public $clientCollection = 'auth';
	/**
	 * @var array configuration for the external clients base authentication URL.
	 */
	private $_baseAuthUrl;
	/**
	 * @var string name of the GET param , which should be used to passed auth client id to URL
	 * defined by {@link baseAuthUrl}.
	 */
	public $clientIdGetParamName = 'authclient';
	/**
	 * @var array the HTML attributes that should be rendered in the div HTML tag representing the container element.
	 */
	public $mainContainerHtmlOptions = [
		'class' => 'auth-clients'
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
	 * @param ClientInterface[] $clients auth providers
	 */
	public function setClients(array $clients)
	{
		$this->_clients = $clients;
	}

	/**
	 * @return ClientInterface[] auth providers
	 */
	public function getClients()
	{
		if ($this->_clients === null) {
			$this->_clients = $this->defaultClients();
		}
		return $this->_clients;
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
	 * Returns default auth clients list.
	 * @return ClientInterface[] auth clients list.
	 */
	protected function defaultClients()
	{
		/** @var $collection \yii\authclient\Collection */
		$collection = Yii::$app->getComponent($this->clientCollection);
		return $collection->getClients();
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
		unset($params[$this->clientIdGetParamName]);
		$baseAuthUrl = array_merge($baseAuthUrl, $params);
		return $baseAuthUrl;
	}

	/**
	 * Outputs client auth link.
	 * @param ClientInterface $client external auth client instance.
	 * @param string $text link text, if not set - default value will be generated.
	 * @param array $htmlOptions link HTML options.
	 */
	public function clientLink($client, $text = null, array $htmlOptions = [])
	{
		if ($text === null) {
			$text = Html::tag('span', '', ['class' => 'auth-icon ' . $client->getName()]);
			$text .= Html::tag('span', $client->getTitle(), ['class' => 'auth-title']);
		}
		if (!array_key_exists('class', $htmlOptions)) {
			$htmlOptions['class'] = 'auth-link ' . $client->getName();
		}
		if ($this->popupMode) {
			$viewOptions = $client->getViewOptions();
			if (isset($viewOptions['popupWidth'])) {
				$htmlOptions['data-popup-width'] = $viewOptions['popupWidth'];
			}
			if (isset($viewOptions['popupHeight'])) {
				$htmlOptions['data-popup-height'] = $viewOptions['popupHeight'];
			}
		}
		echo Html::a($text, $this->createClientUrl($client), $htmlOptions);
	}

	/**
	 * Composes client auth URL.
	 * @param ClientInterface $provider external auth client instance.
	 * @return string auth URL.
	 */
	public function createClientUrl($provider)
	{
		$this->autoRender = false;
		$url = $this->getBaseAuthUrl();
		$url[$this->clientIdGetParamName] = $provider->getId();
		return Html::url($url);
	}

	/**
	 * Renders the main content, which includes all external services links.
	 */
	protected function renderMainContent()
	{
		echo Html::beginTag('ul', ['class' => 'auth-clients clear']);
		foreach ($this->getClients() as $externalService) {
			echo Html::beginTag('li', ['class' => 'auth-client']);
			$this->clientLink($externalService);
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