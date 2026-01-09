<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\console\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Table class displays a table in console.
 *
 * For example,
 *
 * ```
 * $table = new Table();
 *
 * echo $table
 *     ->setHeaders(['test1', 'test2', 'test3'])
 *     ->setRows([
 *         ['col1', 'col2', 'col3'],
 *         ['col1', 'col2', ['col3-0', 'col3-1', 'col3-2']],
 *     ])
 *     ->run();
 * ```
 *
 * or
 *
 * ```
 * echo Table::widget([
 *     'headers' => ['test1', 'test2', 'test3'],
 *     'rows' => [
 *         ['col1', 'col2', 'col3'],
 *         ['col1', 'col2', ['col3-0', 'col3-1', 'col3-2']],
 *     ],
 * ]);
 *
 * @property-write array $chars Table chars.
 * @property-write array $headers Table headers.
 * @property-write string $listPrefix List prefix.
 * @property-write array $rows Table rows.
 * @property-write int $screenWidth Screen width.
 *
 * @author Daniel Gomez Pan <pana_1990@hotmail.com>
 * @since 2.0.13
 */
class Table extends Widget
{
    public const DEFAULT_CONSOLE_SCREEN_WIDTH = 120;
    public const CONSOLE_SCROLLBAR_OFFSET = 3;
    public const CHAR_TOP = 'top';
    public const CHAR_TOP_MID = 'top-mid';
    public const CHAR_TOP_LEFT = 'top-left';
    public const CHAR_TOP_RIGHT = 'top-right';
    public const CHAR_BOTTOM = 'bottom';
    public const CHAR_BOTTOM_MID = 'bottom-mid';
    public const CHAR_BOTTOM_LEFT = 'bottom-left';
    public const CHAR_BOTTOM_RIGHT = 'bottom-right';
    public const CHAR_LEFT = 'left';
    public const CHAR_LEFT_MID = 'left-mid';
    public const CHAR_MID = 'mid';
    public const CHAR_MID_MID = 'mid-mid';
    public const CHAR_RIGHT = 'right';
    public const CHAR_RIGHT_MID = 'right-mid';
    public const CHAR_MIDDLE = 'middle';
    /**
     * @var array table headers
     * @since 2.0.19
     */
    protected $headers = [];
    /**
     * @var array table rows
     * @since 2.0.19
     */
    protected $rows = [];
    /**
     * @var array table chars
     * @since 2.0.19
     */
    protected $chars = [
        self::CHAR_TOP => '═',
        self::CHAR_TOP_MID => '╤',
        self::CHAR_TOP_LEFT => '╔',
        self::CHAR_TOP_RIGHT => '╗',
        self::CHAR_BOTTOM => '═',
        self::CHAR_BOTTOM_MID => '╧',
        self::CHAR_BOTTOM_LEFT => '╚',
        self::CHAR_BOTTOM_RIGHT => '╝',
        self::CHAR_LEFT => '║',
        self::CHAR_LEFT_MID => '╟',
        self::CHAR_MID => '─',
        self::CHAR_MID_MID => '┼',
        self::CHAR_RIGHT => '║',
        self::CHAR_RIGHT_MID => '╢',
        self::CHAR_MIDDLE => '│',
    ];
    /**
     * @var array table column widths
     * @since 2.0.19
     */
    protected $columnWidths = [];
    /**
     * @var int screen width
     * @since 2.0.19
     */
    protected $screenWidth;
    /**
     * @var string list prefix
     * @since 2.0.19
     */
    protected $listPrefix = '• ';


    /**
     * Set table headers.
     *
     * @param array $headers table headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->headers = array_values($headers);
        return $this;
    }

    /**
     * Set table rows.
     *
     * @param array $rows table rows
     * @return $this
     */
    public function setRows(array $rows)
    {
        $this->rows = array_map(function ($row) {
            return array_map(function ($value) {
                return empty($value) && !is_numeric($value)
                    ? ' '
                    :  (is_array($value)
                        ? array_values($value)
                        : $value);
            }, array_values($row));
        }, $rows);
        return $this;
    }

    /**
     * Set table chars.
     *
     * @param array $chars table chars
     * @return $this
     */
    public function setChars(array $chars)
    {
        $this->chars = $chars;
        return $this;
    }

    /**
     * Set screen width.
     *
     * @param int $width screen width
     * @return $this
     */
    public function setScreenWidth($width)
    {
        $this->screenWidth = $width;
        return $this;
    }

    /**
     * Set list prefix.
     *
     * @param string $listPrefix list prefix
     * @return $this
     */
    public function setListPrefix($listPrefix)
    {
        $this->listPrefix = $listPrefix;
        return $this;
    }

    /**
     * @return string the rendered table
     */
    public function run()
    {
        $this->calculateRowsSize();
        $headerCount = count($this->headers);

        $buffer = $this->renderSeparator(
            $this->chars[self::CHAR_TOP_LEFT],
            $this->chars[self::CHAR_TOP_MID],
            $this->chars[self::CHAR_TOP],
            $this->chars[self::CHAR_TOP_RIGHT]
        );
        // Header
        if ($headerCount > 0) {
            $buffer .= $this->renderRow(
                $this->headers,
                $this->chars[self::CHAR_LEFT],
                $this->chars[self::CHAR_MIDDLE],
                $this->chars[self::CHAR_RIGHT]
            );
        }

        // Content
        foreach ($this->rows as $i => $row) {
            if ($i > 0 || $headerCount > 0) {
                $buffer .= $this->renderSeparator(
                    $this->chars[self::CHAR_LEFT_MID],
                    $this->chars[self::CHAR_MID_MID],
                    $this->chars[self::CHAR_MID],
                    $this->chars[self::CHAR_RIGHT_MID]
                );
            }
            $buffer .= $this->renderRow(
                $row,
                $this->chars[self::CHAR_LEFT],
                $this->chars[self::CHAR_MIDDLE],
                $this->chars[self::CHAR_RIGHT]
            );
        }

        $buffer .= $this->renderSeparator(
            $this->chars[self::CHAR_BOTTOM_LEFT],
            $this->chars[self::CHAR_BOTTOM_MID],
            $this->chars[self::CHAR_BOTTOM],
            $this->chars[self::CHAR_BOTTOM_RIGHT]
        );

        return $buffer;
    }

    /**
     * Renders a row of data into a string.
     *
     * @param array $row row of data
     * @param string $spanLeft character for left border
     * @param string $spanMiddle character for middle border
     * @param string $spanRight character for right border
     * @return string
     * @see \yii\console\widgets\Table::render()
     */
    protected function renderRow(array $row, $spanLeft, $spanMiddle, $spanRight)
    {
        $size = $this->columnWidths;

        $buffer = '';
        $arrayPointer = [];
        $renderedChunkTexts = [];
        for ($i = 0, ($max = $this->calculateRowHeight($row)) ?: $max = 1; $i < $max; $i++) {
            $buffer .= $spanLeft . ' ';
            foreach ($size as $index => $cellSize) {
                $cell = isset($row[$index]) ? $row[$index] : null;
                $prefix = '';
                if ($index !== 0) {
                    $buffer .= $spanMiddle . ' ';
                }

                $arrayFromMultilineString = false;
                if (is_string($cell)) {
                    $cellLines = explode(PHP_EOL, $cell);
                    if (count($cellLines) > 1) {
                        $cell = $cellLines;
                        $arrayFromMultilineString = true;
                    }
                }

                if (is_array($cell)) {
                    if (empty($renderedChunkTexts[$index])) {
                        $renderedChunkTexts[$index] = '';
                        $start = 0;
                        $prefix = $arrayFromMultilineString ? '' : $this->listPrefix;
                        if (!isset($arrayPointer[$index])) {
                            $arrayPointer[$index] = 0;
                        }
                    } else {
                        $start = mb_strwidth($renderedChunkTexts[$index], Yii::$app->charset);
                    }
                    $chunk = Console::ansiColorizedSubstr(
                        $cell[$arrayPointer[$index]],
                        $start,
                        $cellSize - 2 - Console::ansiStrwidth($prefix)
                    );
                    $renderedChunkTexts[$index] .= Console::stripAnsiFormat($chunk);
                    $fullChunkText = Console::stripAnsiFormat($cell[$arrayPointer[$index]]);
                    if (isset($cell[$arrayPointer[$index] + 1]) && $renderedChunkTexts[$index] === $fullChunkText) {
                        $arrayPointer[$index]++;
                        $renderedChunkTexts[$index] = '';
                    }
                } else {
                    $chunk = Console::ansiColorizedSubstr($cell, ($cellSize * $i) - ($i * 2), $cellSize - 2);
                }
                $chunk = $prefix . $chunk;
                $repeat = $cellSize - Console::ansiStrwidth($chunk) - 1;
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
     * Renders separator.
     *
     * @param string $spanLeft character for left border
     * @param string $spanMid character for middle border
     * @param string $spanMidMid character for middle-middle border
     * @param string $spanRight character for right border
     * @return string the generated separator row
     * @see \yii\console\widgets\Table::render()
     */
    protected function renderSeparator($spanLeft, $spanMid, $spanMidMid, $spanRight)
    {
        $separator = $spanLeft;
        foreach ($this->columnWidths as $index => $rowSize) {
            if ($index !== 0) {
                $separator .= $spanMid;
            }
            $separator .= str_repeat($spanMidMid, $rowSize);
        }
        $separator .= $spanRight . "\n";
        return $separator;
    }

    /**
     * Calculate the size of rows to draw anchor of columns in console.
     *
     * @see \yii\console\widgets\Table::render()
     */
    protected function calculateRowsSize()
    {
        $this->columnWidths = $columns = [];
        $totalWidth = 0;
        $screenWidth = $this->getScreenWidth() - self::CONSOLE_SCROLLBAR_OFFSET;

        $headerCount = count($this->headers);
        if (empty($this->rows)) {
            $rowColCount = 0;
        } else {
            $rowColCount = max(array_map('count', $this->rows));
        }
        $count = max($headerCount, $rowColCount);
        for ($i = 0; $i < $count; $i++) {
            $columns[] = ArrayHelper::getColumn($this->rows, $i);
            if ($i < $headerCount) {
                $columns[$i][] = $this->headers[$i];
            }
        }

        foreach ($columns as $column) {
            $columnWidth = max(array_map(function ($val) {
                if (is_array($val)) {
                    return max(array_map('yii\helpers\Console::ansiStrwidth', $val)) + Console::ansiStrwidth($this->listPrefix);
                }
                if (is_string($val)) {
                    return max(array_map('yii\helpers\Console::ansiStrwidth', explode(PHP_EOL, $val)));
                }
                return Console::ansiStrwidth($val);
            }, $column)) + 2;
            $this->columnWidths[] = $columnWidth;
            $totalWidth += $columnWidth;
        }

        if ($totalWidth > $screenWidth) {
            $minWidth = 3;
            $fixWidths = [];
            $relativeWidth = $screenWidth / $totalWidth;
            foreach ($this->columnWidths as $j => $width) {
                $scaledWidth = (int) ($width * $relativeWidth);
                if ($scaledWidth < $minWidth) {
                    $fixWidths[$j] = 3;
                }
            }

            $totalFixWidth = array_sum($fixWidths);
            $relativeWidth = ($screenWidth - $totalFixWidth) / ($totalWidth - $totalFixWidth);
            foreach ($this->columnWidths as $j => $width) {
                if (!array_key_exists($j, $fixWidths)) {
                    $this->columnWidths[$j] = (int) ($width * $relativeWidth);
                }
            }
        }
    }

    /**
     * Calculate the height of a row.
     *
     * @param array $row
     * @return int maximum row per cell
     * @see \yii\console\widgets\Table::render()
     */
    protected function calculateRowHeight($row)
    {
        $rowsPerCell = array_map(function ($size, $columnWidth) {
            if (is_array($columnWidth)) {
                $rows = 0;
                foreach ($columnWidth as $width) {
                    $rows +=  $size == 2 ? 0 : ceil($width / ($size - 2));
                }
                return $rows;
            }
            return $size == 2 || $columnWidth == 0 ? 0 : ceil($columnWidth / ($size - 2));
        }, $this->columnWidths, array_map(function ($val) {
            if (is_array($val)) {
                return array_map('yii\helpers\Console::ansiStrwidth', $val);
            }
            if (is_string($val)) {
                return array_map('yii\helpers\Console::ansiStrwidth', explode(PHP_EOL, $val));
            }
            return Console::ansiStrwidth($val);
        }, $row));
        return max($rowsPerCell);
    }

    /**
     * Getting screen width.
     * If it is not able to determine screen width, default value `123` will be set.
     *
     * @return int screen width
     */
    protected function getScreenWidth()
    {
        if (!$this->screenWidth) {
            $size = Console::getScreenSize();
            $this->screenWidth = isset($size[0])
                ? $size[0]
                : self::DEFAULT_CONSOLE_SCREEN_WIDTH + self::CONSOLE_SCROLLBAR_OFFSET;
        }
        return $this->screenWidth;
    }
}
