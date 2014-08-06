<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * Request represents a request that is handled by an [[Application]].
 *
 * @property boolean $isConsoleRequest The value indicating whether the current request is made via console.
 * @property string $scriptFile Entry script file path (processed w/ realpath()).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Request extends Component
{
    private $_scriptFile;
    private $_isConsoleRequest;


    /**
     * Resolves the current request into a route and the associated parameters.
     * @return array the first element is the route, and the second is the associated parameters.
     */
    abstract public function resolve();

    /**
     * Returns a value indicating whether the current request is made via command line
     * @return boolean the value indicating whether the current request is made via console
     */
    public function getIsConsoleRequest()
    {
        return $this->_isConsoleRequest !== null ? $this->_isConsoleRequest : PHP_SAPI === 'cli';
    }

    /**
     * Sets the value indicating whether the current request is made via command line
     * @param boolean $value the value indicating whether the current request is made via command line
     */
    public function setIsConsoleRequest($value)
    {
        $this->_isConsoleRequest = $value;
    }

    /**
     * Returns entry script file path.
     * @return string entry script file path (processed w/ realpath())
     * @throws InvalidConfigException if the entry script file path cannot be determined automatically.
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
     * Sets the entry script file path.
     * The entry script file path can normally be determined based on the `SCRIPT_FILENAME` SERVER variable.
     * However, for some server configurations, this may not be correct or feasible.
     * This setter is provided so that the entry script file path can be manually specified.
     * @param string $value the entry script file path. This can be either a file path or a path alias.
     * @throws InvalidConfigException if the provided entry script file path is invalid.
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
