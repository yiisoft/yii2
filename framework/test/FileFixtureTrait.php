<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\test;
use Yii;
use yii\base\InvalidConfigException;

/**
 * FixtureTrait provides functionalities for loading, unloading and accessing fixtures for a test case.
 *
 * By using FixtureTrait, a test class will be able to specify which fixtures to load by overriding
 * the [[fixtures()]] method. It can then load and unload the fixtures using [[loadFixtures()]] and [[unloadFixtures()]].
 * Once a fixture is loaded, it can be accessed like an object property, thanks to the PHP `__get()` magic method.
 * Also, if the fixture is an instance of [[ActiveFixture]], you will be able to access AR models
 * through the syntax `$this->fixtureName('model name')`.
 *
 * For more details and usage information on FixtureTrait, see the [guide article on fixtures](guide:test-fixtures).
 *
 * @author Leandro Guindani Gehlen <leandrogehlen@gmail.com>
 * @since 2.0.15
 */
trait FileFixtureTrait
{
    /**
     * @var string the directory path or [path alias](guide:concept-aliases) that contains the fixture data
     * @since 2.0.15
     */
    public $dataDirectory;
    /**
     * @var string|bool the file path or [path alias](guide:concept-aliases) of the data file that contains the fixture data
     * to be returned by [[getData()]]. You can set this property to be false to prevent loading any data.
     */
    public $dataFile;

    /**
     * Returns the fixture data.
     *
     * The default implementation will try to return the fixture data by including the external file specified by [[dataFile]].
     * The file should return the data array that will be stored in [[data]] after inserting into the database.
     *
     * @param string $file the data file path
     * @return array the data to be put into the database
     * @throws InvalidConfigException if the specified data file does not exist.
     */
    protected function loadData($file)
    {
        if ($file === false || $file === null) {
            return [];
        }

        if (basename($file) == $file && $this->dataDirectory !== null) {
            $file = $this->dataDirectory . '/' . $file;
        }

        $file = Yii::getAlias($file);
        if (is_file($file)) {
            return require $file;
        }

        throw new InvalidConfigException("Fixture data file does not exist: {$file}");
    }

}
