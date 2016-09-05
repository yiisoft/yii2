<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

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
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.10
 */
class MultipartFormDataParser implements RequestParserInterface
{
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
                $fileSize = StringHelper::byteLength($value);
                $error = UPLOAD_ERR_OK;

                $tmpResource = tmpfile();
                $tmpFileName = null;
                if ($tmpResource === false) {
                    $error = UPLOAD_ERR_CANT_WRITE;
                } else {
                    $tmpResourceMetaData = stream_get_meta_data($tmpResource);
                    $tmpFileName = $tmpResourceMetaData['uri'];
                    fwrite($tmpResource, $value);
                }

                $fileInfo = [
                    'name' => $headers['content-disposition']['filename'],
                    'type' => ArrayHelper::getValue($headers, 'content-type', 'application/octet-stream'),
                    'size' => $fileSize,
                    'error' => $error,
                ];

                if ($error === UPLOAD_ERR_OK) {
                    $fileInfo['tmp_name'] = $tmpFileName;
                    $fileInfo['tmp_resource'] = $tmpResource; // save file resource, otherwise it will be deleted
                }

                $this->addFile($_FILES, $headers['content-disposition']['name'], $fileInfo);
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
}