<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use yii\http\ResourceStream;

/**
 * MultipartFormDataParser parses content encoded as 'multipart/form-data'.
 * This parser provides the fallback for the 'multipart/form-data' processing on non POST requests,
 * for example: the one with 'PUT' request method.
 *
 * In order to enable this parser you should configure [[Request::$parsers]] in the following way:
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
 * Method [[parse()]] of this parser automatically populates [[Request::$uploadedFiles]] with the files parsed from raw body.
 *
 * Usage example:
 *
 * ```php
 * use yii\http\UploadedFile;
 *
 * $restRequestData = Yii::$app->request->getParsedBody();
 * $uploadedFile = Yii::$app->request->getUploadedFileByName('photo');
 *
 * $model = new Item();
 * $model->populate($restRequestData);
 * copy($uploadedFile->tempName, '/path/to/file/storage/photo.jpg');
 * ```
 *
 * > Note: although this parser populates temporary file name for the uploaded file instance, such temporary file will
 * not be recognized by PHP as uploaded one. Thus functions like `is_uploaded_file()` and `move_uploaded_file()` will
 * fail on it. This also means [[UploadedFile::saveAs()]] will fail as well.
 *
 * @property int $uploadFileMaxCount Maximum upload files count.
 * @property int $uploadFileMaxSize Upload file max size in bytes.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.10
 */
class MultipartFormDataParser extends BaseObject implements RequestParserInterface
{
    /**
     * @var bool whether to parse raw body even for 'POST' request and `$_FILES` already populated.
     * By default this option is disabled saving performance for 'POST' requests, which are already
     * processed by PHP automatically.
     * > Note: if this option is enabled, value of [[Request::$uploadedFiles]] will be reset on each parse.
     * @since 2.0.13
     */
    public $force = false;

    /**
     * @var int upload file max size in bytes.
     */
    private $_uploadFileMaxSize;
    /**
     * @var int maximum upload files count.
     */
    private $_uploadFileMaxCount;


    /**
     * @return int upload file max size in bytes.
     */
    public function getUploadFileMaxSize()
    {
        if ($this->_uploadFileMaxSize === null) {
            $this->_uploadFileMaxSize = $this->getByteSize(ini_get('upload_max_filesize'));
        }

        return $this->_uploadFileMaxSize;
    }

    /**
     * @param int $uploadFileMaxSize upload file max size in bytes.
     */
    public function setUploadFileMaxSize($uploadFileMaxSize)
    {
        $this->_uploadFileMaxSize = $uploadFileMaxSize;
    }

    /**
     * @return int maximum upload files count.
     */
    public function getUploadFileMaxCount()
    {
        if ($this->_uploadFileMaxCount === null) {
            $this->_uploadFileMaxCount = ini_get('max_file_uploads');
        }

        return $this->_uploadFileMaxCount;
    }

    /**
     * @param int $uploadFileMaxCount maximum upload files count.
     */
    public function setUploadFileMaxCount($uploadFileMaxCount)
    {
        $this->_uploadFileMaxCount = $uploadFileMaxCount;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($request)
    {
        if (!$this->force) {
            if (!empty($_POST) || !empty($_FILES)) {
                // normal POST request is parsed by PHP automatically
                return $_POST;
            }
        }

        $uploadedFiles = [];

        $contentType = $request->getContentType();
        $rawBody = $request->getBody()->__toString();

        if (empty($rawBody)) {
            return [];
        }

        if (!preg_match('/boundary=(.*)$/is', $contentType, $matches)) {
            return [];
        }
        $boundary = $matches[1];

        $bodyParts = preg_split('/\\R?-+' . preg_quote($boundary, '/') . '/s', $rawBody);
        array_pop($bodyParts); // last block always has no data, contains boundary ending like `--`

        $bodyParams = [];
        $filesCount = 0;
        foreach ($bodyParts as $bodyPart) {
            if (empty($bodyPart)) {
                continue;
            }
            [$headers, $value] = preg_split('/\\R\\R/', $bodyPart, 2);
            $headers = $this->parseHeaders($headers);

            if (!isset($headers['content-disposition']['name'])) {
                continue;
            }

            if (isset($headers['content-disposition']['filename'])) {
                // file upload:
                if ($filesCount >= $this->getUploadFileMaxCount()) {
                    continue;
                }

                $fileConfig = [
                    '__class' => $request->uploadedFileClass,
                    'clientFilename' => $headers['content-disposition']['filename'],
                    'clientMediaType' => ArrayHelper::getValue($headers, 'content-type', 'application/octet-stream'),
                    'size' => StringHelper::byteLength($value),
                    'error' => UPLOAD_ERR_OK,
                    'tempFilename' => null,
                ];

                if ($fileConfig['size'] > $this->getUploadFileMaxSize()) {
                    $fileConfig['error'] = UPLOAD_ERR_INI_SIZE;
                } else {
                    $tmpResource = tmpfile();
                    if ($tmpResource === false) {
                        $fileConfig['error'] = UPLOAD_ERR_CANT_WRITE;
                    } else {
                        $tmpResourceMetaData = stream_get_meta_data($tmpResource);
                        $tmpFileName = $tmpResourceMetaData['uri'];
                        if (empty($tmpFileName)) {
                            $fileConfig['error'] = UPLOAD_ERR_CANT_WRITE;
                            @fclose($tmpResource);
                        } else {
                            fwrite($tmpResource, $value);
                            $fileConfig['tempFilename'] = $tmpFileName;
                            $fileConfig['stream'] = new ResourceStream(['resource' => $tmpResource]); // save file resource, otherwise it will be deleted
                        }
                    }
                }

                $this->addValue($uploadedFiles, $headers['content-disposition']['name'], Yii::createObject($fileConfig));

                $filesCount++;
            } else {
                // regular parameter:
                $this->addValue($bodyParams, $headers['content-disposition']['name'], $value);
            }
        }

        $request->setUploadedFiles($uploadedFiles);

        return ArrayHelper::merge($bodyParams, $uploadedFiles);
    }

    /**
     * Parses content part headers.
     * @param string $headerContent headers source content
     * @return array parsed headers.
     */
    private function parseHeaders($headerContent)
    {
        $headers = [];
        $headerParts = preg_split('/\\R/s', $headerContent, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($headerParts as $headerPart) {
            if (strpos($headerPart, ':') === false) {
                continue;
            }

            [$headerName, $headerValue] = explode(':', $headerPart, 2);
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
                        [$name, $value] = explode('=', $part, 2);
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
                $keys = array_keys($current);
                $lastKey = array_pop($keys);
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
     * Gets the size in bytes from verbose size representation.
     *
     * For example: '5K' => 5*1024.
     * @param string $verboseSize verbose size representation.
     * @return int actual size in bytes.
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
        $size = trim(str_replace($sizeUnit, '', $verboseSize));
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
