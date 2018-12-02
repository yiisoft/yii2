<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

if (version_compare(PHP_VERSION, '4.3', '<')) {
    echo 'At least PHP 4.3 is required to run this script!';
    exit(1);
}

/**
 * YiiRequirementChecker 允许检查，如果当前系统满足运行 Yii 应用程序的要求。
 * 此类允许渲染 web 和控制台应用程序界面的检查报告。
 *
 * 例如：
 *
 * ```php
 * require_once 'path/to/YiiRequirementChecker.php';
 * $requirementsChecker = new YiiRequirementChecker();
 * $requirements = array(
 *     array(
 *         'name' => 'PHP Some Extension',
 *         'mandatory' => true,
 *         'condition' => extension_loaded('some_extension'),
 *         'by' => 'Some application feature',
 *         'memo' => 'PHP extension "some_extension" required',
 *     ),
 * );
 * $requirementsChecker->checkYii()->check($requirements)->render();
 * ```
 *
 * 如果您希望使用自己的表示形式渲染报告，请使用 [[getResult()]] 代替 [[render()]]
 *
 * 需求条件可以采用 "eval:PHP expression" 格式。
 * 在这种情况下，将在此类实例的上下文中计算指定的 PHP 表达式。
 * 例如：
 *
 * ```php
 * $requirements = array(
 *     array(
 *         'name' => 'Upload max file size',
 *         'condition' => 'eval:$this->checkUploadMaxFileSize("5M")',
 *     ),
 * );
 * ```
 *
 * Note: 这个类定义与普通的 Yii 样式不匹配，因为它应该与
 * PHP 4.3 匹配，不应该使用较新的PHP版本的功能！
 *
 * @property array|null $result 检查结果，此属性仅供内部使用。
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class YiiRequirementChecker
{
    /**
     * 检查给定的需求，将结果收集到内部字段中。
     * 可以多次调用此方法来检查不同的需求集。
     * 使用 [[getResult()]] 或 [[render()]] 来获得结果
     * @param array|string $requirements 要检查的需求。
     * 如果是数组，则将其视为一组需求;
     * 如果是字符串，则将其视为文件的路径，其中包含需求;
     * @return $this 自身实例。
     */
    function check($requirements)
    {
        if (is_string($requirements)) {
            $requirements = require $requirements;
        }
        if (!is_array($requirements)) {
            $this->usageError('Requirements must be an array, "' . gettype($requirements) . '" has been given!');
        }
        if (!isset($this->result) || !is_array($this->result)) {
            $this->result = array(
                'summary' => array(
                    'total' => 0,
                    'errors' => 0,
                    'warnings' => 0,
                ),
                'requirements' => array(),
            );
        }
        foreach ($requirements as $key => $rawRequirement) {
            $requirement = $this->normalizeRequirement($rawRequirement, $key);
            $this->result['summary']['total']++;
            if (!$requirement['condition']) {
                if ($requirement['mandatory']) {
                    $requirement['error'] = true;
                    $requirement['warning'] = true;
                    $this->result['summary']['errors']++;
                } else {
                    $requirement['error'] = false;
                    $requirement['warning'] = true;
                    $this->result['summary']['warnings']++;
                }
            } else {
                $requirement['error'] = false;
                $requirement['warning'] = false;
            }
            $this->result['requirements'][] = $requirement;
        }

        return $this;
    }

    /**
     * 执行 Yii 核心要求的检查。
     * @return YiiRequirementChecker 自身实例。
     */
    function checkYii()
    {
        return $this->check(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'requirements.php');
    }

    /**
     * 返回检查结果。
     * @return array|null 检查结果格式：
     *
     * ```php
     * array(
     *     'summary' => array(
     *         'total' => total number of checks,
     *         'errors' => number of errors,
     *         'warnings' => number of warnings,
     *     ),
     *     'requirements' => array(
     *         array(
     *             ...
     *             'error' => is there an error,
     *             'warning' => is there a warning,
     *         ),
     *         ...
     *     ),
     * )
     * ```
     */
    function getResult()
    {
        if (isset($this->result)) {
            return $this->result;
        } else {
            return null;
        }
    }

    /**
     * 渲染需求检查结果。
     * 输出将根据从 web 或控制台运行的脚本而有所不同。
     */
    function render()
    {
        if (!isset($this->result)) {
            $this->usageError('Nothing to render!');
        }
        $baseViewFilePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'views';
        if (!empty($_SERVER['argv'])) {
            $viewFileName = $baseViewFilePath . DIRECTORY_SEPARATOR . 'console' . DIRECTORY_SEPARATOR . 'index.php';
        } else {
            $viewFileName = $baseViewFilePath . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'index.php';
        }
        $this->renderViewFile($viewFileName, $this->result);
    }

    /**
     * 检查给定的 PHP 扩展是否可用且其版本是否与给定的版本匹配。
     * @param string $extensionName PHP 扩展名称。
     * @param string $version 必需的 PHP 扩展版本。
     * @param string $compare 比较运算符，默认为 '>='
     * @return bool 如果 PHP 扩展版本匹配。
     */
    function checkPhpExtensionVersion($extensionName, $version, $compare = '>=')
    {
        if (!extension_loaded($extensionName)) {
            return false;
        }
        $extensionVersion = phpversion($extensionName);
        if (empty($extensionVersion)) {
            return false;
        }
        if (strncasecmp($extensionVersion, 'PECL-', 5) === 0) {
            $extensionVersion = substr($extensionVersion, 5);
        }

        return version_compare($extensionVersion, $version, $compare);
    }

    /**
     * 检查 PHP 配置选项（来自 php.ini）是否打开。
     * @param string $name 配置选项名称。
     * @return bool 选项已开启。
     */
    function checkPhpIniOn($name)
    {
        $value = ini_get($name);
        if (empty($value)) {
            return false;
        }

        return ((int) $value === 1 || strtolower($value) === 'on');
    }

    /**
     * 检查 PHP 配置选项（来自 php.ini）是否关闭。
     * @param string $name 配置选项名称。
     * @return bool 选项已关闭。
     */
    function checkPhpIniOff($name)
    {
        $value = ini_get($name);
        if (empty($value)) {
            return true;
        }

        return (strtolower($value) === 'off');
    }

    /**
     * 比较详细表示中给出的值的字节大小，
     * 例如：'5M'，'15K' 等。
     * @param string $a 第一个值。
     * @param string $b 第二个值。
     * @param string $compare 比较运算符，默认为 '>='。
     * @return bool 比较结果。
     */
    function compareByteSize($a, $b, $compare = '>=')
    {
        $compareExpression = '(' . $this->getByteSize($a) . $compare . $this->getByteSize($b) . ')';

        return $this->evaluateExpression($compareExpression);
    }

    /**
     * 从详细大小表示中获取大小（以字节为单位）。
     * 例如：'5K' => 5*1024
     * @param string $verboseSize 详细的大小表示。
     * @return int 实际大小（字节）。
     */
    function getByteSize($verboseSize)
    {
        if (empty($verboseSize)) {
            return 0;
        }
        if (is_numeric($verboseSize)) {
            return (int) $verboseSize;
        }
        $sizeUnit = trim($verboseSize, '0123456789');
        $size = trim(str_replace($sizeUnit, '', $verboseSize));
        if (!is_numeric($size)) {
            return 0;
        }
        switch (strtolower($sizeUnit)) {
            case 'kb':
            case 'k':
                return $size * 1024;
            case 'mb':
            case 'm':
                return $size * 1024 * 1024;
            case 'gb':
            case 'g':
                return $size * 1024 * 1024 * 1024;
            default:
                return 0;
        }
    }

    /**
     * 检查上传的最大文件大小是否与给定范围匹配。
     * @param string|null $min 上传文件大小最小要求值，传递 null 以跳过最小检查。
     * @param string|null $max 上传文件大小最大要求值，传递 null 以跳过最大检查。
     * @return bool 成功。
     */
    function checkUploadMaxFileSize($min = null, $max = null)
    {
        $postMaxSize = ini_get('post_max_size');
        $uploadMaxFileSize = ini_get('upload_max_filesize');
        if ($min !== null) {
            $minCheckResult = $this->compareByteSize($postMaxSize, $min, '>=') && $this->compareByteSize($uploadMaxFileSize, $min, '>=');
        } else {
            $minCheckResult = true;
        }
        if ($max !== null) {
            $maxCheckResult = $this->compareByteSize($postMaxSize, $max, '<=') && $this->compareByteSize($uploadMaxFileSize, $max, '<=');
        } else {
            $maxCheckResult = true;
        }

        return ($minCheckResult && $maxCheckResult);
    }

    /**
     * 渲染一个视图文件。
     * 此方法将视图文件包含为 PHP 脚本，
     * 并在需要时捕获显示结果。
     * @param string $_viewFile_ 视图文件
     * @param array $_data_ 要提取的数据并使其可用于视图文件
     * @param bool $_return_ 是否应将渲染结果作为字符串返回
     * @return string 渲染结果。如果不需要渲染结果，则为空。
     */
    function renderViewFile($_viewFile_, $_data_ = null, $_return_ = false)
    {
        // we use special variable names here to avoid conflict when extracting data
        if (is_array($_data_)) {
            extract($_data_, EXTR_PREFIX_SAME, 'data');
        } else {
            $data = $_data_;
        }
        if ($_return_) {
            ob_start();
            ob_implicit_flush(false);
            require $_viewFile_;

            return ob_get_clean();
        } else {
            require $_viewFile_;
        }
    }

    /**
     * 规范化需求以确保其格式正确。
     * @param array $requirement 原始需求。
     * @param int $requirementKey 列表中的需求键。
     * @return array 标准化需求。
     */
    function normalizeRequirement($requirement, $requirementKey = 0)
    {
        if (!is_array($requirement)) {
            $this->usageError('Requirement must be an array!');
        }
        if (!array_key_exists('condition', $requirement)) {
            $this->usageError("Requirement '{$requirementKey}' has no condition!");
        } else {
            $evalPrefix = 'eval:';
            if (is_string($requirement['condition']) && strpos($requirement['condition'], $evalPrefix) === 0) {
                $expression = substr($requirement['condition'], strlen($evalPrefix));
                $requirement['condition'] = $this->evaluateExpression($expression);
            }
        }
        if (!array_key_exists('name', $requirement)) {
            $requirement['name'] = is_numeric($requirementKey) ? 'Requirement #' . $requirementKey : $requirementKey;
        }
        if (!array_key_exists('mandatory', $requirement)) {
            if (array_key_exists('required', $requirement)) {
                $requirement['mandatory'] = $requirement['required'];
            } else {
                $requirement['mandatory'] = false;
            }
        }
        if (!array_key_exists('by', $requirement)) {
            $requirement['by'] = 'Unknown';
        }
        if (!array_key_exists('memo', $requirement)) {
            $requirement['memo'] = '';
        }

        return $requirement;
    }

    /**
     * 显示使用错误。
     * 然后，此方法将终止当前应用程序的执行。
     * @param string $message 错误消息
     */
    function usageError($message)
    {
        echo "Error: $message\n\n";
        exit(1);
    }

    /**
     * 在此类的上下文中计算 PHP 表达式。
     * @param string $expression 要计算的 PHP 表达式。
     * @return mixed 表达式结果。
     */
    function evaluateExpression($expression)
    {
        return eval('return ' . $expression . ';');
    }

    /**
     * 返回服务器信息。
     * @return string 服务器信息。
     */
    function getServerInfo()
    {
        return isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
    }

    /**
     * 如果可能，以字符串表示形式返回当前时间。
     * @return string 当前时间
     */
    function getNowDate()
    {
        return @strftime('%Y-%m-%d %H:%M', time());
    }
}
