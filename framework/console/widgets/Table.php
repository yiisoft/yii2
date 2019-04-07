<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Table 类在控制台中显示一个表。
 *
 * 例如，
 *
 * ```php
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
 * 或者
 *
 * ```php
 * echo Table::widget([
 *     'headers' => ['test1', 'test2', 'test3'],
 *     'rows' => [
 *         ['col1', 'col2', 'col3'],
 *         ['col1', 'col2', ['col3-0', 'col3-1', 'col3-2']],
 *     ],
 * ]);
 *
 * @property string $listPrefix 列表前缀。此属性是只写的。
 * @property int $screenWidth 屏幕宽度。此属性是只写的。
 *
 * @author Daniel Gomez Pan <pana_1990@hotmail.com>
 * @since 2.0.13
 */
class Table extends Widget
{
    const DEFAULT_CONSOLE_SCREEN_WIDTH = 120;
    const CONSOLE_SCROLLBAR_OFFSET = 3;
    const CHAR_TOP = 'top';
    const CHAR_TOP_MID = 'top-mid';
    const CHAR_TOP_LEFT = 'top-left';
    const CHAR_TOP_RIGHT = 'top-right';
    const CHAR_BOTTOM = 'bottom';
    const CHAR_BOTTOM_MID = 'bottom-mid';
    const CHAR_BOTTOM_LEFT = 'bottom-left';
    const CHAR_BOTTOM_RIGHT = 'bottom-right';
    const CHAR_LEFT = 'left';
    const CHAR_LEFT_MID = 'left-mid';
    const CHAR_MID = 'mid';
    const CHAR_MID_MID = 'mid-mid';
    const CHAR_RIGHT = 'right';
    const CHAR_RIGHT_MID = 'right-mid';
    const CHAR_MIDDLE = 'middle';

    /**
     * @var array 表头
     */
    private $_headers = [];
    /**
     * @var array 表行
     */
    private $_rows = [];
    /**
     * @var array 表字符
     */
    private $_chars = [
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
     * @var array 表的列宽
     */
    private $_columnWidths = [];
    /**
     * @var int 屏幕宽度
     */
    private $_screenWidth;
    /**
     * @var string 列表前缀
     */
    private $_listPrefix = '• ';


    /**
     * 设置表头。
     *
     * @param array $headers 表头
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->_headers = array_values($headers);
        return $this;
    }

    /**
     * 设置表格行。
     *
     * @param array $rows 表行
     * @return $this
     */
    public function setRows(array $rows)
    {
        $this->_rows = array_map('array_values', $rows);
        return $this;
    }

    /**
     * 设置表格字符。
     *
     * @param array $chars 表格字符
     * @return $this
     */
    public function setChars(array $chars)
    {
        $this->_chars = $chars;
        return $this;
    }

    /**
     * 设置屏幕宽度。
     *
     * @param int $width 屏幕宽度
     * @return $this
     */
    public function setScreenWidth($width)
    {
        $this->_screenWidth = $width;
        return $this;
    }

    /**
     * 设置列表前缀。
     *
     * @param string $listPrefix 前缀列表
     * @return $this
     */
    public function setListPrefix($listPrefix)
    {
        $this->_listPrefix = $listPrefix;
        return $this;
    }

    /**
     * @return string 渲染表
     */
    public function run()
    {
        $this->calculateRowsSize();
        $headerCount = count($this->_headers);

        $buffer = $this->renderSeparator(
            $this->_chars[self::CHAR_TOP_LEFT],
            $this->_chars[self::CHAR_TOP_MID],
            $this->_chars[self::CHAR_TOP],
            $this->_chars[self::CHAR_TOP_RIGHT]
        );
        // Header
        if ($headerCount > 0) {
            $buffer .= $this->renderRow($this->_headers,
                $this->_chars[self::CHAR_LEFT],
                $this->_chars[self::CHAR_MIDDLE],
                $this->_chars[self::CHAR_RIGHT]
            );
        }

        // Content
        foreach ($this->_rows as $i => $row) {
            if ($i > 0 || $headerCount > 0) {
                $buffer .= $this->renderSeparator(
                    $this->_chars[self::CHAR_LEFT_MID],
                    $this->_chars[self::CHAR_MID_MID],
                    $this->_chars[self::CHAR_MID],
                    $this->_chars[self::CHAR_RIGHT_MID]
                );
            }
            $buffer .= $this->renderRow($row,
                $this->_chars[self::CHAR_LEFT],
                $this->_chars[self::CHAR_MIDDLE],
                $this->_chars[self::CHAR_RIGHT]);
        }

        $buffer .= $this->renderSeparator(
            $this->_chars[self::CHAR_BOTTOM_LEFT],
            $this->_chars[self::CHAR_BOTTOM_MID],
            $this->_chars[self::CHAR_BOTTOM],
            $this->_chars[self::CHAR_BOTTOM_RIGHT]
        );

        return $buffer;
    }

    /**
     * 将一行数据呈现为字符串。
     *
     * @param array $row 数据行
     * @param string $spanLeft 左边框的字符
     * @param string $spanMiddle 中间边框的字符
     * @param string $spanRight 右边框的字符
     * @return string
     * @see \yii\console\widgets\Table::render()
     */
    protected function renderRow(array $row, $spanLeft, $spanMiddle, $spanRight)
    {
        $size = $this->_columnWidths;

        $buffer = '';
        $arrayPointer = [];
        $finalChunk = [];
        for ($i = 0, ($max = $this->calculateRowHeight($row)) ?: $max = 1; $i < $max; $i++) {
            $buffer .= $spanLeft . ' ';
            foreach ($size as $index => $cellSize) {
                $cell = isset($row[$index]) ? $row[$index] : null;
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
                    $chunk = mb_substr($cell[$arrayPointer[$index]], $start, $cellSize - 4, Yii::$app->charset);
                    $finalChunk[$index] .= $chunk;
                    if (isset($cell[$arrayPointer[$index] + 1]) && $finalChunk[$index] === $cell[$arrayPointer[$index]]) {
                        $arrayPointer[$index]++;
                        $finalChunk[$index] = '';
                    }
                } else {
                    $chunk = mb_substr($cell, ($cellSize * $i) - ($i * 2), $cellSize - 2, Yii::$app->charset);
                }
                $chunk = $prefix . $chunk;
                $repeat = $cellSize - mb_strwidth($chunk, Yii::$app->charset) - 1;
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
     * 渲染分隔符。
     *
     * @param string $spanLeft 左边框的字符
     * @param string $spanMid 中间边框的字符
     * @param string $spanMidMid middle-middle 边框的字符
     * @param string $spanRight 右边框的字符
     * @return string 生成的分隔行
     * @see \yii\console\widgets\Table::render()
     */
    protected function renderSeparator($spanLeft, $spanMid, $spanMidMid, $spanRight)
    {
        $separator = $spanLeft;
        foreach ($this->_columnWidths as $index => $rowSize) {
            if ($index !== 0) {
                $separator .= $spanMid;
            }
            $separator .= str_repeat($spanMidMid, $rowSize);
        }
        $separator .= $spanRight . "\n";
        return $separator;
    }

    /**
     * 计算要在控制台中绘制列锚点的行的大小。
     *
     * @see \yii\console\widgets\Table::render()
     */
    protected function calculateRowsSize()
    {
        $this->_columnWidths = $columns = [];
        $totalWidth = 0;
        $screenWidth = $this->getScreenWidth() - self::CONSOLE_SCROLLBAR_OFFSET;

        $headerCount = count($this->_headers);
        if (empty($this->_rows)) {
            $rowColCount = 0;
        } else {
            $rowColCount = max(array_map('count', $this->_rows));
        }
        $count = max($headerCount, $rowColCount);
        for ($i = 0; $i < $count; $i++) {
            $columns[] = ArrayHelper::getColumn($this->_rows, $i);
            if ($i < $headerCount) {
                $columns[$i][] = $this->_headers[$i];
            }
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
                $this->_columnWidths[$j] = (int) ($width * $relativeWidth);
                if ($j === count($this->_columnWidths)) {
                    $this->_columnWidths = $totalWidth;
                }
                $totalWidth -= $this->_columnWidths[$j];
            }
        }
    }

    /**
     * 计算行的高度。
     *
     * @param array $row
     * @return int 每单元最大行数
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
     * 获取屏幕宽度。
     * 如果无法确定屏幕宽度，将设置默认值 `123`。
     *
     * @return int 屏幕宽度
     */
    protected function getScreenWidth()
    {
        if (!$this->_screenWidth) {
            $size = Console::getScreenSize();
            $this->_screenWidth = isset($size[0])
                ? $size[0]
                : self::DEFAULT_CONSOLE_SCREEN_WIDTH + self::CONSOLE_SCROLLBAR_OFFSET;
        }
        return $this->_screenWidth;
    }
}
