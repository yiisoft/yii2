<?php
namespace yii\db\pgsql;

use yii\db\pgsql\EncoderTrait;

/**
 * ColumnSchema class describes the metadata of a column in a PostgreSQL database table.
 *
 * @author Ievgen Sentiabov <ievgen.sentiabov@gmail.com>
 */
class ColumnSchema extends \yii\db\ColumnSchema
{
    use EncoderTrait;

    /**
     * @override
     */
    public function phpTypecast($value)
    {
        switch ($this->type) {
            case Schema::TYPE_INTEGER_ARRAY:
                return $this->arrayIntDecode($value);
            case Schema::TYPE_TEXT_ARRAY:
                return $this->arrayTextDecode($value);
            case Schema::TYPE_NUMERIC_ARRAY:
                return $this->arrayNumericDecode($value);
            default:
                return $this->typecast($value);
        }
    }

    /**
     * @override
     */
    public function dbTypecast($value)
    {
        switch ($this->type) {
            case Schema::TYPE_INTEGER_ARRAY:
                return $this->arrayIntEncode($value);
            case Schema::TYPE_TEXT_ARRAY:
                return $this->arrayTextEncode($value);
            case Schema::TYPE_NUMERIC_ARRAY:
                return $this->arrayNumericEncode($value);
            default:
                return $this->typecast($value);
        }
    }
}
