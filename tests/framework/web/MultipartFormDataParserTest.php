<?php

namespace yiiunit\framework\web;

use yii\web\MultipartFormDataParser;
use yiiunit\TestCase;

class MultipartFormDataParserTest extends TestCase
{
    public function testParse()
    {
        $parser = new MultipartFormDataParser();

        $boundary = '---------------------------22472926011618';
        $contentType = 'multipart/form-data; boundary=' . $boundary;
        $rawBody = "--{$boundary}\nContent-Disposition: form-data; name=\"title\"\r\ntest-title";
        $rawBody .= "--{$boundary}\nContent-Disposition: form-data; name=\"Item[name]\"\r\ntest-name";
        $rawBody .= "--{$boundary}\nContent-Disposition: form-data; name=\"someFile\"; filename=\"some-file.txt\"\nContent-Type: text/plain\r\nsome file content";
        $rawBody .= "--{$boundary}\nContent-Disposition: form-data; name=\"Item[file]\"; filename=\"item-file.txt\"\nContent-Type: text/plain\r\nitem file content";
        $rawBody .= "--{$boundary}--";

        $bodyParams = $parser->parse($rawBody, $contentType);

        $expectedBodyParams = [
            'title' => 'test-title',
            'Item' => [
                'name' => 'test-name'
            ]
        ];
        $this->assertEquals($expectedBodyParams, $bodyParams);

        $this->assertFalse(empty($_FILES['someFile']));
        $this->assertEquals(UPLOAD_ERR_OK, $_FILES['someFile']['error']);
        $this->assertEquals('some-file.txt', $_FILES['someFile']['name']);
        $this->assertEquals('text/plain', $_FILES['someFile']['type']);
        $this->assertEquals('some file content', file_get_contents($_FILES['someFile']['tmp_name']));

        $this->assertFalse(empty($_FILES['Item']));
        $this->assertFalse(empty($_FILES['Item']['name']['file']));
        $this->assertEquals('item-file.txt', $_FILES['Item']['name']['file']);
        $this->assertEquals('text/plain', $_FILES['Item']['type']['file']);
        $this->assertEquals('item file content', file_get_contents($_FILES['Item']['tmp_name']['file']));
    }

    /**
     * @depends testParse
     */
    public function testNotEmptyPost()
    {
        $parser = new MultipartFormDataParser();

        $_POST = [
            'name' => 'value'
        ];

        $bodyParams = $parser->parse('should not matter', 'multipart/form-data; boundary=---12345');
        $this->assertEquals($_POST, $bodyParams);
        $this->assertEquals([], $_FILES);
    }

    /**
     * @depends testParse
     */
    public function testNotEmptyFiles()
    {
        $parser = new MultipartFormDataParser();

        $_FILES = [
            'file' => [
                'name' => 'file.txt',
                'type' => 'text/plain',
            ]
        ];

        $boundary = '---------------------------22472926011618';
        $contentType = 'multipart/form-data; boundary=' . $boundary;
        $rawBody = "--{$boundary}\nContent-Disposition: form-data; name=\"title\"\r\ntest-title--{$boundary}--";

        $bodyParams = $parser->parse($rawBody, $contentType);
        $this->assertEquals([], $bodyParams);
    }
}