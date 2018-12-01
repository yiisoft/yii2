<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * Request 表示由 [[Application]] 处理的请求。
 *
 * 有关 Request 的更多详细信息和使用信息，请参阅 [请求的指南文章](guide:runtime-requests)。
 *
 * @property bool $isConsoleRequest 指示当前请求是否通过控制台运行的值。
 * @property string $scriptFile 入口脚本文件的路径（processed w/ realpath()）。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Request extends Component
{
    private $_scriptFile;
    private $_isConsoleRequest;


    /**
     * 将当前请求解析为路由和相关参数。
     * @return array 第一个元素是路由，第二个元素是相关参数。
     */
    abstract public function resolve();

    /**
     * 返回一个值，该值指示当前请求是否通过命令行生成。
     * @return bool 指示当前请求是否通过控制台运行的值
     */
    public function getIsConsoleRequest()
    {
        return $this->_isConsoleRequest !== null ? $this->_isConsoleRequest : PHP_SAPI === 'cli';
    }

    /**
     * 设置指示当前请求是否通过命令行生成的值。
     * @param bool $value 指示当前请求是否通过命令行生成的值
     */
    public function setIsConsoleRequest($value)
    {
        $this->_isConsoleRequest = $value;
    }

    /**
     * 返回入口脚本文件的路径。
     * @return string 入口脚本文件路径（processed w/ realpath()）
     * @throws InvalidConfigException 如果无法自动确定入口脚本文件路径。
     */
    public function getScriptFile()
    {
        if ($this->_scriptFile === null) {
            if (isset($_SERVER['SCRIPT_FILENAME'])) {
                $this->setScriptFile($_SERVER['SCRIPT_FILENAME']);
            } else {
                throw new InvalidConfigException('Unable to determine the entry script file path.');
            }
        }

        return $this->_scriptFile;
    }

    /**
     * 设置入口脚本文件路径。
     * 通常可以根据 `SCRIPT_FILENAME` SERVER 变量确定入口脚本文件路径。
     * 但是，对于某些服务器配置，这可能不正确或不可行。
     * 提供此 setter 以便可以手动指定入口脚本文件路径。
     * @param string $value 入口脚本文件路径。可以是文件路径或 [路径别名](guide:concept-aliases)。
     * @throws InvalidConfigException 如果提供的入口脚本文件路径无效。
     */
    public function setScriptFile($value)
    {
        $scriptFile = realpath(Yii::getAlias($value));
        if ($scriptFile !== false && is_file($scriptFile)) {
            $this->_scriptFile = $scriptFile;
        } else {
            throw new InvalidConfigException('Unable to determine the entry script file path.');
        }
    }
}
