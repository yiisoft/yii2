<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug;

use Yii;
use yii\base\InvalidConfigException;
use yii\log\Target;

/**
 * The debug LogTarget is used to store logs for later use in the debugger tool
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class LogTarget extends Target
{
	/**
	 * @var Module
	 */
	public $module;
	public $tag;

	/**
	 * @param \yii\debug\Module $module
	 * @param array $config
	 */
	public function __construct($module, $config = [])
	{
		parent::__construct($config);
		$this->module = $module;
		$this->tag = uniqid();
	}

	/**
	 * Exports log messages to a specific destination.
	 * Child classes must implement this method.
	 */
	public function export()
	{
		$path = $this->module->dataPath;
		if (!is_dir($path)) {
			mkdir($path);
		}

		$summary = $this->collectSummary();
		$dataFile = "$path/{$this->tag}.data";
		$data = [];
		foreach ($this->module->panels as $id => $panel) {
			$data[$id] = $panel->save();
		}
		$data['summary'] = $summary;
		file_put_contents($dataFile, serialize($data));

		$indexFile = "$path/index.data";
		$this->updateIndexFile($indexFile, $summary);
	}

	/**
	 * Updates index file with summary log data
	 *
	 * @param string $indexFile path to index file
	 * @param array $summary summary log data
	 * @throws \yii\base\InvalidConfigException
	 */
	private function updateIndexFile($indexFile, $summary)
	{
		touch($indexFile);
		if (($fp = @fopen($indexFile, 'r+')) === false) {
			throw new InvalidConfigException("Unable to open debug data index file: $indexFile");
		}
		@flock($fp, LOCK_EX);
		$manifest = '';
		while (($buffer = fgets($fp)) !== false) {
			$manifest .= $buffer;
		}
		if (!feof($fp) || empty($manifest)) {
			// error while reading index data, ignore and create new
			$manifest = [];
		} else {
			$manifest = unserialize($manifest);
		}

		$manifest[$this->tag] = $summary;
		$this->gc($manifest);

		ftruncate($fp, 0);
		rewind($fp);
		fwrite($fp, serialize($manifest));

		@flock($fp, LOCK_UN);
		@fclose($fp);
	}

	/**
	 * Processes the given log messages.
	 * This method will filter the given messages with [[levels]] and [[categories]].
	 * And if requested, it will also export the filtering result to specific medium (e.g. email).
	 * @param array $messages log messages to be processed. See [[Logger::messages]] for the structure
	 * of each message.
	 * @param boolean $final whether this method is called at the end of the current application
	 */
	public function collect($messages, $final)
	{
		$this->messages = array_merge($this->messages, $messages);
		if ($final) {
			$this->export($this->messages);
		}
	}

	protected function gc(&$manifest)
	{
		if (count($manifest) > $this->module->historySize + 10) {
			$n = count($manifest) - $this->module->historySize;
			foreach (array_keys($manifest) as $tag) {
				$file = $this->module->dataPath . "/$tag.data";
				@unlink($file);
				unset($manifest[$tag]);
				if (--$n <= 0) {
					break;
				}
			}
		}
	}

	/**
	 * Collects summary data of current request.
	 * @return array
	 */
	protected function collectSummary()
	{
		$request = Yii::$app->getRequest();
		$response = Yii::$app->getResponse();
		$summary = [
			'tag' => $this->tag,
			'url' => $request->getAbsoluteUrl(),
			'ajax' => $request->getIsAjax(),
			'method' => $request->getMethod(),
			'ip' => $request->getUserIP(),
			'time' => time(),
			'statusCode' => $response->statusCode,
			'sqlCount' => $this->getSqlTotalCount(),
		];

		if (isset($this->module->panels['mail'])) {
			$summary['mailCount'] = count($this->module->panels['mail']->getMessages());
		}

		return $summary;
	}

	/**
	 * Returns total sql count executed in current request. If database panel is not configured
	 * returns 0.
	 * @return integer
	 */
	protected function getSqlTotalCount()
	{
		if (!isset($this->module->panels['db'])) {
			return 0;
		}
		$profileLogs = $this->module->panels['db']->getProfileLogs();
		
		# / 2 because messages are in couple (begin/end)
		return count($profileLogs) / 2;
	}

}
