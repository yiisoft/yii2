<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\debug\Panel;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class LogPanel extends Panel
{
	public function getName()
	{
		return 'Logs';
	}

	public function getSummary()
	{
		$count = count($this->data['messages']);
		return <<<EOD
<div class="yii-debug-toolbar-block">
Log messages: $count
</div>
EOD;
	}

	public function getDetail()
	{
		return '<h2>Logs</h2>';
	}

	public function save()
	{
		return array(
			'messages' => Yii::$app->getLog()->targets['debug']->messages,
		);
	}
}
