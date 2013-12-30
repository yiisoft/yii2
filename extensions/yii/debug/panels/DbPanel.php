<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\debug\Panel;
use yii\log\Logger;
use yii\debug\models\search\Db;

/**
 * Debugger panel that collects and displays database queries performed.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DbPanel extends Panel
{

	/**
	 * @var array db queries info extracted to array as models, to use with data provider.
	 */
	private $_models;

	/**
	 * @var array current database request timings
	 */
	private $_timings;

	public function getName()
	{
		return 'Database';
	}

	public function getSummary()
	{
		$timings = $this->calculateTimings();
		$queryCount = count($timings);
		$queryTime = number_format($this->getTotalQueryTime($timings) * 1000) . ' ms';

		return  Yii::$app->view->render('panels/db/summary',[
			'timings' => $this->calculateTimings(), 
			'panel' => $this,
			'queryCount' => $queryCount,
			'queryTime' => $queryTime,
		]);
	}

	public function getDetail()
	{
		$searchModel = new Db();
		$dataProvider = $searchModel->search($_GET, $this->getModels());

		return  Yii::$app->view->render('panels/db/detail',[
			'panel' => $this,
			'dataProvider' => $dataProvider,
			'searchModel' => $searchModel,
		]);
	}

	protected function calculateTimings()
	{
		if ($this->_timings !== null) {
			return $this->_timings;
		}
		$messages = $this->data['messages'];
		$timings = [];
		$stack = [];
		foreach ($messages as $i => $log) {
			list($token, $level, $category, $timestamp) = $log;
			$log[5] = $i;
			if ($level == Logger::LEVEL_PROFILE_BEGIN) {
				$stack[] = $log;
			} elseif ($level == Logger::LEVEL_PROFILE_END) {
				if (($last = array_pop($stack)) !== null && $last[0] === $token) {
					$timings[$last[5]] = [count($stack), $token, $last[3], $timestamp - $last[3], $last[4]];
				}
			}
		}

		$now = microtime(true);
		while (($last = array_pop($stack)) !== null) {
			$delta = $now - $last[3];
			$timings[$last[5]] = [count($stack), $last[0], $last[2], $delta, $last[4]];
		}
		ksort($timings);
		return $this->_timings = $timings;
	}

	public function save()
	{
		$target = $this->module->logTarget;
		$messages = $target->filterMessages($target->messages, Logger::LEVEL_PROFILE, ['yii\db\Command::query', 'yii\db\Command::execute']);
		return ['messages' => $messages];
	}

	/**
	 * Returns total queries time.
	 * @param array $timings
	 * @return integer total time
	 */
	protected function getTotalQueryTime($timings)
	{
		$queryTime = 0;

		foreach ($timings as $timing) {
			$queryTime += $timing[3];
		}

		return $queryTime;
	}

	/**
	 * Returns array of models that represents logs of the current request. Can be used with data providers,
	 * like yii\data\ArrayDataProvider.
	 * @return array models
	 */
	protected function getModels()
	{
		if ($this->_models === null || $refresh) {
			$this->_models = [];
			$timings = $this->calculateTimings();

			foreach($timings as $dbTiming) {
				$this->_models[] = 	[
					'type' => $this->detectQueryType($dbTiming[1]),
					'query' => $dbTiming[1],
					'duration' => ($dbTiming[3] * 1000), #in milliseconds
					'trace' => $dbTiming[4],
				];
			}
		}
		return $this->_models;
	}

	/**
	 * Detects databse timing type. Detecting is produced through simple parsing to the first space|tab|new row.
	 * First word before space is timing type. If there is no such words, timing will have empty type.
	 * @param string $timing timing procedure string
	 * @return string query type select|insert|delete|etc
	 */
	protected function detectQueryType($timing)
	{
		$timing = ltrim($timing);
		preg_match('/^([a-zA-z]*)/', $timing, $matches);
		return count($matches) ? $matches[0] : '';
	}

}
