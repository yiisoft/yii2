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

/**
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ApiRenderer extends \yii\apidoc\templates\html\ApiRenderer
{
	public $layout = false;
	public $indexView = '@yii/apidoc/templates/online/views/index.php';

	public $pageTitle = 'Yii Framework 2.0 API Documentation';

	/**
	 * @inheritdoc
	 */
	public function render($context, $targetDir)
	{
		parent::render($context, $targetDir);

		if ($this->controller !== null) {
			$this->controller->stdout("writing packages file...");
		}
		$packages = [];
		$notNamespaced = [];
		foreach (array_merge($context->classes, $context->interfaces, $context->traits) as $type) {
			/** @var TypeDoc $type */
			if (empty($type->namespace)) {
				$notNamespaced[] = str_replace('\\', '-', $type->name);
			} else {
				$packages[$type->namespace][] = str_replace('\\', '-', $type->name);
			}
		}
		ksort($packages);
		$packages = array_merge(['Not namespaced' => $notNamespaced], $packages);
		foreach ($packages as $name => $classes) {
			sort($packages[$name]);
		}
		file_put_contents($targetDir . '/packages.txt', serialize($packages));
		if ($this->controller !== null) {
			$this->controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);
		}
	}

	public function generateApiUrl($typeName)
	{
		return strtolower(str_replace('\\', '-', $typeName));
	}

	protected function generateFileName($typeName)
	{
		return $this->generateApiUrl($typeName) . '.html';
	}
}
