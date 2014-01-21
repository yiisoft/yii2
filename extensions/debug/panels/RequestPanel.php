<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\base\InlineAction;
use yii\debug\Panel;

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
		return Yii::$app->view->render('panels/request/summary', ['panel' => $this]);
	}

	public function getDetail()
	{
		return Yii::$app->view->render('panels/request/detail', ['panel' => $this]);
	}

	public function save()
	{
		if (function_exists('apache_request_headers')) {
			$requestHeaders = apache_request_headers();
		} elseif (function_exists('http_get_request_headers')) {
			$requestHeaders = http_get_request_headers();
		} else {
			$requestHeaders = [];
		}
		$responseHeaders = [];
		foreach (headers_list() as $header) {
			if (($pos = strpos($header, ':')) !== false) {
				$name = substr($header, 0, $pos);
				$value = trim(substr($header, $pos + 1));
				if (isset($responseHeaders[$name])) {
					if (!is_array($responseHeaders[$name])) {
						$responseHeaders[$name] = [$responseHeaders[$name], $value];
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
		return [
			'flashes' => $session ? $session->getAllFlashes() : [],
			'statusCode' => Yii::$app->getResponse()->getStatusCode(),
			'requestHeaders' => $requestHeaders,
			'responseHeaders' => $responseHeaders,
			'route' => Yii::$app->requestedAction ? Yii::$app->requestedAction->getUniqueId() : Yii::$app->requestedRoute,
			'action' => $action,
			'actionParams' => Yii::$app->requestedParams,
			'requestBody' => Yii::$app->getRequest()->getRawBody() == '' ? [] : [
				'Content Type' => Yii::$app->getRequest()->getContentType(),
				'Raw' => Yii::$app->getRequest()->getRawBody(),
				'Decoded to Params' => Yii::$app->getRequest()->getBodyParams(),
			],
			'SERVER' => empty($_SERVER) ? [] : $_SERVER,
			'GET' => empty($_GET) ? [] : $_GET,
			'POST' => empty($_POST) ? [] : $_POST,
			'COOKIE' => empty($_COOKIE) ? [] : $_COOKIE,
			'FILES' => empty($_FILES) ? [] : $_FILES,
			'SESSION' => empty($_SESSION) ? [] : $_SESSION,
		];
	}

}
