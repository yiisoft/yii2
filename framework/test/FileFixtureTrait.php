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
 * FileFixtureTrait 提供一些用于从文件中加载数据夹具的函数能力。
 *
 * @author Leandro Guindani Gehlen <leandrogehlen@gmail.com>
 * @since 2.0.14
 */
trait FileFixtureTrait
{
    /**
     * @var string 包含有夹具数据的目录地址或者 [path alias](guide:concept-aliases)
     */
    public $dataDirectory;
    /**
     * @var string|bool 包含有夹具数据的文件路径名称，或者 [path alias](guide:concept-aliases)，这些数据将作为 [[getData()]] 的返回值。
     * 你可以将属性设置为false以阻止加载数据。
     */
    public $dataFile;

    /**
     * 返回夹具数据
     *
     * 这个方法的默认实现是尝试返回通过 [[dataFile]] 指定的文件中包含的外部夹具数据。
     * 这个外部文件需要返回数据数组，这个数组在插入数据库后，将被存储在 [[data]] 属性中。
     *
     * @param string $file 数据文件路径
     * @param bool $throwException 如果夹具数据文件不存在时是否抛出异常。
     * @return array 将要填入数据库中的数据。
     * @throws InvalidConfigException 如果夹具数据文件不存在则抛出异常。
     */
    protected function loadData($file, $throwException = true)
    {
        if ($file === null || $file === false) {
            return [];
        }

        if (basename($file) === $file && $this->dataDirectory !== null) {
            $file = $this->dataDirectory . '/' . $file;
        }

        $file = Yii::getAlias($file);
        if (is_file($file)) {
            return require $file;
        }
        
        if ($throwException) {
            throw new InvalidConfigException("Fixture data file does not exist: {$file}");
        }

        return [];
    }

}
