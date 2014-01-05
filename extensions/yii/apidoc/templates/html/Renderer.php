<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\html;

use yii\apidoc\models\BaseDoc;
use yii\apidoc\models\ConstDoc;
use yii\apidoc\models\EventDoc;
use yii\apidoc\models\MethodDoc;
use yii\apidoc\models\PropertyDoc;
use yii\apidoc\models\TypeDoc;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\Context;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\TraitDoc;
use yii\apidoc\templates\BaseRenderer;
use yii\base\ViewContextInterface;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\Html;
use Yii;
use yii\web\AssetManager;
use yii\web\View;

/**
 * The base class for HTML API documentation renderers.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
abstract class Renderer extends BaseRenderer implements ViewContextInterface
{
	/**
	 * @var string directory to use for output of html files. Can be a path alias.
	 */
	public $targetDir;
	/**
	 * @var string string to use as the title of the generated page.
	 */
	public $pageTitle = 'Yii Framework 2.0 API Documentation';
	/**
	 * @var string path or alias of the layout file to use.
	 */
	public $layout;
	/**
	 * @var string path or alias of the view file to use for rendering types (classes, interfaces, traits).
	 */
	public $typeView = '@yii/apidoc/templates/html/views/type.php';
	/**
	 * @var string path or alias of the view file to use for rendering the index page.
	 */
	public $indexView = '@yii/apidoc/templates/html/views/index.php';
	/**
	 * @var Context the [[Context]] currently being rendered.
	 */
	protected $context;
	/**
	 * @var View
	 */
	private $_view;

	/**
	 * @return View the view instance
	 */
	public function getView()
	{
		if ($this->_view === null) {
			$this->_view = new View();
			$assetPath = Yii::getAlias($this->targetDir) . '/assets';
			if (!is_dir($assetPath)) {
				mkdir($assetPath);
			}
			$this->_view->assetManager = new AssetManager([
				'basePath' => $assetPath,
				'baseUrl' => '/assets',
			]);
		}
		return $this->_view;
	}

	/**
	 * Renders a given [[Context]].
	 *
	 * @param Context $context the api documentation context to render.
	 * @param Controller $controller the apidoc controller instance. Can be used to control output.
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
	 * @param BaseDoc $context
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
		// TODO use phpdoc Context for this
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

		return ($method->isReturnByReference ? '<b>&</b>' : '')
			. ($method->returnType === null ? 'void' : $this->typeLink($method->returnTypes))
			. ' ' . $method->name . '( '
			. implode(', ', $params)
			. ' )';
	}

	protected function generateFileName($typeName)
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
		return Yii::getAlias('@yii/apidoc/templates/html/views/' . $view);
	}
}