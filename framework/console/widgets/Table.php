<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\widgets;

use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Table display a table in console.
 *
 * For example,
 *
 * ```php
 * $table = new Table();
 *
 * echo $table->setHeaders(['test1', 'test2', 'test3'])
 *            ->setRows([
 *               ['col1', 'col2', 'col3'],
 *               ['col1', 'col2', ['col3-0', 'col3-1', 'col3-2']]
 *      ])->render();
 * ```
 *
 * @author Daniel Gomez Pan <pana_1990@hotmail.com>
 * @since 2.0.12
 */
class Table extends Object
{
    /**
     * @var array table headers
     */
    private $_headers = [];
    /**
     * @var array table rows
     */
    private $_rows = [];
    /**
     * @var array table chars
     */
    private $_chars = [
        'top' => '═',
        'top-mid' => '╤',
        'top-left' => '╔',
        'top-right' => '╗',
        'bottom' => '═',
        'bottom-mid' => '╧',
        'bottom-left' => '╚',
        'bottom-right' => '╝',
        'left' => '║',
        'left-mid' => '╟',
        'mid' => '─',
        'mid-mid' => '┼',
        'right' => '║',
        'right-mid' => '╢',
        'middle' => '│',
    ];
    /**
     * @var array table column widths
     */
    private $_columnWidths = [];
    /**
     * @var integer screen size
     */
    private $_screenSize;
    /**
     * @var string list prefix
     */
    private $_listPrefix = '• ';

    /**
     * Set table headers
     *
     * @param array $headers table headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->_headers = $headers;
        return $this;
    }

    /**
     * Set table rows
     *
     * @param array $rows table rows
     * @return $this
     */
    public function setRows(array $rows)
    {
        $this->_rows = $rows;
        return $this;
    }

    /**
     * Set table chars
     *
     * @param array $chars table chars
     * @return $this
     */
    public function setChars(array $chars)
    {
        $this->_chars = $chars;
        return $this;
    }

    /**
     * Set screen width
     *
     * @param int $width screen width
     * @return $this
     */
    public function setScreenSize($width)
    {
        $this->_screenSize = $width;
        return $this;
    }

    /**
     * Set list prefix
     *
     * @param string $listPrefix list prefix
     * @return $this
     */
    public function setListPrefix($listPrefix)
    {
        $this->_listPrefix = $listPrefix;
        return $this;
    }

    /**
     * @return string the generated table
     */
    public function render()
    {
        $this->calculateRowsSize();
        $buffer = $this->renderSeparator(
            $this->_chars['top-left'],
            $this->_chars['top-mid'],
            $this->_chars['top'],
            $this->_chars['top-right']
        );
        // Header
        $buffer .= $this->renderRow($this->_headers,
            $this->_chars['left'],
            $this->_chars['middle'],
            $this->_chars['right']
        );

        // Content
        foreach ($this->_rows as $row) {
            $buffer .= $this->renderSeparator(
                $this->_chars['left-mid'],
                $this->_chars['mid-mid'],
                $this->_chars['mid'],
                $this->_chars['right-mid']
            );
            $buffer .= $this->renderRow($row,
                $this->_chars['left'],
                $this->_chars['middle'],
                $this->_chars['right']);
        }

        $buffer .= $this->renderSeparator(
            $this->_chars['bottom-left'],
            $this->_chars['bottom-mid'],
            $this->_chars['bottom'],
            $this->_chars['bottom-right']
        );

        return $buffer;
    }

    /**
     * @param array $row
     * @param string $spanLeft
     * @param string $spanMiddle
     * @param string $spanRight
     * @return string the generated row
     * @see \yii\console\widgets\Table::render()
     */
    protected function renderRow(array $row, $spanLeft, $spanMiddle, $spanRight)
    {
        $size = $this->_columnWidths;

        $buffer = '';
        $arrayPointer = [];
        for ($i = 0, $max = $this->calculateRowHeight($row); $i < $max; $i++) {
            $buffer .= $spanLeft . ' ';
            foreach ($row as $index => $cell) {
                $prefix = '';
                if ($index !== 0) {
                    $buffer .= $spanMiddle . ' ';
                }
                if (is_array($cell)) {
                    if (empty($finalChunk[$index])) {
                        $finalChunk[$index] = '';
                        $start = 0;
                        $prefix = $this->_listPrefix;
                        if (!isset($arrayPointer[$index])) {
                            $arrayPointer[$index] = 0;
                        }
                    } else {
                        $start = mb_strwidth($finalChunk[$index], Yii::$app->charset);
                    }
                    $chunk = mb_substr($cell[$arrayPointer[$index]], $start, $size[$index] - 4, Yii::$app->charset);
                    $finalChunk[$index] .= $chunk;
                    if (isset($cell[$arrayPointer[$index] + 1]) && $finalChunk[$index] === $cell[$arrayPointer[$index]]) {
                        $arrayPointer[$index]++;
                        $finalChunk[$index] = '';
                    }
                } else {
                    $chunk = mb_substr($cell, ($size[$index] * $i) - ($i * 2), $size[$index] - 2, Yii::$app->charset);
                }
                $chunk = $prefix . $chunk;
                $repeat = $size[$index] - mb_strwidth($chunk, Yii::$app->charset) - 1;
                $buffer .= $chunk;
                if ($repeat >= 0) {
                    $buffer .= str_repeat(' ', $repeat);
                }
            }
            $buffer .= "$spanRight\n";
        }

        return $buffer;
    }

    /**
     * @param string $spanLeft
     * @param string $spanMid
     * @param string $spanMidMid
     * @param string $spanRight
     * @return string the generated separator row
     * @see \yii\console\widgets\Table::render()
     */
    protected function renderSeparator($spanLeft, $spanMid, $spanMidMid, $spanRight)
    {
        $separator = $spanLeft;
        foreach ($this->_columnWidths as $index => $rowSize) {
            if ($index !== 0) {
                $separator .= $spanMid;
            }
            var_dump($this->_columnWidths);
            $separator .= str_repeat($spanMidMid, $rowSize);
        }
        $separator .= $spanRight . "\n";
        return $separator;
    }

    /**
     * Calculate the size of rows to draw anchor of columns in console
     *
     * @see \yii\console\widgets\Table::render()
     */
    protected function calculateRowsSize()
    {
        $this->_columnWidths = $columns = [];
        $totalWidth = 0;
        $screenWidth = $this->getScreenSize() - 3;

        foreach ($this->_headers as $i => $header) {
            $columns[] = ArrayHelper::getColumn($this->_rows, $i);
            $columns[$i][] = $this->_headers[$i];
        }
        foreach ($columns as $column) {
            $columnWidth = max(array_map(function ($val) {
                    if (is_array($val)) {
                        $encodings = array_fill(0, count($val), Yii::$app->charset);
                        return max(array_map('mb_strwidth', $val, $encodings)) + mb_strwidth($this->_listPrefix, Yii::$app->charset);
                    }
                    return mb_strwidth($val, Yii::$app->charset);
                }, $column)) + 2;
            $this->_columnWidths[] = $columnWidth;
            $totalWidth += $columnWidth;
        }

        $relativeWidth = $screenWidth / $totalWidth;

        if ($totalWidth > $screenWidth) {
            foreach ($this->_columnWidths as $j => $width) {
                $this->_columnWidths[$j] = (int)($width * $relativeWidth);
                if ($j === count($this->_columnWidths)) {
                    $this->_columnWidths = $totalWidth;
                }
                $totalWidth -= $this->_columnWidths[$j];
            }
        }
    }

    /**
     * Calculate the height of row
     *
     * @param array $row
     * @return integer maximum row per cell
     * @see \yii\console\widgets\Table::render()
     */
    protected function calculateRowHeight($row)
    {
        $rowsPerCell = array_map(function ($size, $columnWidth) {
            if (is_array($columnWidth)) {
                $rows = 0;
                foreach ($columnWidth as $width) {
                    $rows += ceil($width / ($size - 2));
                }
                return $rows;
            }
            return ceil($columnWidth / ($size - 2));
        }, $this->_columnWidths, array_map(function ($val) {
                if (is_array($val)) {
                    $encodings = array_fill(0, count($val), Yii::$app->charset);
                    return array_map('mb_strwidth', $val, $encodings);
                }
                return mb_strwidth($val, Yii::$app->charset);
            }, $row)
        );

        return max($rowsPerCell);
    }

    /**
     * Getting screen size
     *
     * @return int screen size
     */
    protected function getScreenSize()
    {
        if (!$this->_screenSize) {
            $this->_screenSize = Console::getScreenSize()[0];
        }
        return $this->_screenSize;
    }
}
