<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use Yii;

/**
 * GettextPoFile represents a PO Gettext message file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class GettextPoFile extends GettextFile
{
    /**
     * Loads messages from a PO file.
     * @param string $filePath file path
     * @param string $context message context
     * @return array message translations. Array keys are source messages and array values are translated messages:
     * source message => translated message.
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
     * Saves messages to a PO file.
     * @param string $filePath file path
     * @param array $messages message translations. Array keys are source messages and array values are
     * translated messages: source message => translated message. Note if the message has a context,
     * the message ID must be prefixed with the context with chr(4) as the separator.
     */
    public function save($filePath, $messages)
    {
        $content = '';
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
     * Encodes special characters in a message.
     * @param string $string message to be encoded
     * @return string the encoded message
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
     * Decodes special characters in a message.
     * @param string $string message to be decoded
     * @return string the decoded message
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
