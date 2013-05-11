<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CProfileLogRoute displays the profiling results in Web page.
 *
 * The profiling is done by calling {@link YiiBase::beginProfile()} and {@link YiiBase::endProfile()},
 * which marks the begin and end of a code block.
 *
 * CProfileLogRoute supports two types of report by setting the {@link setReport report} property:
 * <ul>
 * <li>summary: list the execution time of every marked code block</li>
 * <li>callstack: list the mark code blocks in a hierarchical view reflecting their calling sequence.</li>
 * </ul>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CProfileLogRoute extends CWebLogRoute
{
	/**
	 * @var boolean whether to aggregate results according to profiling tokens.
	 * If false, the results will be aggregated by categories.
	 * Defaults to true. Note that this property only affects the summary report
	 * that is enabled when {@link report} is 'summary'.
	 */
	public $groupByToken = true;
	/**
	 * @var string type of profiling report to display
	 */
	private $_report = 'summary';

	/**
	 * Initializes the route.
	 * This method is invoked after the route is created by the route manager.
	 */
	public function init()
	{
		$this->levels = CLogger::LEVEL_PROFILE;
	}

	/**
	 * @return string the type of the profiling report to display. Defaults to 'summary'.
	 */
	public function getReport()
	{
		return $this->_report;
	}

	/**
	 * @param string $value the type of the profiling report to display. Valid values include 'summary' and 'callstack'.
	 */
	public function setReport($value)
	{
		if ($value === 'summary' || $value === 'callstack')
			$this->_report = $value;
		else
			throw new CException(Yii::t('yii|CProfileLogRoute.report "{report}" is invalid. Valid values include "summary" and "callstack".',
				array('{report}' => $value)));
	}

	/**
	 * Displays the log messages.
	 * @param array $logs list of log messages
	 */
	public function processLogs($logs)
	{
		$app = \Yii::$app;
		if (!($app instanceof CWebApplication) || $app->getRequest()->getIsAjaxRequest())
			return;

		if ($this->getReport() === 'summary')
			$this->displaySummary($logs);
		else
			$this->displayCallstack($logs);
	}

	/**
	 * Displays the callstack of the profiling procedures for display.
	 * @param array $logs list of logs
	 */
	protected function displayCallstack($logs)
	{
		$stack = array();
		$results = array();
		$n = 0;
		foreach ($logs as $log)
		{
			if ($log[1] !== CLogger::LEVEL_PROFILE) {
				continue;
			}
			$message = $log[0];
			if (!strncasecmp($message, 'begin:', 6)) {
				$log[0] = substr($message, 6);
				$log[4] = $n;
				$stack[] = $log;
				$n++;
			} elseif (!strncasecmp($message, 'end:', 4)) {
				$token = substr($message, 4);
				if (($last = array_pop($stack)) !== null && $last[0] === $token) {
					$delta = $log[3] - $last[3];
					$results[$last[4]] = array($token, $delta, count($stack));
				} else
				{
					throw new CException(Yii::t('yii|CProfileLogRoute found a mismatching code block "{token}". Make sure the calls to Yii::beginProfile() and Yii::endProfile() be properly nested.',
						array('{token}' => $token)));
				}
			}
		}
		// remaining entries should be closed here
		$now = microtime(true);
		while (($last = array_pop($stack)) !== null) {
			$results[$last[4]] = array($last[0], $now - $last[3], count($stack));
		}
		ksort($results);
		$this->render('profile-callstack', $results);
	}

	/**
	 * Displays the summary report of the profiling result.
	 * @param array $logs list of logs
	 */
	protected function displaySummary($logs)
	{
		$stack = array();
		foreach ($logs as $log)
		{
			if ($log[1] !== CLogger::LEVEL_PROFILE)
				continue;
			$message = $log[0];
			if (!strncasecmp($message, 'begin:', 6))
			{
				$log[0] = substr($message, 6);
				$stack[] = $log;
			} elseif (!strncasecmp($message, 'end:', 4))
			{
				$token = substr($message, 4);
				if (($last = array_pop($stack)) !== null && $last[0] === $token)
				{
					$delta = $log[3] - $last[3];
					if (!$this->groupByToken)
						$token = $log[2];
					if (isset($results[$token]))
						$results[$token] = $this->aggregateResult($results[$token], $delta);
					else
						$results[$token] = array($token, 1, $delta, $delta, $delta);
				} else
					throw new CException(Yii::t('yii|CProfileLogRoute found a mismatching code block "{token}". Make sure the calls to Yii::beginProfile() and Yii::endProfile() be properly nested.',
						array('{token}' => $token)));
			}
		}

		$now = microtime(true);
		while (($last = array_pop($stack)) !== null)
		{
			$delta = $now - $last[3];
			$token = $this->groupByToken ? $last[0] : $last[2];
			if (isset($results[$token]))
				$results[$token] = $this->aggregateResult($results[$token], $delta);
			else
				$results[$token] = array($token, 1, $delta, $delta, $delta);
		}

		$entries = array_values($results);
		$func = create_function('$a,$b', 'return $a[4] < $b[4] ? 1 : 0;');
		usort($entries, $func);

		$this->render('profile-summary', $entries);
	}

	/**
	 * Aggregates the report result.
	 * @param array $result log result for this code block
	 * @param float $delta time spent for this code block
	 * @return array
	 */
	protected function aggregateResult($result, $delta)
	{
		list($token, $calls, $min, $max, $total) = $result;
		if ($delta < $min)
			$min = $delta;
		elseif ($delta > $max)
			$max = $delta;
		$calls++;
		$total += $delta;
		return array($token, $calls, $min, $max, $total);
	}
}
