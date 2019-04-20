<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use yii\base\Exception;

/**
 * GettextMoFile 表示 MO Gettext 消息文件。
 *
 * 这个类是通过在 PEAR 中调整 Michael 的 Gettext_MO 类来编写的。
 * 请参阅以下许可条款。
 *
 * Copyright (c) 2004-2005, Michael Wallner <mike@iworks.at>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class GettextMoFile extends GettextFile
{
    /**
     * @var bool 在读写整数时是否使用 big-endian。
     */
    public $useBigEndian = false;


    /**
     * 从 MO 文件加载消息。
     * @param string $filePath 文件路径
     * @param string $context 消息上下文
     * @return array 消息翻译。 数组键是源消息，数组值是已翻译的消息：
     * 源消息 => 已翻译的消息。
     * @throws Exception 如果无法读取 MO 文件
     */
    public function load($filePath, $context)
    {
        if (false === ($fileHandle = @fopen($filePath, 'rb'))) {
            throw new Exception('Unable to read file "' . $filePath . '".');
        }
        if (false === @flock($fileHandle, LOCK_SH)) {
            throw new Exception('Unable to lock file "' . $filePath . '" for reading.');
        }

        // magic
        $array = unpack('c', $this->readBytes($fileHandle, 4));
        $magic = current($array);
        if ($magic == -34) {
            $this->useBigEndian = false;
        } elseif ($magic == -107) {
            $this->useBigEndian = true;
        } else {
            throw new Exception('Invalid MO file: ' . $filePath . ' (magic: ' . $magic . ').');
        }

        // revision
        $revision = $this->readInteger($fileHandle);
        if ($revision !== 0) {
            throw new Exception('Invalid MO file revision: ' . $revision . '.');
        }

        $count = $this->readInteger($fileHandle);
        $sourceOffset = $this->readInteger($fileHandle);
        $targetOffset = $this->readInteger($fileHandle);

        $sourceLengths = [];
        $sourceOffsets = [];
        fseek($fileHandle, $sourceOffset);
        for ($i = 0; $i < $count; ++$i) {
            $sourceLengths[] = $this->readInteger($fileHandle);
            $sourceOffsets[] = $this->readInteger($fileHandle);
        }

        $targetLengths = [];
        $targetOffsets = [];
        fseek($fileHandle, $targetOffset);
        for ($i = 0; $i < $count; ++$i) {
            $targetLengths[] = $this->readInteger($fileHandle);
            $targetOffsets[] = $this->readInteger($fileHandle);
        }

        $messages = [];
        for ($i = 0; $i < $count; ++$i) {
            $id = $this->readString($fileHandle, $sourceLengths[$i], $sourceOffsets[$i]);
            $separatorPosition = strpos($id, chr(4));


            if ((!$context && $separatorPosition === false) || ($context && $separatorPosition !== false && strncmp($id, $context, $separatorPosition) === 0)) {
                if ($separatorPosition !== false) {
                    $id = substr($id, $separatorPosition + 1);
                }

                $message = $this->readString($fileHandle, $targetLengths[$i], $targetOffsets[$i]);
                $messages[$id] = $message;
            }
        }

        @flock($fileHandle, LOCK_UN);
        @fclose($fileHandle);

        return $messages;
    }

    /**
     * 将消息保存到 MO 文件。
     * @param string $filePath 文件路径
     * @param array $messages 消息翻译。 数组键是源消息，数组值是已翻译的消息：源消息 => 已翻译的消息。
     * 请注意，如果消息具有上下文，
     * 则消息 ID 必须以 chr(4) 作为分隔符的上下文前缀。
     * @throws Exception 如果无法保存 MO 文件
     */
    public function save($filePath, $messages)
    {
        if (false === ($fileHandle = @fopen($filePath, 'wb'))) {
            throw new Exception('Unable to write file "' . $filePath . '".');
        }
        if (false === @flock($fileHandle, LOCK_EX)) {
            throw new Exception('Unable to lock file "' . $filePath . '" for reading.');
        }

        // magic
        if ($this->useBigEndian) {
            $this->writeBytes($fileHandle, pack('c*', 0x95, 0x04, 0x12, 0xde)); // -107
        } else {
            $this->writeBytes($fileHandle, pack('c*', 0xde, 0x12, 0x04, 0x95)); // -34
        }

        // revision
        $this->writeInteger($fileHandle, 0);

        // message count
        $messageCount = count($messages);
        $this->writeInteger($fileHandle, $messageCount);

        // offset of source message table
        $offset = 28;
        $this->writeInteger($fileHandle, $offset);
        $offset += $messageCount * 8;
        $this->writeInteger($fileHandle, $offset);

        // hashtable size, omitted
        $this->writeInteger($fileHandle, 0);
        $offset += $messageCount * 8;
        $this->writeInteger($fileHandle, $offset);

        // length and offsets for source messages
        foreach (array_keys($messages) as $id) {
            $length = strlen($id);
            $this->writeInteger($fileHandle, $length);
            $this->writeInteger($fileHandle, $offset);
            $offset += $length + 1;
        }

        // length and offsets for target messages
        foreach ($messages as $message) {
            $length = strlen($message);
            $this->writeInteger($fileHandle, $length);
            $this->writeInteger($fileHandle, $offset);
            $offset += $length + 1;
        }

        // source messages
        foreach (array_keys($messages) as $id) {
            $this->writeString($fileHandle, $id);
        }

        // target messages
        foreach ($messages as $message) {
            $this->writeString($fileHandle, $message);
        }

        @flock($fileHandle, LOCK_UN);
        @fclose($fileHandle);
    }

    /**
     * 读一个或多个字节。
     * @param resource $fileHandle 要读入的文件句柄
     * @param int $byteCount 要读入的字节数目
     * @return string 字节
     */
    protected function readBytes($fileHandle, $byteCount = 1)
    {
        if ($byteCount > 0) {
            return fread($fileHandle, $byteCount);
        }

        return null;
    }

    /**
     * 写入字节。
     * @param resource $fileHandle 要写入的文件句柄
     * @param string $bytes 要写入的字节
     * @return int 写入的字节数目
     */
    protected function writeBytes($fileHandle, $bytes)
    {
        return fwrite($fileHandle, $bytes);
    }

    /**
     * 读一个 4 字节的整数。
     * @param resource $fileHandle 要读入的文件句柄
     * @return int 结果
     */
    protected function readInteger($fileHandle)
    {
        $array = unpack($this->useBigEndian ? 'N' : 'V', $this->readBytes($fileHandle, 4));

        return current($array);
    }

    /**
     * 写一个 4 字节的整数。
     * @param resource $fileHandle 要写入的文件句柄
     * @param int $integer 要写入的整数
     * @return int 写入的字节数目
     */
    protected function writeInteger($fileHandle, $integer)
    {
        return $this->writeBytes($fileHandle, pack($this->useBigEndian ? 'N' : 'V', (int) $integer));
    }

    /**
     * 读入一个字符串
     * @param resource $fileHandle 文件句柄
     * @param int $length 字符串的长度
     * @param int $offset 文件中字符串的偏移量。 如果为 null，则从当前位置读取。
     * @return string 结果
     */
    protected function readString($fileHandle, $length, $offset = null)
    {
        if ($offset !== null) {
            fseek($fileHandle, $offset);
        }

        return $this->readBytes($fileHandle, $length);
    }

    /**
     * 写一个字符串。
     * @param resource $fileHandle 要写入的文件句柄
     * @param string $string 要写入的字符串
     * @return int 写入的字节数目
     */
    protected function writeString($fileHandle, $string)
    {
        return $this->writeBytes($fileHandle, $string . "\0");
    }
}
