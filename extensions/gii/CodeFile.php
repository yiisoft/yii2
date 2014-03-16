<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii;

use Yii;
use yii\base\Object;
use yii\gii\components\DiffRendererHtmlInline;
use yii\helpers\Html;

/**
 * CodeFile represents a code file to be generated.
 *
 * @property string $relativePath The code file path relative to the application base path. This property is
 * read-only.
 * @property string $type The code file extension (e.g. php, txt). This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CodeFile extends Object
{
    /**
     * The code file is new.
     */
    const OP_CREATE = 'create';
    /**
     * The code file already exists, and the new one may need to overwrite it.
     */
    const OP_OVERWRITE = 'overwrite';
    /**
     * The new code file and the existing one are identical.
     */
    const OP_SKIP = 'skip';

    /**
     * @var string an ID that uniquely identifies this code file.
     */
    public $id;
    /**
     * @var string the file path that the new code should be saved to.
     */
    public $path;
    /**
     * @var string the newly generated code content
     */
    public $content;
    /**
     * @var string the operation to be performed. This can be [[OP_NEW]], [[OP_OVERWRITE]] or [[OP_SKIP]].
     */
    public $operation;

    /**
     * Constructor.
     * @param string $path    the file path that the new code should be saved to.
     * @param string $content the newly generated code content.
     */
    public function __construct($path, $content)
    {
        $this->path = strtr($path, ['/' => DIRECTORY_SEPARATOR, '\\' => DIRECTORY_SEPARATOR]);
        $this->content = $content;
        $this->id = md5($this->path);
        if (is_file($path)) {
            $this->operation = file_get_contents($path) === $content ? self::OP_SKIP : self::OP_OVERWRITE;
        } else {
            $this->operation = self::OP_CREATE;
        }
    }

    /**
     * Saves the code into the file specified by [[path]].
     * @return string|boolean the error occurred while saving the code file, or true if no error.
     */
    public function save()
    {
        $module = Yii::$app->controller->module;
        if ($this->operation === self::OP_CREATE) {
            $dir = dirname($this->path);
            if (!is_dir($dir)) {
                $mask = @umask(0);
                $result = @mkdir($dir, $module->newDirMode, true);
                @umask($mask);
                if (!$result) {
                    return "Unable to create the directory '$dir'.";
                }
            }
        }
        if (@file_put_contents($this->path, $this->content) === false) {
            return "Unable to write the file '{$this->path}'.";
        } else {
            $mask = @umask(0);
            @chmod($this->path, $module->newFileMode);
            @umask($mask);
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

    /**
     * Returns preview or false if it cannot be rendered
     *
     * @return boolean|string
     */
    public function preview()
    {
        if (($pos = strrpos($this->path, '.')) !== false) {
            $type = substr($this->path, $pos + 1);
        } else {
            $type = 'unknown';
        }

        if ($type === 'php') {
            return highlight_string($this->content, true);
        } elseif (!in_array($type, ['jpg', 'gif', 'png', 'exe'])) {
            return nl2br(Html::encode($this->content));
        } else {
            return false;
        }
    }

    /**
     * Returns diff or false if it cannot be calculated
     *
     * @return boolean|string
     */
    public function diff()
    {
        $type = strtolower($this->getType());
        if (in_array($type, ['jpg', 'gif', 'png', 'exe'])) {
            return false;
        } elseif ($this->operation === self::OP_OVERWRITE) {
            return $this->renderDiff(file($this->path), $this->content);
        } else {
            return '';
        }
    }

    /**
     * Renders diff between two sets of lines
     *
     * @param  mixed  $lines1
     * @param  mixed  $lines2
     * @return string
     */
    private function renderDiff($lines1, $lines2)
    {
        if (!is_array($lines1)) {
            $lines1 = explode("\n", $lines1);
        }
        if (!is_array($lines2)) {
            $lines2 = explode("\n", $lines2);
        }
        foreach ($lines1 as $i => $line) {
            $lines1[$i] = rtrim($line, "\r\n");
        }
        foreach ($lines2 as $i => $line) {
            $lines2[$i] = rtrim($line, "\r\n");
        }

        $renderer = new DiffRendererHtmlInline();
        $diff = new \Diff($lines1, $lines2);

        return $diff->render($renderer);
    }
}
