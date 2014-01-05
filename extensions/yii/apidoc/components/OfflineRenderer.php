<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\components;


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
	public $itemView = '@yii/apidoc/views/class.php';
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

		$items = array_merge($context->classes, $context->interfaces, $context->traits);
		$itemCount = count($items) + 1;
		Console::startProgress(0, $itemCount, 'Rendering files: ', false);
		$done = 0;
		foreach($items as $item) {
			$fileContent = $this->renderWithLayout($this->itemView, [
				'item' => $item,
				'docContext' => $context,
			]);
			file_put_contents($dir . '/' . $this->generateFileName($item->name), $fileContent);
			Console::updateProgress(++$done, $itemCount);
		}
		$indexFileContent = $this->renderWithLayout($this->indexView, [
			'docContext' => $context,
			'items' => $items,
		]);
		file_put_contents($dir . '/index.html', $indexFileContent);
		Console::updateProgress(++$done, $itemCount);
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
	 * creates a link to an item
	 * @param ClassDoc|InterfaceDoc|TraitDoc $item
	 * @param string $title
	 * @return string
	 */
	public function link($item, $title = null)
	{
		if ($title === null) {
			$title = $item->name;
		}
		return Html::a($title, null, ['href' => $this->generateFileName($item->name)]);
	}

	/**
	 * @param ClassDoc $class
	 * @return string
	 */
	public function renderInheritance($class)
	{
		$parents[] = $this->link($class);
		while ($class->parentClass !== null) {
			if(isset($this->context->classes[$class->parentClass])) {
				$class = $this->context->classes[$class->parentClass];
				$parents[] = $this->link($class);
			} else {
				$parents[] = $class->parentClass; // TODO link to php.net
				break;
			}
		}
		return implode(" &raquo;\n",$parents);
	}

	/**
	 * @param ClassDoc $class
	 * @return string
	 */
	public function renderImplements($class)
	{
		$interfaces = [];
		foreach($class->interfaces as $interface) {
			if(isset($this->context->interfaces[$interface])) {
				$interfaces[] = $this->link($this->context->interfaces[$interface]);
			} else {
				$interfaces[] = $interface; // TODO link to php.net
			}
		}
		return implode(', ',$interfaces);
	}

	/**
	 * @param ClassDoc|TraitDoc $class
	 * @return string
	 */
	public function renderTraitUses($class)
	{
		$traits = [];
		foreach($class->traits as $trait) {
			if(isset($this->context->traits[$trait])) {
				$traits[] = $this->link($this->context->traits[$trait]);
			} else {
				$traits[] = $trait; // TODO link to php.net
			}
		}
		return implode(', ',$traits);
	}

	public function renderSubclasses($class)
	{
		$subclasses = [];
		foreach($class->subclasses as $subclass) {
			if(isset($this->context->classes[$subclass])) {
				$subclasses[] = $this->link($this->context->classes[$subclass]);
			} else {
				$subclasses[] = $subclass; // TODO link to php.net
			}
		}
		return implode(', ',$subclasses);
	}


	public function generateFileName($itemName)
	{
		return strtolower(str_replace('\\', '_', $itemName)) . '.html';
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