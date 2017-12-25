<?php

namespace yii\db;

/**
 * Class PdoValue represents a $value that should be bound to PDO with defined $type and
 * should not be transformed before binding.
 *
 * TODO: usage example description, update docs in framework to follow this change.
 *
 * ```php
 * [':name' => 'John', ':profile' => new PdoValue($profile, \PDO::PARAM_LOB)]`.
 * ```
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class PdoValue implements ExpressionInterface
{
    /**
     * @var mixed
     */
    protected $value;
    /**
     * @var int One of PDO_PARAM_* constants
     * @see http://php.net/manual/en/pdo.constants.php
     */
    protected $type;

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

    public function getValue()
    {
        return $this->value;
    }

    public function getType()
    {
        return $this->type;
    }
}
