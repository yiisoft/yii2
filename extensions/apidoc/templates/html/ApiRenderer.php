<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\html;

use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\MethodDoc;
use yii\apidoc\models\PropertyDoc;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\Context;
use yii\apidoc\renderers\ApiRenderer as BaseApiRenderer;
use yii\base\ViewContextInterface;
use yii\helpers\Console;
use yii\helpers\Html;
use yii\web\AssetManager;
use yii\web\View;
use Yii;

/**
 * The base class for HTML API documentation renderers.
 *
 * @property View $view The view instance. This property is read-only.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ApiRenderer extends BaseApiRenderer implements ViewContextInterface
{
	/**
	 * @var string string to use as the title of the generated page.
	 */
	public $pageTitle;
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
	 * @var View
	 */
	private $_view;
	private $_targetDir;


	public function init()
	{
		parent::init();

		if ($this->pageTitle === null) {
			$this->pageTitle = 'Yii Framework 2.0 API Documentation'; // TODO guess page title
		}
	}

	/**
	 * @return View the view instance
	 */
	public function getView()
	{
		if ($this->_view === null) {
			$this->_view = new View();
			$assetPath = Yii::getAlias($this->_targetDir) . '/assets';
			if (!is_dir($assetPath)) {
				mkdir($assetPath);
			}
			$this->_view->assetManager = new AssetManager([
				'basePath' => $assetPath,
				'baseUrl' => './assets',
			]);
		}
		return $this->_view;
	}

	/**
	 * Renders a given [[Context]].
	 *
	 * @param Context $context the api documentation context to render.
	 * @param $targetDir
	 */
	public function render($context, $targetDir)
	{
		$this->apiContext = $context;
		$this->_targetDir = $targetDir;

		$types = array_merge($context->classes, $context->interfaces, $context->traits);
		$typeCount = count($types) + 1;

		if ($this->controller !== null) {
			Console::startProgress(0, $typeCount, 'Rendering files: ', false);
		}
		$done = 0;
		foreach($types as $type) {
			$fileContent = $this->renderWithLayout($this->typeView, [
				'type' => $type,
				'apiContext' => $context,
				'types' => $types,
			]);
			file_put_contents($targetDir . '/' . $this->generateFileName($type->name), $fileContent);

			if ($this->controller !== null) {
				Console::updateProgress(++$done, $typeCount);
			}
		}

		$indexFileContent = $this->renderWithLayout($this->indexView, [
			'apiContext' => $context,
			'types' => $types,
		]);
		file_put_contents($targetDir . '/index.html', $indexFileContent);

		if ($this->controller !== null) {
			Console::updateProgress(++$done, $typeCount);
			Console::endProgress(true);
			$this->controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);
		}
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
	 * @param ClassDoc $class
	 * @return string
	 */
	public function renderInheritance($class)
	{
		$parents = [];
		$parents[] = $this->createTypeLink($class);
		while ($class->parentClass !== null) {
			if(isset($this->apiContext->classes[$class->parentClass])) {
				$class = $this->apiContext->classes[$class->parentClass];
				$parents[] = $this->createTypeLink($class);
			} else {
				$parents[] = $this->createTypeLink($class->parentClass);
				break;
			}
		}
		return implode(" &raquo;\n", $parents);
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
			if(isset($this->apiContext->interfaces[$interface])) {
				$interfaces[] = $this->createTypeLink($this->apiContext->interfaces[$interface]);
			} else {
				$interfaces[] = $this->createTypeLink($interface);
			}
		}
		return implode(', ', $interfaces);
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
			if(isset($this->apiContext->traits[$trait])) {
				$traits[] = $this->createTypeLink($this->apiContext->traits[$trait]);
			} else {
				$traits[] = $this->createTypeLink($trait);
			}
		}
		return implode(', ', $traits);
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
			if(isset($this->apiContext->classes[$class])) {
				$classes[] = $this->createTypeLink($this->apiContext->classes[$class]);
			} else {
				$classes[] = $this->createTypeLink($class);
			}
		}
		return implode(', ', $classes);
	}

	/**
	 * @param PropertyDoc $property
	 * @return string
	 */
	public function renderPropertySignature($property)
	{
		if ($property->getter !== null || $property->setter !== null) {
			$sig = [];
			if ($property->getter !== null) {
				$sig[] = $this->renderMethodSignature($property->getter);
			}
			if ($property->setter !== null) {
				$sig[] = $this->renderMethodSignature($property->setter);
			}
			return implode('<br />', $sig);
		}
		return $this->createTypeLink($property->types) . ' ' . $this->createSubjectLink($property, $property->name) . ' '
				. ApiMarkdown::highlight('= ' . ($property->defaultValue === null ? 'null' : $property->defaultValue), 'php');
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
			. ($method->returnType === null ? 'void' : $this->createTypeLink($method->returnTypes))
			. ' ' . $this->createSubjectLink($method, $method->name)
			. ApiMarkdown::highlight('( ' . implode(', ', $params) . ' )', 'php');
	}

	public function generateApiUrl($typeName)
	{
		return $this->generateFileName($typeName);
	}

	protected function generateFileName($typeName)
	{
		return strtolower(str_replace('\\', '-', $typeName)) . '.html';
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

	/**
	 * generate link markup
	 * @param $text
	 * @param $href
	 * @return mixed
	 */
	protected function generateLink($text, $href)
	{
		return Html::a($text, null, ['href' => $href]);
	}

	public function getSourceUrl($type)
	{
		return null;
	}
}
