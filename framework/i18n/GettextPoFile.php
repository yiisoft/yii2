<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\i18n;

use Yii;
use yii\base\Exception;

/**
 * GettextPoFile represents a PO Gettext message file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class GettextPoFile extends GettextFile
{
    /**
     * Quoted PO string that stays on one physical line, including the surrounding quotes.
     * Excluding CR/LF prevents a legacy entry that ends with a single backslash from consuming
     * the next entry's closing quote.
     */
    private const QUOTED_STRING_PATTERN = '"(?:[^"\\\\\r\n]|\\\\[^\r\n])*+"';

    /**
     * Loads messages from a PO file.
     * @param string $filePath file path
     * @param string $context message context
     * @return array message translations. Array keys are source messages and array values are translated messages:
     * source message => translated message.
     * @throws Exception if a regular expression fails
     */
    public function load($filePath, $context)
    {
        $quoted = self::QUOTED_STRING_PATTERN;
        $pattern = '/(msgctxt\s+((?:' . $quoted . '\s*)++))?\s*msgid\s*((?:' . $quoted . '\s*)++)\s*msgstr\s*((?:' . $quoted . '\s*)++)/';
        $content = file_get_contents($filePath);
        $matches = [];
        $matchCount = preg_match_all($pattern, $content, $matches);
        if ($matchCount === false) {
            throw new Exception('Unable to parse PO file "' . $filePath . '" (PCRE error ' . preg_last_error() . ').');
        }

        $messages = [];
        for ($i = 0; $i < $matchCount; ++$i) {
            if ($this->decode($matches[2][$i]) === $context) {
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
                $content .= 'msgctxt "' . $this->encode(substr($id, 0, $separatorPosition)) . "\"\n";
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
            ['\\', '"', "\n", "\t", "\r"],
            ['\\\\', '\\"', '\\n', '\\t', '\\r'],
            $string
        );
    }

    /**
     * Decodes special characters in a message.
     * @param string $string message to be decoded
     * @return string the decoded message
     * @throws Exception if a regular expression fails
     */
    protected function decode($string)
    {
        if (preg_match_all('/' . self::QUOTED_STRING_PATTERN . '/', $string, $matches) === false) {
            throw new Exception('Unable to decode PO string (PCRE error ' . preg_last_error() . ').');
        }

        $decoded = '';
        foreach ($matches[0] as $fragment) {
            $decoded .= strtr(substr($fragment, 1, -1), [
                '\n' => "\n",
                '\r' => "\r",
                '\t' => "\t",
                '\"' => '"',
                '\\\\' => '\\',
            ]);
        }

        return $decoded;
    }
}
