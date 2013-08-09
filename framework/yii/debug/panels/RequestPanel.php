<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\base\InlineAction;
use yii\bootstrap\Tabs;
use yii\debug\Panel;
use yii\helpers\Html;
use yii\web\Response;

/**
 * Debugger panel that collects and displays request data.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class RequestPanel extends Panel
{
	public function getName()
	{
		return 'Request';
	}

	public function getSummary()
	{
		$url = $this->getUrl();
		$statusCode = $this->data['statusCode'];
		if ($statusCode === null) {
			$statusCode = 200;
		}
		if ($statusCode >= 200 && $statusCode < 300) {
			$class = 'label-success';
		} elseif ($statusCode >= 100 && $statusCode < 200) {
			$class = 'label-info';
		} else {
			$class = 'label-important';
		}
		$statusText = Html::encode(isset(Response::$httpStatuses[$statusCode]) ? Response::$httpStatuses[$statusCode] : '');

		return <<<EOD
<div class="yii-debug-toolbar-block">
	<a href="$url" title="Status code: $statusCode $statusText">Status: <span class="label $class">$statusCode</span></a>
</div>
<div class="yii-debug-toolbar-block">
	<a href="$url">Action: <span class="label">{$this->data['action']}</span></a>
</div>
EOD;
	}

	public function getDetail()
	{
		$data = array(
			'Route' => $this->data['route'],
			'Action' => $this->data['action'],
			'Parameters' => $this->data['actionParams'],
		);
		return Tabs::widget(array(
			'items' => array(
				array(
					'label' => 'Parameters',
					'content' => $this->renderData('Routing', $data)
						. $this->renderData('$_GET', $this->data['GET'])
						. $this->renderData('$_POST', $this->data['POST'])
						. $this->renderData('$_FILES', $this->data['POST'])
						. $this->renderData('$_COOKIE', $this->data['COOKIE']),
					'active' => true,
				),
				array(
					'label' => 'Headers',
					'content' => $this->renderData('Request Headers', $this->data['requestHeaders'])
						. $this->renderData('Response Headers', $this->data['responseHeaders']),
				),
				array(
					'label' => 'Session',
					'content' => $this->renderData('$_SESSION', $this->data['SESSION'])
						. $this->renderData('Flashes', $this->data['flashes']),
				),
				array(
					'label' => '$_SERVER',
					'content' => $this->renderData('$_SERVER', $this->data['SERVER']),
				),
			),
		));
	}

	public function save()
	{
		if (function_exists('apache_request_headers')) {
			$requestHeaders = apache_request_headers();
		} elseif (function_exists('http_get_request_headers')) {
			$requestHeaders = http_get_request_headers();
		} else {
			$requestHeaders = array();
		}
		$responseHeaders = array();
		foreach (headers_list() as $header) {
			if (($pos = strpos($header, ':')) !== false) {
				$name = substr($header, 0, $pos);
				$value = trim(substr($header, $pos + 1));
				if (isset($responseHeaders[$name])) {
					if (!is_array($responseHeaders[$name])) {
						$responseHeaders[$name] = array($responseHeaders[$name], $value);
					} else {
						$responseHeaders[$name][] = $value;
					}
				} else {
					$responseHeaders[$name] = $value;
				}
			} else {
				$responseHeaders[] = $header;
			}
		}
		if (Yii::$app->requestedAction) {
			if (Yii::$app->requestedAction instanceof InlineAction) {
				$action = get_class(Yii::$app->requestedAction->controller) . '::' . Yii::$app->requestedAction->actionMethod . '()';
			} else {
				$action = get_class(Yii::$app->requestedAction) . '::run()';
			}
		} else {
			$action = null;
		}
		/** @var \yii\web\Session $session */
		$session = Yii::$app->getComponent('session', false);
		return array(
			'flashes' => $session ? $session->getAllFlashes() : array(),
			'statusCode' => Yii::$app->getResponse()->getStatusCode(),
			'requestHeaders' => $requestHeaders,
			'responseHeaders' => $responseHeaders,
			'route' => Yii::$app->requestedAction ? Yii::$app->requestedAction->getUniqueId() : Yii::$app->requestedRoute,
			'action' => $action,
			'actionParams' => Yii::$app->requestedParams,
			'SERVER' => empty($_SERVER) ? array() : $_SERVER,
			'GET' => empty($_GET) ? array() : $_GET,
			'POST' => empty($_POST) ? array() : $_POST,
			'COOKIE' => empty($_COOKIE) ? array() : $_COOKIE,
			'FILES' => empty($_FILES) ? array() : $_FILES,
			'SESSION' => empty($_SESSION) ? array() : $_SESSION,
		);
	}

	protected function renderData($caption, $values)
	{
		if (empty($values)) {
			return "<h3>$caption</h3>\n<p>Empty.</p>";
		}
		$rows = array();
		foreach ($values as $name => $value) {
			$rows[] = '<tr><th style="width: 200px;">' . Html::encode($name) . '</th><td><div style="overflow:auto">' . Html::encode(var_export($value, true)) . '</div></td></tr>';
		}
		$rows = implode("\n", $rows);
		return <<<EOD
<h3>$caption</h3>
<table class="table table-condensed table-bordered table-striped table-hover" style="table-layout: fixed;">
<thead><tr><th style="width: 200px;">Name</th><th>Value</th></tr></thead>
<tbody>
$rows
</tbody>
</table>
EOD;
	}
}
