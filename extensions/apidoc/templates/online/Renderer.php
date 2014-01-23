<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\online;
use yii\apidoc\models\Context;
use yii\apidoc\models\TypeDoc;
use yii\console\Controller;
use Yii;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;

/**
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Renderer extends \yii\apidoc\templates\html\Renderer
{
	public $layout = false;//'@yii/apidoc/templates/offline/views/offline.php';
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
		$controller->stdout("writing packages file...");
		$packages = [];
		$notNamespaced = [];
		foreach(array_merge($context->classes, $context->interfaces, $context->traits) as $type) {
			/** @var TypeDoc $type */
			if (empty($type->namespace)) {
				$notNamespaced[] = str_replace('\\', '-', $type->name);
			} else {
				$packages[$type->namespace][] = str_replace('\\', '-', $type->name);
			}
		}
		ksort($packages);
		$packages = array_merge(['Not namespaced' => $notNamespaced], $packages);
		foreach($packages as $name => $classes) {
			sort($packages[$name]);
		}
		file_put_contents($dir . '/packages.txt', serialize($packages));
		$controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);
	}

	protected function generateLink($typeName)
	{
		return strtolower(str_replace('\\', '-', $typeName));
	}

	protected function generateFileName($typeName)
	{
		return $this->generateLink($typeName) . '.html';
	}
}