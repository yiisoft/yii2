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
class ConfigPanel extends Panel
{
	public function getName()
	{
		return 'Config';
	}

	public function getSummary()
	{
		return <<<EOD
<div class="yii-debug-toolbar-block">
	PHP: {$this->data['phpVersion']},
	Yii: {$this->data['yiiVersion']}
</div>
EOD;
	}

	public function getDetail()
	{
		return '<h2>Config</h2>';
	}

	public function save()
	{
		return array(
			'phpVersion' => PHP_VERSION,
			'yiiVersion' => Yii::getVersion(),
		);
	}
}
