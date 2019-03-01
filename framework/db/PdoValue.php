<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * 类 PdoValue 表示应该使用确切的 $type 绑定到 PDO 的一个 $value。
 *
 * 例如，当您需要将二进制数据绑定到 DBMS 中的 BLOB 列时，它将会非常有用：
 *
 * ```php
 * [':name' => 'John', ':profile' => new PdoValue($profile, \PDO::PARAM_LOB)]`.
 * ```
 *
 * 要查看可能的类型，请参考 [PDO::PARAM_* constants](http://php.net/manual/en/pdo.constants.php)。
 *
 * @see http://php.net/manual/en/pdostatement.bindparam.php
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
final class PdoValue implements ExpressionInterface
{
    /**
     * @var mixed
     */
    private $value;
    /**
     * @var int PDO_PARAM_* 常量之一
     * @see http://php.net/manual/en/pdo.constants.php
     */
    private $type;


    /**
     * PdoValue 构造函数。
     *
     * @param $value
     * @param $type
     */
    public function __construct($value, $type)
    {
        $this->value = $value;
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }
}
