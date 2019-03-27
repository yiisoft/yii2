<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use Yii;

/**
 * GettextPoFile 表示 PO Gettext 消息文件。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class GettextPoFile extends GettextFile
{
    /**
     * 从 PO 文件加载消息。
     * @param string $filePath 文件路径
     * @param string $context 消息上下文
     * @return array 消息翻译。数组键是源消息，数组值是已翻译的消息：
     * 源消息 => 已翻译的消息。
     */
    public function load($filePath, $context)
    {
        $pattern = '/(msgctxt\s+"(.*?(?<!\\\\))")?\s+' // context
            . 'msgid\s+((?:".*(?<!\\\\)"\s*)+)\s+' // message ID, i.e. original string
            . 'msgstr\s+((?:".*(?<!\\\\)"\s*)+)/'; // translated string
        $content = file_get_contents($filePath);
        $matches = [];
        $matchCount = preg_match_all($pattern, $content, $matches);

        $messages = [];
        for ($i = 0; $i < $matchCount; ++$i) {
            if ($matches[2][$i] === $context) {
                $id = $this->decode($matches[3][$i]);
                $message = $this->decode($matches[4][$i]);
                $messages[$id] = $message;
            }
        }

        return $messages;
    }

    /**
     * 将消息保存到 PO 文件。
     * @param string $filePath 文件路径
     * @param array $messages 消息翻译。 数组键是源消息，数组值是已翻译的消息：源消息 => 已翻译的消息。
     * 请注意，如果消息具有上下文，
     * 则消息 ID 必须以 chr(4) 作为分隔符的上下文前缀。
     */
    public function save($filePath, $messages)
    {
        $language = str_replace('-', '_', basename(dirname($filePath)));
        $headers = [
            'msgid ""',
            'msgstr ""',
            '"Project-Id-Version: \n"',
            '"POT-Creation-Date: \n"',
            '"PO-Revision-Date: \n"',
            '"Last-Translator: \n"',
            '"Language-Team: \n"',
            '"Language: ' . $language . '\n"',
            '"MIME-Version: 1.0\n"',
            '"Content-Type: text/plain; charset=' . Yii::$app->charset . '\n"',
            '"Content-Transfer-Encoding: 8bit\n"',
        ];
        $content = implode("\n", $headers) . "\n\n";
        foreach ($messages as $id => $message) {
            $separatorPosition = strpos($id, chr(4));
            if ($separatorPosition !== false) {
                $content .= 'msgctxt "' . substr($id, 0, $separatorPosition) . "\"\n";
                $id = substr($id, $separatorPosition + 1);
            }
            $content .= 'msgid "' . $this->encode($id) . "\"\n";
            $content .= 'msgstr "' . $this->encode($message) . "\"\n\n";
        }
        file_put_contents($filePath, $content);
    }

    /**
     * 在消息特殊字符进行编码。
     * @param string $string 要编码的消息
     * @return string 编码的消息
     */
    protected function encode($string)
    {
        return str_replace(
            ['"', "\n", "\t", "\r"],
            ['\\"', '\\n', '\\t', '\\r'],
            $string
        );
    }

    /**
     * 解码消息中的特殊字符。
     * @param string $string 要解码的消息
     * @return string 解码的消息
     */
    protected function decode($string)
    {
        $string = preg_replace(
            ['/"\s+"/', '/\\\\n/', '/\\\\r/', '/\\\\t/', '/\\\\"/'],
            ['', "\n", "\r", "\t", '"'],
            $string
        );

        return substr(rtrim($string), 1, -1);
    }
}
