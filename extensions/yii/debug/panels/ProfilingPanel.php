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
use yii\debug\models\search\Profile;

/**
 * Debugger panel that collects and displays performance profiling info.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ProfilingPanel extends Panel
{

	/**
	 * @var array profile messages timings
	 */
	private $_timings;

	/**
	 * @var array current request profile timings
	 */
	private $_models;

	public function getName()
	{
		return 'Profiling';
	}

	public function getSummary()
	{
		return  Yii::$app->view->render('panels/profile/summary', [
			'memory' => sprintf('%.1f MB', $this->data['memory'] / 1048576),
			'time' => number_format($this->data['time'] * 1000) . ' ms',
			'panel' => $this
		]);
	}

	public function getDetail()
	{
		$searchModel = new Profile();
		$dataProvider = $searchModel->search(Yii::$app->request->get(), $this->getModels());

		return  Yii::$app->view->render('panels/profile/detail', [
			'panel' => $this,
			'dataProvider' => $dataProvider,
			'searchModel' => $searchModel,
			'memory' => sprintf('%.1f MB', $this->data['memory'] / 1048576),
			'time' => number_format($this->data['time'] * 1000) . ' ms',
		]);
	}

	/**
	 * Calculates given request profile messages timings.
	 * @return array timings [token, category, timestamp, traces, nesting level, elapsed time]
	 */
	protected function calculateTimings()
	{
		if ($this->_timings !== null) {
			return $this->_timings;
		}

		$messages = $this->data['messages'];
		$timings = [];
		$stack = [];

		foreach ($messages as $i => $log) {
			list($token, $level, $category, $timestamp, $traces) = $log;
			$log[5] = $i;
			if ($level == Logger::LEVEL_PROFILE_BEGIN) {
				$stack[] = $log;
			} elseif ($level == Logger::LEVEL_PROFILE_END) {
				if (($last = array_pop($stack)) !== null && $last[0] === $token) {
					$timings[$last[5]] = [$last[0], $last[2], $last[3], $last[4], count($stack), $timestamp - $last[3]];
				}
			}
		}

		ksort($timings);

		return $this->_timings = array_values($timings);
	}

	public function save()
	{
		$target = $this->module->logTarget;
		$messages = $target->filterMessages($target->messages, Logger::LEVEL_PROFILE);
		return [
			'memory' => memory_get_peak_usage(),
			'time' => microtime(true) - YII_BEGIN_TIME,
			'messages' => $messages,
		];
	}

	/**
	 * Returns array of profiling models that can be used in data provider.
	 * @return array models
	 */
	protected function getModels()
	{
		if ($this->_models === null) {
			$this->_models = [];
			$timings = $this->calculateTimings();

			foreach($timings as $seq => $profileTiming) {
				$this->_models[] = 	[
					'duration' => $profileTiming[5] * 1000, // in milliseconds
					'category' => $profileTiming[1],
					'info' => $profileTiming[0],
					'level' => $profileTiming[4],
					'timestamp' => $profileTiming[2],
					'seq' => $seq,
				];
			}
		}
		return $this->_models;
	}

}
