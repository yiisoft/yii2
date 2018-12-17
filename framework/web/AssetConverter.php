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
 * AssetConverter 支持将一些流行的脚本格式编译成 JS 或者 CSS 脚本。
 *
 * 它被 [[AssetManager]] 用于编译被发布的文件。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AssetConverter extends Component implements AssetConverterInterface
{
    /**
     * @var array 用于执行资源编译的命令列表。
     * 键是资源文件的扩展名，
     * 值是相应的目标脚本（“css” 或者 “js”）和用来编译的命令。
     *
     * 你也可以用 [路径别名](guide:concept-aliases) 来指定命令的位置：
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
     * @var bool 是否应编译源资源文件，即使其结果已存在。
     * 你可能需要设置此属性为 `true` ，在开发阶段确保编译资源一直是最新的。
     * 不要再生产服务器设置此值为 true，
     * 它会显着降低性能。
     */
    public $forceConvert = false;


    /**
     * 将所给的资源文件编译成 JS 或者 CSS 文件。
     * @param string $asset 资源文件路径，相对于 $basePath。
     * @param string $basePath 资源 $asset 相对于的目录。
     * @return string 编译成的资源文件路径，相对于 $basePath。
     */
    public function convert($asset, $basePath)
    {
        $pos = strrpos($asset, '.');
        if ($pos !== false) {
            $ext = substr($asset, $pos + 1);
            if (isset($this->commands[$ext])) {
                list($ext, $command) = $this->commands[$ext];
                $result = substr($asset, 0, $pos + 1) . $ext;
                if ($this->forceConvert || @filemtime("$basePath/$result") < @filemtime("$basePath/$asset")) {
                    $this->runCommand($command, $basePath, $asset, $result);
                }

                return $result;
            }
        }

        return $asset;
    }

    /**
     * 执行命令来编译资源文件。
     * @param string $command 执行的命令。如果以 `@` 前缀，则当作 [路径别名](guide:concept-aliases)。
     * @param string $basePath 资源基路径和命令的工作目录
     * @param string $asset 资源文件名
     * @param string $result 编译命令将生成的文件名
     * @return bool 成功时为 true，失败为 false。失败时会记日志。
     * @throws \yii\base\Exception 当命令失败和 YII_DEBUG 为 true 时抛出。
     * 而生产模式下记录错误日志。
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
