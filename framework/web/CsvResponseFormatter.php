<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\base\Arrayable;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * CsvResponseFormatter formats the given data into CSV response content.
 *
 * It is used by [[Response]] to format response data.
 *
 * @author Sam Mousa <sam@mousa.nl>
 * @since 2.0.9
 */
class CsvResponseFormatter extends Component implements ResponseFormatterInterface
{
    /**
     * @var int Maximum number of bytes to use in memory before using a temp file. Defaults to 20MB
     */
    public $maxMemory = 20971520;
    /**
     * @var string the Content-Type header for the response
     */
    public $contentType = 'text/csv';

    /**
     * @var boolean Whether to include column names as the first line, if data is associative.
     */
    public $includeColumnNames = true;

    /**
     * @var string The delimiter to use (one character only)
     * @see fputcsv
     */
    public $delimiter = ',';

    /**
     * @var string The field enclosure to use (one character only)
     * @see fputcsv
     */
    public $enclosure = '"';

    /**
     * @var string The escape character to use (one character only)
     * @see fputcsv
     */
    public $escape = '\'';

    /**
     * @var bool Whether to check all rows for column names. This means iterating the data twice but it adds support
     * for non-uniform data (ie rows with missing columns).
     */
    public $checkAllRows = false;

    /**
     * @var string The value to use for NULL values.
     */
    public $nullValue = "(null)";

    /**
     * @var string The value to use for missing columns (only applicable if `$checkAllRows` is true)
     */
    public $missingValue = "(missing)";

    /**
     * Formats the specified response.
     * @param Response $response the response to be formatted.
     * @throws \RuntimeException
     */
    public function format($response)
    {
        $response->getHeaders()->set('Content-Type', 'text/csv; charset=UTF-8');

        $handle = fopen('php://temp/maxmemory:' . intval($this->maxMemory), 'w+');
        $response->stream = $handle;
        
        if ($this->includeColumnNames && $this->checkAllRows) {
            $columns = $this->getColumnNames($response->data);
            if (empty($columns)) { return; }
            $outputHeader = false;

            $this->put($handle, $columns);

        } else {
            $outputHeader = true;
        }

        if (!$response->data instanceof \Traversable && !is_array($response->data)) {
            throw new \InvalidArgumentException('Response data must be traversable.');
        }

        foreach($response->data as $row) {
            if($outputHeader
                && $this->includeColumnNames
                && !$this->checkAllRows
                && \yii\helpers\ArrayHelper::isAssociative($row)

            ) {
                $this->put($handle, array_keys($row));
                $outputHeader = false;
            }

            if ($row instanceof Arrayable) {
                $row = $row->toArray();
            }

            $rowData = [];
            if (isset($columns)) {
                // Map columns.
                foreach($columns as $column) {
                    if (array_key_exists($column, $row)) {
                        $rowData[] = isset($row[$column]) ? $row[$column] : $this->nullValue;
                    } else {
                        $rowData[] = $this->missingValue;
                    }
                }

            } else {
                foreach($row as $column => $value) {
                    $rowData[] = isset($value) ? $value : $this->nullValue;
                }
            }
            $this->put($handle, $rowData);
        }
        rewind($handle);
    }

    /**
     * @param array|Traversable $data The data set
     * @return array The column names found in the data
     */
    protected function getColumnNames($data)
    {
        $columns = [];
        // Use foreach to support arrays and traversable objects.
        foreach($data as $row) {
            foreach($row as $column => $value) {
                if (is_int($column)) {
                    throw new InvalidConfigException('You should not use $checkAllRows in combination with non-associative rows.');
                }
                $columns[$column] = true;
            }

        }
        return array_keys($columns);
    }

    /**
     * Writes a line of CSV data using configuration from the formatter.
     * @param $handle The file handle write to
     * @param array $data The data to write
     * @throws \RuntimeException In case CSV data fails to write.
     */
    protected function put($handle, array $data)
    {
        if (PHP_VERSION_ID > 50504) {

            if (fputcsv($handle, $data, $this->delimiter, $this->enclosure, $this->escape) === false) {
                throw new \RuntimeException("Failed to write CSV data.");
            }
        } else {
            if (fputcsv($handle, $data, $this->delimiter, $this->enclosure) === false) {
                throw new \RuntimeException("Failed to write CSV data.");
            }
        }
    }
}
