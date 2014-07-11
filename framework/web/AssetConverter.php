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
     */
    public $commands = [
        'less' => ['css', 'lessc {from} {to} --no-color'],
        'scss' => ['css', 'sass {from} {to}'],
        'sass' => ['css', 'sass {from} {to}'],
        'styl' => ['js', 'stylus < {from} > {to}'],
        'coffee' => ['js', 'coffee -p {from} > {to}'],
        'ts' => ['js', 'tsc --out {to} {from}'],
    ];

    /**
     * Converts a given asset file into a CSS or JS file.
     * @param string $asset the asset file path, relative to $basePath
     * @param string $srcPath the asset source directory.
     * @param string $dstPath the asset destination directory.
     * @return string|boolean the converted asset file path, relative to $basePath,
     * if no conversion is made `false` will be returned.
     */
    public function convert($asset, $srcPath, $dstPath)
    {
        $pos = strrpos($asset, '.');
        if ($pos !== false) {
            $ext = substr($asset, $pos + 1);
            if (isset($this->commands[$ext])) {
                list ($ext, $command) = $this->commands[$ext];
                $result = substr($asset, 0, $pos + 1) . $ext;
                $srcFileName = $srcPath . DIRECTORY_SEPARATOR .$asset;
                $resultFileName = $dstPath . DIRECTORY_SEPARATOR . $result;
                if (@filemtime($resultFileName) < filemtime($srcFileName)) {
                    if ($this->runCommand($command, $srcFileName, $resultFileName)) {
                        return $result;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Runs a command to convert asset files.
     * @param string $command the command to run
     * @param string $srcFileName source file name.
     * @param string $resultFileName the name of the file to be generated by the converter command.
     * @return boolean true on success, false on failure. Failures will be logged.
     * @throws \yii\base\Exception when the command fails and YII_DEBUG is true.
     * In production mode the error will be logged.
     */
    protected function runCommand($command, $srcFileName, $resultFileName)
    {
        $command = strtr($command, [
            '{from}' => escapeshellarg($srcFileName),
            '{to}' => escapeshellarg($resultFileName),
        ]);
        $descriptor = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $pipes = [];
        $proc = proc_open($command, $descriptor, $pipes, dirname($srcFileName));
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        foreach ($pipes as $pipe) {
            fclose($pipe);
        }
        $status = proc_close($proc);

        if ($status === 0) {
            Yii::trace("Converted {$srcFileName} into {$resultFileName}:\nSTDOUT:\n$stdout\nSTDERR:\n$stderr", __METHOD__);
        } elseif (YII_DEBUG) {
            throw new Exception("AssetConverter command '$command' failed with exit code $status:\nSTDOUT:\n$stdout\nSTDERR:\n$stderr");
        } else {
            Yii::error("AssetConverter command '$command' failed with exit code $status:\nSTDOUT:\n$stdout\nSTDERR:\n$stderr", __METHOD__);
        }

        return $status === 0;
    }
}
