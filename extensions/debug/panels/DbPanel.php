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

		return Yii::$app->view->render('panels/db/summary', [
			'timings' => $this->calculateTimings(), 
			'panel' => $this,
			'queryCount' => $queryCount,
			'queryTime' => $queryTime,
		]);
	}

	public function getDetail()
	{
		$searchModel = new Db();
		$dataProvider = $searchModel->search(Yii::$app->request->getQueryParams(), $this->getModels());

		return Yii::$app->view->render('panels/db/detail', [
			'panel' => $this,
			'dataProvider' => $dataProvider,
			'searchModel' => $searchModel,
		]);
	}

	/**
	 * Calculates given request profile messages timings.
	 * @return array timings [token, category, timestamp, traces, nesting level, elapsed time]
	 */
	protected function calculateTimings()
	{
		if ($this->_timings === null) {
			$this->_timings = Yii::$app->getLog()->calculateTimings($this->data['messages']);
		}
		return $this->_timings;
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
			$queryTime += $timing['duration'];
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
		if ($this->_models === null) {
			$this->_models = [];
			$timings = $this->calculateTimings();

			foreach($timings as $seq => $dbTiming) {
				$this->_models[] = 	[
					'type' => $this->detectQueryType($dbTiming['info']),
					'query' => $dbTiming['info'],
					'duration' => ($dbTiming['duration'] * 1000), // in milliseconds
					'trace' => $dbTiming['trace'],
					'timestamp' => ($dbTiming['timestamp'] * 1000), // in milliseconds
					'seq' => $seq,
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
