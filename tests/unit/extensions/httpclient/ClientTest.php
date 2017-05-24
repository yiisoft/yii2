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

        $responseFormat = 'testFormat';
        $responseContent = 'test content';
        $client->responseConfig = [
            'format' => $responseFormat
        ];
        $response = $client->createResponse($responseContent);
        $this->assertEquals($responseFormat, $response->getFormat());
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

    /**
     * @depends testSend
     */
    public function testSendPost()
    {
        $client = new Client();
        $client->baseUrl = 'http://uk.php.net';
        $response = $client->createRequest()
            ->setMethod('post')
            ->setUrl('search.php')
            ->setData(['pattern' => 'curl'])
            ->send();
        $this->assertTrue($response->isOk());
    }

    /**
     * @depends testSend
     */
    public function testBatchSend()
    {
        $client = new Client();
        $client->baseUrl = 'http://uk.php.net';

        $requests = [];
        $requests['docs'] = $client->createRequest()
            ->setMethod('get')
            ->setUrl('docs.php');
        $requests['support'] = $client->createRequest()
            ->setMethod('get')
            ->setUrl('support.php');

        $responses = $client->batchSend($requests);
        $this->assertCount(count($requests), $responses);

        foreach ($responses as $response) {
            $this->assertTrue($response->isOk());
        }

        $this->assertTrue($responses['docs'] instanceof Response, $responses);
        $this->assertTrue($responses['support'] instanceof Response, $responses);

        $this->assertContains('<h1>Documentation</h1>', $responses['docs']->getContent());
        $this->assertContains('Mailing Lists', $responses['support']->getContent());
    }
} 