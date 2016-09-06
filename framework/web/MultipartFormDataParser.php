<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\base\Object;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

/**
 * MultipartFormDataParser parses content encoded as 'multipart/form-data'.
 * This parser provides the fallback for the 'multipart/form-data' processing on non POST requests,
 * for example: the one with 'PUT' request method.
 *
 * In order to enable this parser you should configure [[\yii\web\Request::parsers]] in the following way:
 *
 * ```php
 * return [
 *     'components' => [
 *         'request' => [
 *             'parsers' => [
 *                 'multipart/form-data' => 'yii\web\MultipartFormDataParser'
 *             ],
 *         ],
 *         // ...
 *     ],
 *     // ...
 * ];
 * ```
 *
 * Method `parse()` of this parser automatically populates `$_FILES` with the files parsed from raw body.
 *
 * Note: although this parser fully emulates regular structure of the `$_FILES`, related temporary
 * files, which are available via 'tmp_name' key, will not be recognized by PHP as uploaded ones.
 * Thus functions like `is_uploaded_file()` and `move_uploaded_file()` will fail on them. This also
 * means [[UploadedFile::saveAs()]] will fail as well.
 *
 * @property integer $uploadFileMaxSize upload file max size in bytes.
 * @property integer $uploadFileMaxCount maximum upload files count.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.10
 */
class MultipartFormDataParser extends Object implements RequestParserInterface
{
    /**
     * @var integer upload file max size in bytes.
     */
    private $_uploadFileMaxSize;
    /**
     * @var integer maximum upload files count.
     */
    private $_uploadFileMaxCount;


    /**
     * @return integer upload file max size in bytes.
     */
    public function getUploadFileMaxSize()
    {
        if ($this->_uploadFileMaxSize === null) {
            $this->_uploadFileMaxSize = $this->getByteSize(ini_get('upload_max_filesize'));
        }
        return $this->_uploadFileMaxSize;
    }

    /**
     * @param integer $uploadFileMaxSize upload file max size in bytes.
     */
    public function setUploadFileMaxSize($uploadFileMaxSize)
    {
        $this->_uploadFileMaxSize = $uploadFileMaxSize;
    }

    /**
     * @return integer maximum upload files count.
     */
    public function getUploadFileMaxCount()
    {
        if ($this->_uploadFileMaxCount === null) {
            $this->_uploadFileMaxCount = ini_get('max_file_uploads');
        }
        return $this->_uploadFileMaxCount;
    }

    /**
     * @param integer $uploadFileMaxCount maximum upload files count.
     */
    public function setUploadFileMaxCount($uploadFileMaxCount)
    {
        $this->_uploadFileMaxCount = $uploadFileMaxCount;
    }

    /**
     * @inheritdoc
     */
    public function parse($rawBody, $contentType)
    {
        if (!empty($_POST) || !empty($_FILES)) {
            // normal POST request is parsed by PHP automatically
            return $_POST;
        }

        if (empty($rawBody)) {
            return [];
        }

        if (!preg_match('/boundary=(.*)$/is', $contentType, $matches)) {
            return [];
        }
        $boundary = $matches[1];

        $bodyParts = preg_split('/-+' . preg_quote($boundary) . '/s', $rawBody);
        array_pop($bodyParts); // last block always has no data

        $bodyParams = [];
        $filesCount = 0;
        foreach ($bodyParts as $bodyPart) {
            if (empty($bodyPart)) {
                continue;
            }
            list($headers, $value) = explode("\r\n", $bodyPart, 2);
            $headers = $this->parseHeaders($headers);
            
            if (!isset($headers['content-disposition']['name'])) {
                continue;
            }

            if (isset($headers['content-disposition']['filename'])) {
                // file upload:
                if ($filesCount >= $this->getUploadFileMaxCount()) {
                    continue;
                }

                $fileInfo = [
                    'name' => $headers['content-disposition']['filename'],
                    'type' => ArrayHelper::getValue($headers, 'content-type', 'application/octet-stream'),
                    'size' => StringHelper::byteLength($value),
                    'error' => UPLOAD_ERR_OK,
                    'tmp_name' => null,
                ];

                if ($fileInfo['size'] > $this->getUploadFileMaxSize()) {
                    $fileInfo['error'] = UPLOAD_ERR_INI_SIZE;
                } else {
                    $tmpResource = tmpfile();
                    if ($tmpResource === false) {
                        $fileInfo['error'] = UPLOAD_ERR_CANT_WRITE;
                    } else {
                        $tmpResourceMetaData = stream_get_meta_data($tmpResource);
                        $tmpFileName = $tmpResourceMetaData['uri'];
                        if (empty($tmpFileName)) {
                            $fileInfo['error'] = UPLOAD_ERR_CANT_WRITE;
                            @fclose($tmpResource);
                        } else {
                            fwrite($tmpResource, $value);
                            $fileInfo['tmp_name'] = $tmpFileName;
                            $fileInfo['tmp_resource'] = $tmpResource; // save file resource, otherwise it will be deleted
                        }
                    }
                }

                $this->addFile($_FILES, $headers['content-disposition']['name'], $fileInfo);

                $filesCount++;
            } else {
                // regular parameter:
                $this->addValue($bodyParams, $headers['content-disposition']['name'], $value);
            }
        }

        return $bodyParams;
    }

    /**
     * Parses content part headers.
     * @param string $headerContent headers source content
     * @return array parsed headers.
     */
    private function parseHeaders($headerContent)
    {
        $headers = [];
        $headerParts = preg_split("/[\n|\r]/s", $headerContent);
        foreach ($headerParts as $headerPart) {
            if (empty($headerPart)) {
                continue;
            }
            if (($separatorPos = strpos($headerPart, ':')) === false) {
                continue;
            }

            list($headerName, $headerValue) = explode(':', $headerPart, 2);
            $headerName = strtolower(trim($headerName));
            $headerValue = trim($headerValue);

            if (strpos($headerValue, ';') === false) {
                $headers[$headerName] = $headerValue;
            } else {
                $headers[$headerName] = [];
                foreach (explode(';', $headerValue) as $part) {
                    $part = trim($part);
                    if (strpos($part, '=') === false) {
                        $headers[$headerName][] = $part;
                    } else {
                        list($name, $value) = explode('=', $part, 2);
                        $name = strtolower(trim($name));
                        $value = trim(trim($value), '"');
                        $headers[$headerName][$name] = $value;
                    }
                }
            }
        }

        return $headers;
    }

    /**
     * Adds value to the array by input name, e.g. `Item[name]`.
     * @param array $array array which should store value.
     * @param string $name input name specification.
     * @param mixed $value value to be added.
     */
    private function addValue(&$array, $name, $value)
    {
        $nameParts = preg_split('/\\]\\[|\\[/s', $name);
        $current = &$array;
        foreach ($nameParts as $namePart) {
            $namePart = trim($namePart, ']');
            if ($namePart === '') {
                $current[] = [];
                $lastKey = array_pop(array_keys($current));
                $current = &$current[$lastKey];
            } else {
                if (!isset($current[$namePart])) {
                    $current[$namePart] = [];
                }
                $current = &$current[$namePart];
            }
        }
        $current = $value;
    }

    /**
     * Adds file info to the uploaded files array by input name, e.g. `Item[file]`.
     * @param array $files array containing uploaded files
     * @param string $name input name specification.
     * @param array $info file info.
     */
    private function addFile(&$files, $name, $info)
    {
        if (strpos($name, '[') === false) {
            $files[$name] = $info;
            return;
        }

        $fileInfoAttributes = [
            'name',
            'type',
            'size',
            'error',
            'tmp_name',
            'tmp_resource'
        ];

        $nameParts = preg_split('/\\]\\[|\\[/s', $name);
        $baseName = array_shift($nameParts);
        if (!isset($files[$baseName])) {
            $files[$baseName] = [];
            foreach ($fileInfoAttributes as $attribute) {
                $files[$baseName][$attribute] = [];
            }
        } else {
            foreach ($fileInfoAttributes as $attribute) {
                $files[$baseName][$attribute] = (array)$files[$baseName][$attribute];
            }
        }

        foreach ($fileInfoAttributes as $attribute) {
            if (!isset($info[$attribute])) {
                continue;
            }

            $current = &$files[$baseName][$attribute];
            foreach ($nameParts as $namePart) {
                $namePart = trim($namePart, ']');
                if ($namePart === '') {
                    $current[] = [];
                    $lastKey = array_pop(array_keys($current));
                    $current = &$current[$lastKey];
                } else {
                    if (!isset($current[$namePart])) {
                        $current[$namePart] = [];
                    }
                    $current = &$current[$namePart];
                }
            }
            $current = $info[$attribute];
        }
    }

    /**
     * Gets the size in bytes from verbose size representation.
     * For example: '5K' => 5*1024
     * @param string $verboseSize verbose size representation.
     * @return integer actual size in bytes.
     */
    private function getByteSize($verboseSize)
    {
        if (empty($verboseSize)) {
            return 0;
        }
        if (is_numeric($verboseSize)) {
            return (int) $verboseSize;
        }
        $sizeUnit = trim($verboseSize, '0123456789');
        $size = str_replace($sizeUnit, '', $verboseSize);
        $size = trim($size);
        if (!is_numeric($size)) {
            return 0;
        }
        switch (strtolower($sizeUnit)) {
            case 'kb':
            case 'k':
                return $size * 1024;
            case 'mb':
            case 'm':
                return $size * 1024 * 1024;
            case 'gb':
            case 'g':
                return $size * 1024 * 1024 * 1024;
            default:
                return 0;
        }
    }
}