<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\bootstrap;
use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\ConstDoc;
use yii\apidoc\models\Context;
use yii\apidoc\models\EventDoc;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\MethodDoc;
use yii\apidoc\models\PropertyDoc;
use yii\apidoc\models\TraitDoc;
use yii\console\Controller;
use Yii;
use yii\helpers\Console;
use yii\helpers\Html;

/**
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ApiRenderer extends \yii\apidoc\templates\html\ApiRenderer
{
	use RendererTrait;

	public $layout = '@yii/apidoc/templates/bootstrap/layouts/api.php';
	public $indexView = '@yii/apidoc/templates/bootstrap/views/index.php';

	/**
	 * @inheritdoc
	 */
	public function render($context, $targetDir)
	{
		$types = array_merge($context->classes, $context->interfaces, $context->traits);

		$extTypes = [];
		foreach($this->extensions as $k => $ext) {
			$extType = $this->filterTypes($types, $ext);
			if (empty($extType)) {
				unset($this->extensions[$k]);
				continue;
			}
			$extTypes[$ext] = $extType;
		}

		// render view files
		parent::render($context, $targetDir);

		if ($this->controller !== null) {
			$this->controller->stdout('generating extension index files...');
		}

		foreach($extTypes as $ext => $extType) {
			$readme = @file_get_contents("https://raw.github.com/yiisoft/yii2-$ext/master/README.md");
			$indexFileContent = $this->renderWithLayout($this->indexView, [
				'docContext' => $context,
				'types' => $extType,
				'readme' => $readme ?: null,
			]);
			file_put_contents($targetDir . "/ext-{$ext}-index.html", $indexFileContent);
		}

		$yiiTypes = $this->filterTypes($types, 'yii');
		if (empty($yiiTypes)) {
//			$readme = @file_get_contents("https://raw.github.com/yiisoft/yii2-framework/master/README.md");
			$indexFileContent = $this->renderWithLayout($this->indexView, [
				'docContext' => $context,
				'types' => $this->filterTypes($types, 'app'),
				'readme' => null,
			]);
		} else {
			$readme = @file_get_contents("https://raw.github.com/yiisoft/yii2-framework/master/README.md");
			$indexFileContent = $this->renderWithLayout($this->indexView, [
				'docContext' => $context,
				'types' => $yiiTypes,
				'readme' => $readme ?: null,
			]);
		}
		file_put_contents($targetDir . '/index.html', $indexFileContent);

		if ($this->controller !== null) {
			$this->controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);
		}
	}
}