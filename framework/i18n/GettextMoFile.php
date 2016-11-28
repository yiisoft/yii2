<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use yii\base\Exception;

/**
 * GettextMoFile represents an MO Gettext message file.
 *
 * This class is written by adapting Michael's Gettext_MO class in PEAR.
 * Please refer to the following license terms.
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
     * @var bool whether to use big-endian when reading and writing an integer.
     */
    public $useBigEndian = false;


    /**
     * Loads messages from an MO file.
     * @param string $filePath file path
     * @param string $context message context
     * @return array message translations. Array keys are source messages and array values are translated messages:
     * source message => translated message.
     * @throws Exception if unable to read the MO file
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
                    $id = substr($id, $separatorPosition+1);
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
     * Saves messages to an MO file.
     * @param string $filePath file path
     * @param array $messages message translations. Array keys are source messages and array values are
     * translated messages: source message => translated message. Note if the message has a context,
     * the message ID must be prefixed with the context with chr(4) as the separator.
     * @throws Exception if unable to save the MO file
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
     * Reads one or several bytes.
     * @param resource $fileHandle to read from
     * @param int $byteCount to be read
     * @return string bytes
     */
    protected function readBytes($fileHandle, $byteCount = 1)
    {
        if ($byteCount > 0) {
            return fread($fileHandle, $byteCount);
        } else {
            return null;
        }
    }

    /**
     * Write bytes.
     * @param resource $fileHandle to write to
     * @param string $bytes to be written
     * @return int how many bytes are written
     */
    protected function writeBytes($fileHandle, $bytes)
    {
        return fwrite($fileHandle, $bytes);
    }

    /**
     * Reads a 4-byte integer.
     * @param resource $fileHandle to read from
     * @return int the result
     */
    protected function readInteger($fileHandle)
    {
        $array = unpack($this->useBigEndian ? 'N' : 'V', $this->readBytes($fileHandle, 4));

        return current($array);
    }

    /**
     * Writes a 4-byte integer.
     * @param resource $fileHandle to write to
     * @param int $integer to be written
     * @return int how many bytes are written
     */
    protected function writeInteger($fileHandle, $integer)
    {
        return $this->writeBytes($fileHandle, pack($this->useBigEndian ? 'N' : 'V', (int) $integer));
    }

    /**
     * Reads a string.
     * @param resource $fileHandle file handle
     * @param int $length of the string
     * @param int $offset of the string in the file. If null, it reads from the current position.
     * @return string the result
     */
    protected function readString($fileHandle, $length, $offset = null)
    {
        if ($offset !== null) {
            fseek($fileHandle, $offset);
        }

        return $this->readBytes($fileHandle, $length);
    }

    /**
     * Writes a string.
     * @param resource $fileHandle to write to
     * @param string $string to be written
     * @return int how many bytes are written
     */
    protected function writeString($fileHandle, $string)
    {
        return $this->writeBytes($fileHandle, $string. "\0");
    }
}
