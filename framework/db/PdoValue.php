<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * Class PdoValue represents a $value that should be bound to PDO with exact $type.
 *
 * For example, it will be useful when you need to bind binary data to BLOB column in DBMS:
 *
 * ```
 * [':name' => 'John', ':profile' => new PdoValue($profile, \PDO::PARAM_LOB)]`.
 * ```
 *
 * To see possible types, check [PDO::PARAM_* constants](https://www.php.net/manual/en/pdo.constants.php).
 *
 * @see https://www.php.net/manual/en/pdostatement.bindparam.php
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 * @phpcs:disable Squiz.NamingConventions.ValidVariableName.PrivateNoUnderscore
 */
final class PdoValue implements ExpressionInterface
{
    /**
     * @var mixed
     */
    private $value;
    /**
     * @var int One of PDO_PARAM_* constants
     * @see https://www.php.net/manual/en/pdo.constants.php
     */
    private $type;


    /**
     * PdoValue constructor.
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
