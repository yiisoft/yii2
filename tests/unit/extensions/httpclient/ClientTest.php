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
} 