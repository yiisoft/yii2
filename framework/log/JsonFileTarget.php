<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\log;

use yii\helpers\Json;

/**
 * JsonFileTarget extends FileTarget changing the output to JSON
 *
 * @author Fabio Oak <fabio@oakideas.com.br>
 */
class JsonFileTarget extends FileTarget
{
	/**
     * Formats a log message for display as a JSON string.
     * @param array $message the log message to be formatted.
     * @return string the formatted message
     */
	public function formatMessage($message): string {

        list($text, $level, $category, $timestamp) = $message;
        $level = Logger::getLevelName($level);

        if ($text instanceof \Throwable || $text instanceof \Exception) {
            $text = (string) $text;
		}

        $traces = [];
        if (isset($message[4])) {
            foreach ($message[4] as $trace) {
                $traces[] = "in {$trace['file']}:{$trace['line']}";
            }
        }

		return Json::encode(['timestamp' => $timestamp, 'datetime' => date('Y-m-d\TH:i:s\Z', intval($timestamp)), 'text' => $text, 'level' => $level, 'category' => $category, 'traces' => $traces]);
    }

}
