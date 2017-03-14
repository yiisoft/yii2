<?php

namespace yii\console\widgets;

use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Class Table
 *
 * @author Daniel Gomez Pan <pana_1990@hotmail.com>
 * @since 2.0.12
 */
class Table extends Object
{
    private $_headers = [];
    private $_rows = [];
    private $_chars = [
        'top' => '═', 'top-mid' => '╤', 'top-left' => '╔',
        'top-right' => '╗', 'bottom' => '═', 'bottom-mid' => '╧',
        'bottom-left' => '╚', 'bottom-right' => '╝', 'left' => '║',
        'left-mid' => '╟', 'mid' => '─', 'mid-mid' => '┼',
        'right' => '║', 'right-mid' => '╢', 'middle' => '│',
    ];
    private $_columnWidths = [];

    public function setHeaders(array $headers)
    {
        $this->_headers = $headers;
        return $this;
    }

    public function setRows(array $rows)
    {
        $this->_rows = $rows;
        return $this;
    }

    public function setChars(array $chars)
    {
        $this->_chars = $chars;
        return $this;
    }

    /**
     * Renders table to output.
     *
     * @return string the generated table
     */
    public function render()
    {
        $this->calculateSizeRows();
        $buffer = $this->renderSeparator(
            $this->_chars['top-left'],
            $this->_chars['top-mid'],
            $this->_chars['top'],
            $this->_chars['top-right']
        );
        $buffer .= $this->renderRows($this->_headers,
            $this->_chars['left'],
            $this->_chars['middle'],
            $this->_chars['right']
        );

        foreach ($this->_rows as $row) {
            $buffer .= $this->renderSeparator(
                $this->_chars['left-mid'],
                $this->_chars['mid-mid'],
                $this->_chars['mid'],
                $this->_chars['right-mid']
            );
            $buffer .= $this->renderRows($row,
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

    protected function renderRows(array $row, $spanLeft, $spanMiddle, $spanRight)
    {
        $size = $this->_columnWidths;
        $rowsPerCell = array_map(function ($size, $columnWidth) {
            return ceil($columnWidth / ($size - 2));
        }, $size, array_map('mb_strwidth', $row));

        $buffer = '';

        for ($i = 0; $i < max($rowsPerCell); $i++) {
            $buffer .= $spanLeft . ' ';

            foreach ($row as $index => $cell) {
                if ($index != 0) {
                    $buffer .= $spanMiddle . ' ';
                }

                $chunk = mb_substr($cell, ($size[$index] * $i) - ($i * 2) , $size[$index] - 2 , Yii::$app->charset);
                $repeat = $size[$index]  - mb_strwidth($chunk, Yii::$app->charset) - 1;

                $buffer .= $chunk;
                if ($repeat >= 0) {
                    $buffer .= str_repeat(' ', $repeat);
                }
            }
            $buffer .= "$spanRight\n";
        }

        return $buffer;
    }

    protected function renderSeparator($spanLeft, $spanMid, $spanMidMid, $spanRight)
    {
        $separator = $spanLeft;
        foreach ($this->_columnWidths as $index => $rowSize) {
            if ($index != 0) {
                $separator .= $spanMid;
            }
            $separator .= str_repeat($spanMidMid, $rowSize);
        }
        $separator .= $spanRight . "\n";
        return $separator;
    }

    protected function calculateSizeRows()
    {
        $this->_columnWidths = $columns = [];
        $totalWidth = 0;
        $screenWidth = Console::getScreenSize()[0] - 3;

        for ($i = 0, $size = count($this->_headers); $i < $size; $i++) {
            $columns[] = ArrayHelper::getColumn($this->_rows, $i);
            array_push($columns[$i], $this->_headers[$i]);
        }
        $encoding = array_fill(0, count($columns), Yii::$app->charset);
        foreach ($columns as $column) {
            $columnWidth = max(array_map('mb_strwidth', $column, $encoding)) + 2;
            $this->_columnWidths[] = $columnWidth;
            $totalWidth += $columnWidth;
        }

        $relativeWidth = $screenWidth / $totalWidth;

        if ($totalWidth > $screenWidth) {
            foreach ($this->_columnWidths as $j => $width) {
                $this->_columnWidths[$j] = intval($width * $relativeWidth);
                if ($j == count($this->_columnWidths)) {
                    $this->_columnWidths = $totalWidth;
                }
                $totalWidth -= $this->_columnWidths[$j];
            }
        }

        $this->_columnWidths;
    }
}
