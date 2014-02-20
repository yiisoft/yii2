<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;

use yii\base\Action;
use yii\base\NotSupportedException;
use yii\debug\Panel;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use yii\web\Response;
use Yii;

/**
 * Debug Action is used by [[DebugPanel]] to perform elasticsearch queries using ajax.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class DebugAction extends Action
{
	/**
	 * @var string the connection id to use
	 */
	public $db;
	/**
     * @var Panel
	 */
	public $panel;

	public function run($logId, $tag)
	{
		$this->controller->loadData($tag);

		$timings = $this->panel->calculateTimings();
		ArrayHelper::multisort($timings, 3, SORT_DESC);
		if (!isset($timings[$logId])) {
			throw new HttpException(404, 'Log message not found.');
		}
		$message = $timings[$logId][1];
		if (($pos = mb_strpos($message, "#")) !== false) {
			$url = mb_substr($message, 0, $pos);
			$body = mb_substr($message, $pos + 1);
		} else {
			$url = $message;
			$body = null;
		}
		$method = mb_substr($url, 0, $pos = mb_strpos($url, ' '));
		$url = mb_substr($url, $pos + 1);

		$options = ['pretty' => true];

		/** @var Connection $db */
		$db = \Yii::$app->getComponent($this->db);
		$time = microtime(true);
		switch($method) {
			case 'GET': $result = $db->get($url, $options, $body, true); break;
			case 'POST': $result = $db->post($url, $options, $body, true); break;
			case 'PUT': $result = $db->put($url, $options, $body, true); break;
			case 'DELETE': $result = $db->delete($url, $options, $body, true); break;
			case 'HEAD': $result = $db->head($url, $options, $body); break;
			default:
				throw new NotSupportedException("Request method '$method' is not supported by elasticsearch.");
		}
		$time = microtime(true) - $time;

		if ($result === true) {
			$result = '<span class="label label-success">success</span>';
		} elseif ($result === false) {
			$result = '<span class="label label-danger">no success</span>';
		}

		Yii::$app->response->format = Response::FORMAT_JSON;
		return [
			'time' => sprintf('%.1f ms', $time * 1000),
			'result' => $result,
		];
	}
}
