<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\Component;
use yii\base\Exception;

/**
 * AssetConverter supports conversion of several popular script formats into JS or CSS scripts.
 *
 * It is used by [[AssetManager]] to convert files after they have been published.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AssetConverter extends Component implements AssetConverterInterface
{
    /**
     * @var array the commands that are used to perform the asset conversion.
     * The keys are the asset file extension names, and the values are the corresponding
     * target script types (either "css" or "js") and the commands used for the conversion.
     *
     * You may also use a [path alias](guide:concept-aliases) to specify the location of the command:
     *
     * ```php
     * [
     *     'styl' => ['css', '@app/node_modules/bin/stylus < {from} > {to}'],
     * ]
     * ```
     */
    public $commands = [
        'less' => ['css', 'lessc {from} {to} --no-color --source-map'],
        'scss' => ['css', 'sass {from} {to} --sourcemap'],
        'sass' => ['css', 'sass {from} {to} --sourcemap'],
        'styl' => ['css', 'stylus < {from} > {to}'],
        'coffee' => ['js', 'coffee -p {from} > {to}'],
        'ts' => ['js', 'tsc --out {to} {from}'],
    ];
    /**
     * @var bool whether the source asset file should be converted even if its result already exists.
     * You may want to set this to be `true` during the development stage to make sure the converted
     * assets are always up-to-date. Do not set this to true on production servers as it will
     * significantly degrade the performance.
     */
    public $forceConvert = false;
    /**
     * @var callable a PHP callback, which should be invoked to check whether asset conversion result is outdated.
     * It will be invoked only if conversion target file exists and its modification time is older then the one of source file.
     * Callback should match following signature:
     *
     * ```php
     * function (string $basePath, string $sourceFile, string $targetFile, string $sourceExtension, string $targetExtension) : bool
     * ```
     *
     * where $basePath is the asset source directory; $sourceFile is the asset source file path, relative to $basePath;
     * $targetFile is the asset target file path, relative to $basePath; $sourceExtension is the source asset file extension
     * and $targetExtension is the target asset file extension, respectively.
     *
     * It should return `true` is case asset should be reconverted.
     * For example:
     *
     * ```php
     * function ($basePath, $sourceFile, $targetFile, $sourceExtension, $targetExtension) {
     *     if (YII_ENV !== 'dev') {
     *         return false;
     *     }
     *
     *     $resultModificationTime = @filemtime("$basePath/$result");
     *     foreach (FileHelper::findFiles($basePath, ['only' => ["*.{$sourceExtension}"]]) as $filename) {
     *         if ($resultModificationTime < @filemtime($filename)) {
     *             return true;
     *         }
     *     }
     *
     *     return false;
     * }
     * ```
     *
     * @since 3.0.0
     */
    public $isOutdatedCallback;


    /**
     * Converts a given asset file into a CSS or JS file.
     * @param string $asset the asset file path, relative to $basePath
     * @param string $basePath the directory the $asset is relative to.
     * @return string the converted asset file path, relative to $basePath.
     */
    public function convert($asset, $basePath)
    {
        $pos = strrpos($asset, '.');
        if ($pos !== false) {
            $srcExt = substr($asset, $pos + 1);
            if (isset($this->commands[$srcExt])) {
                [$ext, $command] = $this->commands[$srcExt];
                $result = substr($asset, 0, $pos + 1) . $ext;
                if ($this->forceConvert || $this->isOutdated($basePath, $asset, $result, $srcExt, $ext)) {
                    $this->runCommand($command, $basePath, $asset, $result);
                }

                return $result;
            }
        }

        return $asset;
    }

    /**
     * Checks whether asset convert result is outdated, and thus should be reconverted.
     * @param string $basePath the directory the $asset is relative to.
     * @param string $sourceFile the asset source file path, relative to [[$basePath]].
     * @param string $targetFile the converted asset file path, relative to [[$basePath]].
     * @param string $sourceExtension source asset file extension.
     * @param string $targetExtension target asset file extension.
     * @return bool whether asset is outdated or not.
     * @since 3.0.0
     */
    protected function isOutdated($basePath, $sourceFile, $targetFile, $sourceExtension, $targetExtension)
    {
        $resultModificationTime = @filemtime("$basePath/$targetFile");
        if ($resultModificationTime === false || $resultModificationTime === null) {
            return true;
        }

        if ($resultModificationTime < @filemtime("$basePath/$sourceFile")) {
            return true;
        }

        if ($this->isOutdatedCallback === null) {
            return false;
        }

        return call_user_func($this->isOutdatedCallback, $basePath, $sourceFile, $targetFile, $sourceExtension, $targetExtension);
    }

    /**
     * Runs a command to convert asset files.
     * @param string $command the command to run. If prefixed with an `@` it will be treated as a [path alias](guide:concept-aliases).
     * @param string $basePath asset base path and command working directory
     * @param string $asset the name of the asset file
     * @param string $result the name of the file to be generated by the converter command
     * @return bool true on success, false on failure. Failures will be logged.
     * @throws \yii\base\Exception when the command fails and YII_DEBUG is true.
     * In production mode the error will be logged.
     */
    protected function runCommand($command, $basePath, $asset, $result)
    {
        $command = Yii::getAlias($command);

        $command = strtr($command, [
            '{from}' => escapeshellarg("$basePath/$asset"),
            '{to}' => escapeshellarg("$basePath/$result"),
        ]);
        $descriptor = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $pipes = [];
        $proc = proc_open($command, $descriptor, $pipes, $basePath);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        foreach ($pipes as $pipe) {
            fclose($pipe);
        }
        $status = proc_close($proc);

        if ($status === 0) {
            Yii::debug("Converted $asset into $result:\nSTDOUT:\n$stdout\nSTDERR:\n$stderr", __METHOD__);
        } elseif (YII_DEBUG) {
            throw new Exception("AssetConverter command '$command' failed with exit code $status:\nSTDOUT:\n$stdout\nSTDERR:\n$stderr");
        } else {
            Yii::error("AssetConverter command '$command' failed with exit code $status:\nSTDOUT:\n$stdout\nSTDERR:\n$stderr", __METHOD__);
        }

        return $status === 0;
    }
}
