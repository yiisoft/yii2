<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\build\controllers;

use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\FileHelper;

/**
 * Check files for broken UTF8 and non-printable characters.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
class Utf8Controller extends Controller
{
    public $defaultAction = 'check-guide';

    /**
     * Check guide for non-printable characters that may break docs generation.
     *
     * @param string $directory the directory to check. If not specified, the default
     * guide directory will be checked.
     */
    public function actionCheckGuide($directory = null)
    {
        if ($directory === null) {
            $directory = \dirname(\dirname(__DIR__)) . '/docs';
        }
        if (is_file($directory)) {
            $files = [$directory];
        } else {
            $files = FileHelper::findFiles($directory, [
                'only' => ['*.md'],
            ]);
        }

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $chars = preg_split('//u', $content, null, PREG_SPLIT_NO_EMPTY);

            $line = 1;
            $pos = 0;
            foreach ($chars as $c) {
                $ord = $this->unicodeOrd($c);

                $pos++;
                if ($ord == 0x000A) {
                    $line++;
                    $pos = 0;
                }

                if ($ord === false) {
                    $this->found('BROKEN UTF8', $c, $line, $pos, $file);
                    continue;
                }

                // http://unicode-table.com/en/blocks/general-punctuation/
                if (0x2000 <= $ord && $ord <= 0x200F
                 || 0x2028 <= $ord && $ord <= 0x202E
                 || 0x205f <= $ord && $ord <= 0x206F
                    ) {
                    $this->found('UNSUPPORTED SPACE CHARACTER', $c, $line, $pos, $file);
                    continue;
                }
                if ($ord < 0x0020 && $ord != 0x000A && $ord != 0x0009 ||
                    0x0080 <= $ord && $ord < 0x009F) {
                    $this->found('CONTROL CHARARCTER', $c, $line, $pos, $file);
                    continue;
                }
//                if ($ord > 0x009F) {
//                    $this->found("NON ASCII CHARARCTER", $c, $line, $pos, $file);
//                    continue;
//                }
            }
        }
    }

    private $_foundFiles = [];

    private function found($what, $char, $line, $pos, $file)
    {
        if (!isset($this->_foundFiles[$file])) {
            $this->stdout("$file: \n", Console::BOLD);
            $this->_foundFiles[$file] = $file;
        }

        $hexcode = dechex($this->unicodeOrd($char));
        $hexcode = str_repeat('0', max(4 - \strlen($hexcode), 0)) . $hexcode;

        $this->stdout("  at $line:$pos FOUND $what: 0x$hexcode '$char' http://unicode-table.com/en/$hexcode/\n");
    }

    /**
     * Equivalent for ord() just for unicode.
     *
     * http://stackoverflow.com/a/10333324/1106908
     *
     * @param $c
     * @return bool|int
     */
    private function unicodeOrd($c)
    {
        $h = \ord($c[0]);
        if ($h <= 0x7F) {
            return $h;
        } elseif ($h < 0xC2) {
            return false;
        } elseif ($h <= 0xDF) {
            return ($h & 0x1F) << 6 | (\ord($c[1]) & 0x3F);
        } elseif ($h <= 0xEF) {
            return ($h & 0x0F) << 12 | (\ord($c[1]) & 0x3F) << 6
                                     | (\ord($c[2]) & 0x3F);
        } elseif ($h <= 0xF4) {
            return ($h & 0x0F) << 18 | (\ord($c[1]) & 0x3F) << 12
                                     | (\ord($c[2]) & 0x3F) << 6
                                     | (\ord($c[3]) & 0x3F);
        }

        return false;
    }
}
