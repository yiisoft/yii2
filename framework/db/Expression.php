<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * Expression 表示不需要转义或引用的 DB 表达式。
 *
 * 当表达式对象嵌入到 SQL 语句或片段时，
 * 它将替换为 [[expression]] 属性值，而不进行任何的 DB 转义或引用。
 * 例如，
 *
 * ```php
 * $expression = new Expression('NOW()');
 * $now = (new \yii\db\Query)->select($expression)->scalar();  // SELECT NOW();
 * echo $now; // prints the current date
 * ```
 *
 * 表达式对象主要用于将原始 SQL 表达式传递给[[Query]]，
 * [[ActiveQuery]] 和相关类的方法。
 *
 * 表达式还可以通过 [[params]] 指定的参数绑定。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Expression extends \yii\base\BaseObject implements ExpressionInterface
{
    /**
     * @var string DB 表达式
     */
    public $expression;
    /**
     * @var array 应为此表达式绑定的参数列表。
     * 键是出现在 [[expression]] 中的占位符，
     * 值是相应的参数值。
     */
    public $params = [];


    /**
     * 构造函数。
     * @param string $expression DB 表达式
     * @param array $params 参数
     * @param array $config 将用于初始化对象属性的键值对
     */
    public function __construct($expression, $params = [], $config = [])
    {
        $this->expression = $expression;
        $this->params = $params;
        parent::__construct($config);
    }

    /**
     * String 魔术方法。
     * @return string DB 表达式。
     */
    public function __toString()
    {
        return $this->expression;
    }
}
