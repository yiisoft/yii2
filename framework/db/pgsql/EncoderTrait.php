<?php
namespace yii\db\pgsql;

/**
 * Trait contains methods to convert php arrays to PostgreSQL arrays and otherwise
 *
 * @author Ievgen Sentiabov <ievgen.sentiabov@gmail.com>
 */
trait EncoderTrait
{
    /**
     * Convert php integer array to postgres array
     * @param mixed $value
     * @return string
     */
    public function arrayIntEncode($value)
    {
        $data = array_map('intval', (array)$value);
        return '{'.implode(',', $data).'}';
    }

    /**
     * Convert postgres integer array to php array
     *
     * @access public
     * @param $value
     * @return array
     */
    public function arrayIntDecode($value)
    {
        $value = str_replace(['{', '}'], '', $value);
        if ($value === '') {
            return [];
        }
        $data = explode(',', $value);
        return array_map('intval', $data);
    }

    /**
     * Convert php string array to postgres text array
     *
     * @access public
     * @param mixed $value
     * @return string
     */
    public function arrayTextEncode($value)
    {
        return '{'.implode(',', (array)$value).'}';
    }

    /*
    * Convert postgres text array to php array
    *
    * @access public
    * @param string $value
    * @return array
    */
    public function arrayTextDecode($value)
    {
        //remove "{", "}"
        $data = substr($value, 1, -1);
        if (!$data) {
            return [];
        }
        $data = explode(',', $data);
        foreach ($data as &$item) {
            $item = trim($item, '"');
        }
        unset($item);
        return $data;
    }

    /**
     * Convert php array to postgres numeric array
     *
     * @access public
     * @param mixed $value
     * @return string
     */
    public function arrayNumericEncode($value)
    {
        foreach ((array)$value as &$item) {
            $item = str_replace(',', '.', $item);
        }
        unset($item);
        return '{'.implode(',', $value).'}';
    }

    /**
     * Convert postgres numeric array to php array
     *
     * @access public
     * @param string $value
     * @return array
     */
    public function arrayNumericDecode($value)
    {
        $value = str_replace(['{', '}'], '', $value);
        if ($value === '') {
            return [];
        }
        $value = explode(',', $value);
        return array_map('floatval', $value);
    }
}
