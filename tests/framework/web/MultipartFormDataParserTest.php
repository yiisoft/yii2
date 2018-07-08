<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use Psr\Http\Message\UploadedFileInterface;
use yii\http\UploadedFile;
use yii\web\MultipartFormDataParser;
use yii\web\Request;
use yiiunit\TestCase;

class MultipartFormDataParserTest extends TestCase
{
    public function testParse()
    {
        $parser = new MultipartFormDataParser();

        $boundary = '---------------------------22472926011618';
        $contentType = 'multipart/form-data; boundary=' . $boundary;
        $rawBody = "--{$boundary}\nContent-Disposition: form-data; name=\"title\"\r\n\r\ntest-title";
        $rawBody .= "\r\n--{$boundary}\nContent-Disposition: form-data; name=\"Item[name]\"\r\n\r\ntest-name";
        $rawBody .= "\r\n--{$boundary}\nContent-Disposition: form-data; name=\"someFile\"; filename=\"some-file.txt\"\nContent-Type: text/plain\r\n\r\nsome file content";
        $rawBody .= "\r\n--{$boundary}\nContent-Disposition: form-data; name=\"Item[file]\"; filename=\"item-file.txt\"\nContent-Type: text/plain\r\n\r\nitem file content";
        $rawBody .= "\r\n--{$boundary}--";

        $request = new Request([
            'rawBody' => $rawBody,
            'headers' => [
                'content-type' => [$contentType]
            ]
        ]);

        $bodyParams = $parser->parse($request);

        $uploadedFiles = $request->getUploadedFiles();

        $this->assertFalse(empty($uploadedFiles['someFile']));
        /* @var $uploadedFile UploadedFileInterface */
        $uploadedFile = $uploadedFiles['someFile'];
        $this->assertTrue($uploadedFile instanceof UploadedFileInterface);
        $this->assertEquals(UPLOAD_ERR_OK, $uploadedFile->getError());
        $this->assertEquals('some-file.txt', $uploadedFile->getClientFilename());
        $this->assertEquals('text/plain', $uploadedFile->getClientMediaType());
        $this->assertEquals('some file content', $uploadedFile->getStream()->__toString());

        $this->assertFalse(empty($uploadedFiles['Item']['file']));
        /* @var $uploadedFile UploadedFileInterface */
        $uploadedFile = $uploadedFiles['Item']['file'];
        $this->assertEquals(UPLOAD_ERR_OK, $uploadedFile->getError());
        $this->assertEquals('item-file.txt', $uploadedFile->getClientFilename());
        $this->assertEquals('text/plain', $uploadedFile->getClientMediaType());
        $this->assertEquals('item file content', $uploadedFile->getStream()->__toString());

        $expectedBodyParams = [
            'title' => 'test-title',
            'Item' => [
                'name' => 'test-name',
                'file' => $uploadedFiles['Item']['file'],
            ],
            'someFile' => $uploadedFiles['someFile']
        ];
        $this->assertEquals($expectedBodyParams, $bodyParams);
    }

    /**
     * @depends testParse
     */
    public function testNotEmptyPost()
    {
        $_POST = [
            'name' => 'value',
        ];

        $request = new Request([
            'rawBody' => 'should not matter',
            'headers' => [
                'content-type' => ['multipart/form-data; boundary=---12345']
            ],
            'parsers' => [
                'multipart/form-data' => MultipartFormDataParser::class
            ]
        ]);
        $bodyParams = $request->getParsedBody();
        $this->assertEquals($_POST, $bodyParams);
        $this->assertEquals([], $request->getUploadedFiles());
    }

    /**
     * @depends testParse
     */
    public function testNotEmptyFiles()
    {
        $_FILES = [
            'file' => [
                'name' => 'file.txt',
                'type' => 'text/plain',
            ],
        ];

        $boundary = '---------------------------22472926011618';
        $contentType = 'multipart/form-data; boundary=' . $boundary;
        $rawBody = "--{$boundary}\nContent-Disposition: form-data; name=\"title\"\r\ntest-title--{$boundary}--";

        $request = new Request([
            'rawBody' => $rawBody,
            'headers' => [
                'content-type' => [$contentType]
            ],
            'parsers' => [
                'multipart/form-data' => MultipartFormDataParser::class
            ]
        ]);
        $bodyParams = $request->getParsedBody();
        $this->assertEquals([], $bodyParams);
    }

    /**
     * @depends testParse
     */
    public function testUploadFileMaxCount()
    {
        $parser = new MultipartFormDataParser();
        $parser->setUploadFileMaxCount(2);

        $boundary = '---------------------------22472926011618';
        $contentType = 'multipart/form-data; boundary=' . $boundary;
        $rawBody = "--{$boundary}\nContent-Disposition: form-data; name=\"firstFile\"; filename=\"first-file.txt\"\nContent-Type: text/plain\r\n\r\nfirst file content";
        $rawBody .= "--{$boundary}\nContent-Disposition: form-data; name=\"secondFile\"; filename=\"second-file.txt\"\nContent-Type: text/plain\r\n\r\nsecond file content";
        $rawBody .= "--{$boundary}\nContent-Disposition: form-data; name=\"thirdFile\"; filename=\"third-file.txt\"\nContent-Type: text/plain\r\n\r\nthird file content";
        $rawBody .= "--{$boundary}--";

        $request = new Request([
            'rawBody' => $rawBody,
            'headers' => [
                'content-type' => [$contentType]
            ]
        ]);
        $parser->parse($request);
        $this->assertCount(2, $request->getUploadedFiles());
    }

    /**
     * @depends testParse
     */
    public function testUploadFileMaxSize()
    {
        $parser = new MultipartFormDataParser();
        $parser->setUploadFileMaxSize(20);

        $boundary = '---------------------------22472926011618';
        $contentType = 'multipart/form-data; boundary=' . $boundary;
        $rawBody = "--{$boundary}\nContent-Disposition: form-data; name=\"firstFile\"; filename=\"first-file.txt\"\nContent-Type: text/plain\r\n\r\nfirst file content";
        $rawBody .= "--{$boundary}\nContent-Disposition: form-data; name=\"secondFile\"; filename=\"second-file.txt\"\nContent-Type: text/plain\r\n\r\nsecond file content";
        $rawBody .= "--{$boundary}\nContent-Disposition: form-data; name=\"thirdFile\"; filename=\"third-file.txt\"\nContent-Type: text/plain\r\n\r\nthird file with too long file content";
        $rawBody .= "--{$boundary}--";

        $request = new Request([
            'rawBody' => $rawBody,
            'headers' => [
                'content-type' => [$contentType]
            ]
        ]);
        $parser->parse($request);
        $uploadedFiles = $request->getUploadedFiles();
        $this->assertCount(3, $uploadedFiles);
        $this->assertEquals(UPLOAD_ERR_INI_SIZE, $uploadedFiles['thirdFile']->getError());
    }

    /**
     * @depends testNotEmptyPost
     * @depends testNotEmptyFiles
     */
    public function testForce()
    {
        $parser = new MultipartFormDataParser();
        $parser->force = true;

        $_POST = [
            'existingName' => 'value',
        ];
        $_FILES = [
            'existingFile' => [
                'name' => 'file.txt',
                'type' => 'text/plain',
            ],
        ];

        $boundary = '---------------------------22472926011618';
        $contentType = 'multipart/form-data; boundary=' . $boundary;
        $rawBody = "--{$boundary}\nContent-Disposition: form-data; name=\"title\"\r\n\r\ntest-title";
        $rawBody .= "\r\n--{$boundary}\nContent-Disposition: form-data; name=\"someFile\"; filename=\"some-file.txt\"\nContent-Type: text/plain\r\n\r\nsome file content";
        $rawBody .= "\r\n--{$boundary}--";

        $request = new Request([
            'rawBody' => $rawBody,
            'headers' => [
                'content-type' => [$contentType]
            ]
        ]);
        $bodyParams = $parser->parse($request);

        $uploadedFiles = $request->getUploadedFiles();
        $this->assertNotEmpty($uploadedFiles['someFile']);
        $this->assertFalse(isset($uploadedFiles['existingFile']));

        $expectedBodyParams = [
            'title' => 'test-title',
            'someFile' => $uploadedFiles['someFile'],
        ];
        $this->assertEquals($expectedBodyParams, $bodyParams);
    }
}
