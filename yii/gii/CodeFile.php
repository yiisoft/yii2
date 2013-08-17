<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii;

use Yii;
use yii\base\Object;
use yii\gii\components\TextDiff;
use yii\helpers\Html;

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CodeFile extends Object
{
	const OP_NEW = 'new';
	const OP_OVERWRITE = 'overwrite';
	const OP_SKIP = 'skip';

	public $id;
	/**
	 * @var string the file path that the new code should be saved to.
	 */
	public $path;
	/**
	 * @var mixed the newly generated code. If this is null, it means {@link path}
	 * should be treated as a directory.
	 */
	public $content;
	/**
	 * @var string the operation to be performed
	 */
	public $operation;

	/**
	 * Constructor.
	 * @param string $path the file path that the new code should be saved to.
	 * @param string $content the newly generated code
	 */
	public function __construct($path, $content)
	{
		$this->path = strtr($path, array('/' => DIRECTORY_SEPARATOR, '\\' => DIRECTORY_SEPARATOR));
		$this->content = $content;
		$this->id = md5($this->path);
		if (is_file($path)) {
			$this->operation = file_get_contents($path) === $content ? self::OP_SKIP : self::OP_OVERWRITE;
		} else {
			$this->operation = self::OP_NEW;
		}
	}

	/**
	 * Saves the code into the file {@link path}.
	 * @return string|boolean
	 */
	public function save()
	{
		$module = Yii::$app->controller->module;
		if ($this->operation === self::OP_NEW) {
			$dir = dirname($this->path);
			if (!is_dir($dir)) {
				$oldmask = @umask(0);
				$result = @mkdir($dir, $module->newDirMode, true);
				@umask($oldmask);
				if (!$result) {
					return "Unable to create the directory '$dir'.";
				}
			}
		}
		if (@file_put_contents($this->path, $this->content) === false) {
			return "Unable to write the file '{$this->path}'.";
		} else {
			$oldmask = @umask(0);
			@chmod($this->path, $module->newFileMode);
			@umask($oldmask);
		}
		return true;
	}

	/**
	 * @return string the code file path relative to the application base path.
	 */
	public function getRelativePath()
	{
		if (strpos($this->path, Yii::$app->basePath) === 0) {
			return substr($this->path, strlen(Yii::$app->basePath) + 1);
		} else {
			return $this->path;
		}
	}

	/**
	 * @return string the code file extension (e.g. php, txt)
	 */
	public function getType()
	{
		if (($pos = strrpos($this->path, '.')) !== false) {
			return substr($this->path, $pos + 1);
		} else {
			return 'unknown';
		}
	}

	public function preview()
	{
		if (($pos = strrpos($this->path, '.')) !== false) {
			$type = substr($this->path, $pos + 1);
		} else {
			$type = 'unknown';
		}

		if ($type === 'php') {
			return '<div class="content">' . highlight_string($this->content, true) . '</div>';
		} elseif(in_array($type, array('txt','js','css'))) {
			return '<div class="content">' . nl2br(Html::encode($this->content)) . '</div>';
		} else {
			return '<div class="error">Preview is not available for this file type.</div>';
		}
	}

	public function diff()
	{
		$type = $this->getType();
		if (!in_array($type, array('php', 'txt','js','css'))) {
			return false;
		} elseif ($this->operation === self::OP_OVERWRITE) {
			return TextDiff::compare(file_get_contents($this->path), $this->content);
		} else {
			return '';
		}
	}
}
