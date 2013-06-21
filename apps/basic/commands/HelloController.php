<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\base\MutexFilter;

/**
 * This command echos what the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{
	public function behaviors()
	{
		return array(
			'mutex' => array(
				'class' => MutexFilter::className(),
				'actions' => array(
					'critical' => array(
						'expire' => 0,
						'lockName' => 'criticalSection',
						'errorMessage' => "Already working on this task!\n",
					),
				),
			),
		);
	}

	/**
	 * This command echos what you have entered as the message.
	 * @param string $message the message to be echoed.
	 */
	public function actionIndex($message = 'hello world')
	{
		echo "{$message}\n";
	}

	/**
	 * Code of this action will be executed only by a single process at the same time.
	 */
	public function actionCritical()
	{
		echo "Running critical operations...\n";
		sleep(10);
		echo "Done!\n";
	}
}
