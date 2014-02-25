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
	public $layout = '@yii/apidoc/templates/bootstrap/layouts/api.php';
	public $indexView = '@yii/apidoc/templates/bootstrap/views/index.php';

	public $extensions = [
		'apidoc',
		'authclient',
		'bootstrap',
		'codeception',
		'composer',
		'debug',
		'elasticsearch',
		'faker',
		'gii',
		'imagine',
		'jui',
		'mongodb',
		'redis',
		'smarty',
		'sphinx',
		'swiftmailer',
		'twig',
	];

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

	public function getNavTypes($type, $types)
	{
		if ($type === null) {
			return $types;
		}
		$extensions = $this->extensions;
		$navClasses = 'app';
		if (isset($type)) {
			if ($type->name == 'Yii') {
				$navClasses = 'yii';
			} elseif (strncmp($type->name, 'yii\\', 4) == 0) {
				$subName = substr($type->name, 4);
				if (($pos = strpos($subName, '\\')) !== false) {
					$subNamespace = substr($subName, 0, $pos);
					if (in_array($subNamespace, $extensions)) {
						$navClasses = $subNamespace;
					} else {
						$navClasses = 'yii';
					}
				}
			}
		}
		return $this->filterTypes($types, $navClasses);
	}

	protected function filterTypes($types, $navClasses)
	{
		switch ($navClasses)
		{
			case 'app':
				$types = array_filter($types, function($val) {
					return strncmp($val->name, 'yii\\', 4) !== 0;
				});
				break;
			case 'yii':
				$self = $this;
				$types = array_filter($types, function($val) use ($self) {
					if (strlen($val->name) < 5) {
						return false;
					}
					$subName = substr($val->name, 4, strpos($val->name, '\\', 5) - 4);
					return strncmp($val->name, 'yii\\', 4) === 0 && !in_array($subName, $self->extensions);
				});
				break;
			default:
				$types = array_filter($types, function($val) use ($navClasses) {
					return strncmp($val->name, "yii\\$navClasses\\", strlen("yii\\$navClasses\\")) === 0;
				});
		}
		return $types;
	}
}