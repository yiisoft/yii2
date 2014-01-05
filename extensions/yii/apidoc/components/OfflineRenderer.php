<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\components;


use yii\apidoc\models\BaseDoc;
use yii\apidoc\models\ConstDoc;
use yii\apidoc\models\EventDoc;
use yii\apidoc\models\MethodDoc;
use yii\apidoc\models\PropertyDoc;
use yii\apidoc\models\TypeDoc;
use yii\base\ViewContextInterface;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\Context;
use Yii;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\TraitDoc;

class OfflineRenderer extends BaseRenderer implements ViewContextInterface
{
	public $targetDir;

	public $layout = '@yii/apidoc/views/layouts/offline.php';
	public $typeView = '@yii/apidoc/views/type.php';
	public $indexView = '@yii/apidoc/views/index.php';

	public $pageTitle = 'Yii Framework 2.0 API Documentation';

	/**
	 * @var Context
	 */
	protected $context;

	/**
	 * @param Context $context
	 * @param Controller $controller
	 */
	public function render($context, $controller)
	{
		$this->context = $context;
		$dir = Yii::getAlias($this->targetDir);
		if (!is_dir($dir)) {
			mkdir($dir);
		}

		$types = array_merge($context->classes, $context->interfaces, $context->traits);
		$typeCount = count($types) + 1;
		Console::startProgress(0, $typeCount, 'Rendering files: ', false);
		$done = 0;
		foreach($types as $type) {
			$fileContent = $this->renderWithLayout($this->typeView, [
				'type' => $type,
				'docContext' => $context,
			]);
			file_put_contents($dir . '/' . $this->generateFileName($type->name), $fileContent);
			Console::updateProgress(++$done, $typeCount);
		}
		$indexFileContent = $this->renderWithLayout($this->indexView, [
			'docContext' => $context,
			'types' => $types,
		]);
		file_put_contents($dir . '/index.html', $indexFileContent);
		Console::updateProgress(++$done, $typeCount);
		Console::endProgress(true);
		$controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);

		$controller->stdout('Copying asset files... ');
		FileHelper::copyDirectory(__DIR__ . '/../assets/css', $dir . '/css');
		$controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);

	}

	protected function renderWithLayout($viewFile, $params)
	{
		$output = $this->getView()->render($viewFile, $params, $this);
		if ($this->layout !== false) {
			$params['content'] = $output;
			return $this->getView()->renderFile($this->layout, $params, $this);
		} else {
			return $output;
		}
	}

	/**
	 * creates a link to a type (class, interface or trait)
	 * @param ClassDoc|InterfaceDoc|TraitDoc $types
	 * @param string $title
	 * @return string
	 */
	public function typeLink($types, $context = null)
	{
		if (!is_array($types)) {
			$types = [$types];
		}
		$links = [];
		foreach($types as $type) {
			$postfix = '';
			if (!is_object($type)) {
				if (substr($type, -2, 2) == '[]') {
					$postfix = '[]';
					$type = substr($type, 0, -2);
				}

				if (($t = $this->context->getType(ltrim($type, '\\'))) !== null) {
					$type = $t;
				} elseif ($type[0] !== '\\' && ($t = $this->context->getType($this->resolveNamespace($context) . '\\' . ltrim($type, '\\'))) !== null) {
					$type = $t;
				} else {
					ltrim($type, '\\');
				}
			}
			if (!is_object($type)) {
				$links[] = $type;
			} else {
				$links[] = Html::a(
					$type->name,
					null,
					['href' => $this->generateFileName($type->name)]
				) . $postfix;
			}
		}
		return implode('|', $links);
	}

	/**
	 * creates a link to a subject
	 * @param PropertyDoc|MethodDoc|ConstDoc|EventDoc $subject
	 * @param string $title
	 * @return string
	 */
	public function subjectLink($subject, $title = null)
	{
		if ($title === null) {
			$title = $subject->name;
		}
		if (($type = $this->context->getType($subject->definedBy)) === null) {
			return $subject->name;
		} else {
			$link = $this->generateFileName($type->name);
			if ($subject instanceof MethodDoc) {
				$link .= '#' . $subject->name . '()';
			} else {
				$link .= '#' . $subject->name;
			}
			$link .= '-detail';
			return Html::a($title, null, ['href' => $link]);
		}
	}

	/**
	 * @param BaseDoc $context
	 */
	private function resolveNamespace($context)
	{
		if ($context === null) {
			return '';
		}
		if ($context instanceof TypeDoc) {
			return $context->namespace;
		}
		if ($context->hasProperty('definedBy')) {
			$type = $this->context->getType($context);
			if ($type !== null) {
				return $type->namespace;
			}
		}
		return '';
	}

	/**
	 * @param ClassDoc $class
	 * @return string
	 */
	public function renderInheritance($class)
	{
		$parents[] = $this->typeLink($class);
		while ($class->parentClass !== null) {
			if(isset($this->context->classes[$class->parentClass])) {
				$class = $this->context->classes[$class->parentClass];
				$parents[] = $this->typeLink($class);
			} else {
				$parents[] = $class->parentClass; // TODO link to php.net
				break;
			}
		}
		return implode(" &raquo;\n",$parents);
	}

	/**
	 * @param array $names
	 * @return string
	 */
	public function renderInterfaces($names)
	{
		$interfaces = [];
		sort($names, SORT_STRING);
		foreach($names as $interface) {
			if(isset($this->context->interfaces[$interface])) {
				$interfaces[] = $this->typeLink($this->context->interfaces[$interface]);
			} else {
				$interfaces[] = $interface; // TODO link to php.net
			}
		}
		return implode(', ',$interfaces);
	}

	/**
	 * @param array $names
	 * @return string
	 */
	public function renderTraits($names)
	{
		$traits = [];
		sort($names, SORT_STRING);
		foreach($names as $trait) {
			if(isset($this->context->traits[$trait])) {
				$traits[] = $this->typeLink($this->context->traits[$trait]);
			} else {
				$traits[] = $trait; // TODO link to php.net
			}
		}
		return implode(', ',$traits);
	}

	/**
	 * @param array $names
	 * @return string
	 */
	public function renderClasses($names)
	{
		$classes = [];
		sort($names, SORT_STRING);
		foreach($names as $class) {
			if(isset($this->context->classes[$class])) {
				$classes[] = $this->typeLink($this->context->classes[$class]);
			} else {
				$classes[] = $class; // TODO link to php.net
			}
		}
		return implode(', ',$classes);
	}

	/**
	 * @param PropertyDoc $property
	 * @return string
	 */
	public function renderPropertySignature($property)
	{
		return $this->typeLink($property->types) . ' ' . $property->name . ' = ' . ($property->defaultValue === null ? 'null' : $property->defaultValue);
		// TODO
		if(!empty($property->signature))
			return $property->signature;
		$sig='';
		if(!empty($property->getter))
			$sig=$property->getter->signature;
		if(!empty($property->setter))
		{
			if($sig!=='')
				$sig.='<br/>';
			$sig.=$property->setter->signature;
		}
		return $sig;
	}

	/**
	 * @param MethodDoc $method
	 * @return string
	 */
	public function renderMethodSignature($method)
	{
		$params = [];
		foreach($method->params as $param) {
			$params[] = (empty($param->typeHint) ? '' : $param->typeHint . ' ')
				. ($param->isPassedByReference ? '<b>&</b>' : '')
				. $param->name
				. ($param->isOptional ? ' = ' . $param->defaultValue : '');
		}

		//<?php echo preg_replace('/\{\{([^\{\}]*?)\|([^\{\}]*?)\}\}\(/','$2(',$method->signature);

		return ($method->isReturnByReference ? '<b>&</b>' : '')
			. ($method->returnType === null ? 'void' : $this->typeLink($method->returnTypes))
			. ' ' . $method->name . '( '
			. implode(', ', $params)
			. ' )';
	}

	public function generateFileName($typeName)
	{
		return strtolower(str_replace('\\', '_', $typeName)) . '.html';
	}

	/**
	 * Finds the view file corresponding to the specified relative view name.
	 * @param string $view a relative view name. The name does NOT start with a slash.
	 * @return string the view file path. Note that the file may not exist.
	 */
	public function findViewFile($view)
	{
		return Yii::getAlias('@yii/apidoc/views/' . $view);
	}
}