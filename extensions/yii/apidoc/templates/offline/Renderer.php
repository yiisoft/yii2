<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\offline;
use yii\apidoc\models\Context;
use yii\console\Controller;
use Yii;
use yii\helpers\Console;
use yii\helpers\FileHelper;

/**
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Renderer extends \yii\apidoc\templates\html\Renderer
{
	public $layout = '@yii/apidoc/templates/offline/views/offline.php';
	public $indexView = '@yii/apidoc/templates/offline/views/index.php';

	public $pageTitle = 'Yii Framework 2.0 API Documentation';

	/**
	 * Renders a given [[Context]].
	 *
	 * @param Context $context the api documentation context to render.
	 * @param Controller $controller the apidoc controller instance. Can be used to control output.
	 */
	public function render($context, $controller)
	{
		parent::render($context, $controller);

		$dir = Yii::getAlias($this->targetDir);

		$controller->stdout('Copying asset files... ');
		FileHelper::copyDirectory(__DIR__ . '/assets/css', $dir . '/css');
		$controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);
	}

}