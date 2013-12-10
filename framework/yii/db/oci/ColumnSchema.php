<?php
namespace yii\db\oci;

class ColumnSchema extends \yii\db\ColumnSchema
{

    /**
     * Initializes the column with its DB type and default value.
     * This sets up the column's PHP type, size, precision, scale as well as default value.
     * 
     * @param string $dbType
     *            the column's DB type
     * @param mixed $defaultValue
     *            the default value
     */
    public function extract($dbType, $defaultValue)
    {
        $this->dbType = $dbType;
        $this->extractType($dbType);
        $this->extractLimit($dbType);
        if ($defaultValue !== null)
            $this->extractDefault($defaultValue);
    }

    /**
     * Extracts the PHP type from DB type.
     * 
     * @param string $dbType
     *            DB type
     * @return string
     */
    protected function extractOraType($dbType)
    {
        if (strpos($dbType, 'FLOAT') !== false)
            return 'double';
        
        if (strpos($dbType, 'NUMBER') !== false || strpos($dbType, 'INTEGER') !== false) {
            if (strpos($dbType, '(') && preg_match('/\((.*)\)/', $dbType, $matches)) {
                $values = explode(',', $matches[1]);
                if (isset($values[1]) and (((int) $values[1]) > 0))
                    return 'double';
                else
                    return 'integer';
            } else
                return 'double';
        } else
            return 'string';
    }

    /**
     * Extracts the PHP type from DB type.
     * 
     * @param string $dbType
     *            DB type
     */
    protected function extractType($dbType)
    {
        $this->type = $this->extractOraType($dbType);
    }

    /**
     * Extracts size, precision and scale information from column's DB type.
     * 
     * @param string $dbType
     *            the column's DB type
     */
    protected function extractLimit($dbType)
    {
        if (strpos($dbType, '(') && preg_match('/\((.*)\)/', $dbType, $matches)) {
            $values = explode(',', $matches[1]);
            $this->size = $this->precision = (int) $values[0];
            if (isset($values[1]))
                $this->scale = (int) $values[1];
        }
    }

    /**
     * Extracts the default value for the column.
     * The value is typecasted to correct PHP type.
     * 
     * @param mixed $defaultValue
     *            the default value obtained from metadata
     */
    protected function extractDefault($defaultValue)
    {
        if (stripos($defaultValue, 'timestamp') !== false) {
            $this->defaultValue = null;
        } else {
            $this->defaultValue = $this->typecast($defaultValue);
        }
    }
}
