<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

/**
 * 控制台请求表示控制台应用程序的环境信息。
 *
 * 它是 PHP `$_SERVER` 变量的包装器，该变量包含有关当前运行的
 * PHP 脚本及其命令行参数的信息。
 *
 * @property array $params 命令行参数。它不包括条目脚本名称。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Request extends \yii\base\Request
{
    private $_params;


    /**
     * 返回命令行参数。
     * @return array 命令行参数。它不包括条目脚本名称。
     */
    public function getParams()
    {
        if ($this->_params === null) {
            if (isset($_SERVER['argv'])) {
                $this->_params = $_SERVER['argv'];
                array_shift($this->_params);
            } else {
                $this->_params = [];
            }
        }

        return $this->_params;
    }

    /**
     * 设置命令行参数。
     * @param array $params 命令行参数
     */
    public function setParams($params)
    {
        $this->_params = $params;
    }

    /**
     * 将当前请求解析为路由和相关参数。
     * @return array 第一个元素是路由，第二个元素是关联的参数。
     * @throws Exception 当参数错误且无法解析时
     */
    public function resolve()
    {
        $rawParams = $this->getParams();
        $endOfOptionsFound = false;
        if (isset($rawParams[0])) {
            $route = array_shift($rawParams);

            if ($route === '--') {
                $endOfOptionsFound = true;
                $route = array_shift($rawParams);
            }
        } else {
            $route = '';
        }

        $params = [];
        $prevOption = null;
        foreach ($rawParams as $param) {
            if ($endOfOptionsFound) {
                $params[] = $param;
            } elseif ($param === '--') {
                $endOfOptionsFound = true;
            } elseif (preg_match('/^--([\w-]+)(?:=(.*))?$/', $param, $matches)) {
                $name = $matches[1];
                if (is_numeric(substr($name, 0, 1))) {
                    throw new Exception('Parameter "' . $name . '" is not valid');
                }

                if ($name !== Application::OPTION_APPCONFIG) {
                    $params[$name] = isset($matches[2]) ? $matches[2] : true;
                    $prevOption = &$params[$name];
                }
            } elseif (preg_match('/^-([\w-]+)(?:=(.*))?$/', $param, $matches)) {
                $name = $matches[1];
                if (is_numeric($name)) {
                    $params[] = $param;
                } else {
                    $params['_aliases'][$name] = isset($matches[2]) ? $matches[2] : true;
                    $prevOption = &$params['_aliases'][$name];
                }
            } elseif ($prevOption === true) {
                // `--option value` syntax
                $prevOption = $param;
            } else {
                $params[] = $param;
            }
        }

        return [$route, $params];
    }
}
