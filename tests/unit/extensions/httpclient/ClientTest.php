<?php

namespace yiiunit\extensions\httpclient;

use yii\httpclient\Client;
use yii\httpclient\Request;
use yii\httpclient\Response;

class ClientTest extends TestCase
{
    public function testCreateRequest()
    {
        $client = new Client();

        $request = $client->createRequest();
        $this->assertTrue($request instanceof Request);
        $this->assertEquals($client, $request->client);

        $requestContent = 'test content';
        $client->requestConfig = [
            'content' => $requestContent
        ];
        $request = $client->createRequest();
        $this->assertEquals($requestContent, $request->getContent());
    }

    public function testCreateResponse()
    {
        $client = new Client();

        $response = $client->createResponse();
        $this->assertTrue($response instanceof Response);

        $responseContent = 'test content';
        $client->responseConfig = [
            'content' => $responseContent
        ];
        $response = $client->createResponse();
        $this->assertEquals($responseContent, $response->getContent());
    }

    /**
     * @depends testCreateRequest
     * @depends testCreateResponse
     */
    public function testSend()
    {
        $client = new Client();
        $client->baseUrl = 'http://uk.php.net';
        $response = $client->createRequest()
            ->setMethod('get')
            ->setUrl('docs.php')
            ->send();

        $this->assertTrue($response->isOk());
        $content = $response->getContent();
        $this->assertNotEmpty($content);
        $this->assertContains('<h1>Documentation</h1>', $content);
    }
} 