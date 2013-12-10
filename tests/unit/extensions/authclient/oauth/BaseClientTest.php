<?php

namespace yiiunit\extensions\authclient\oauth;

use yii\authclient\oauth\signature\PlainText;
use yii\authclient\oauth\Token;
use yiiunit\extensions\authclient\TestCase;
use yii\authclient\oauth\BaseClient;

class BaseClientTest extends TestCase
{
	/**
	 * Creates test OAuth client instance.
	 * @return BaseClient oauth client.
	 */
	protected function createOAuthClient()
	{
		$oauthClient = $this->getMock(BaseClient::className(), ['setState', 'getState', 'composeRequestCurlOptions', 'refreshAccessToken', 'apiInternal']);
		$oauthClient->expects($this->any())->method('setState')->will($this->returnValue($oauthClient));
		$oauthClient->expects($this->any())->method('getState')->will($this->returnValue(null));
		return $oauthClient;
	}

	/**
	 * Invokes the OAuth client method even if it is protected.
	 * @param BaseClient $oauthClient OAuth client instance.
	 * @param string $methodName name of the method to be invoked.
	 * @param array $arguments method arguments.
	 * @return mixed method invoke result.
	 */
	protected function invokeOAuthClientMethod($oauthClient, $methodName, array $arguments = [])
	{
		$classReflection = new \ReflectionClass(get_class($oauthClient));
		$methodReflection = $classReflection->getMethod($methodName);
		$methodReflection->setAccessible(true);
		$result = $methodReflection->invokeArgs($oauthClient, $arguments);
		$methodReflection->setAccessible(false);
		return $result;
	}

	// Tests :

	public function testSetGet()
	{
		$oauthClient = $this->createOAuthClient();

		$returnUrl = 'http://test.return.url';
		$oauthClient->setReturnUrl($returnUrl);
		$this->assertEquals($returnUrl, $oauthClient->getReturnUrl(), 'Unable to setup return URL!');

		$curlOptions = [
			'option1' => 'value1',
			'option2' => 'value2',
		];
		$oauthClient->setCurlOptions($curlOptions);
		$this->assertEquals($curlOptions, $oauthClient->getCurlOptions(), 'Unable to setup cURL options!');
	}

	public function testSetupComponents()
	{
		$oauthClient = $this->createOAuthClient();

		$oauthToken = new Token();
		$oauthClient->setAccessToken($oauthToken);
		$this->assertEquals($oauthToken, $oauthClient->getAccessToken(), 'Unable to setup token!');

		$oauthSignatureMethod = new PlainText();
		$oauthClient->setSignatureMethod($oauthSignatureMethod);
		$this->assertEquals($oauthSignatureMethod, $oauthClient->getSignatureMethod(), 'Unable to setup signature method!');
	}

	/**
	 * @depends testSetupComponents
	 */
	public function testSetupComponentsByConfig()
	{
		$oauthClient = $this->createOAuthClient();

		$oauthToken = [
			'token' => 'test_token',
			'tokenSecret' => 'test_token_secret',
		];
		$oauthClient->setAccessToken($oauthToken);
		$this->assertEquals($oauthToken['token'], $oauthClient->getAccessToken()->getToken(), 'Unable to setup token as config!');

		$oauthSignatureMethod = [
			'class' => 'yii\authclient\oauth\signature\PlainText'
		];
		$oauthClient->setSignatureMethod($oauthSignatureMethod);
		$returnedSignatureMethod = $oauthClient->getSignatureMethod();
		$this->assertEquals($oauthSignatureMethod['class'], get_class($returnedSignatureMethod), 'Unable to setup signature method as config!');
	}

	/**
	 * Data provider for [[testComposeUrl()]].
	 * @return array test data.
	 */
	public function composeUrlDataProvider()
	{
		return [
			[
				'http://test.url',
				[
					'param1' => 'value1',
					'param2' => 'value2',
				],
				'http://test.url?param1=value1&param2=value2',
			],
			[
				'http://test.url?with=some',
				[
					'param1' => 'value1',
					'param2' => 'value2',
				],
				'http://test.url?with=some&param1=value1&param2=value2',
			],
		];
	}

	/**
	 * @dataProvider composeUrlDataProvider
	 *
	 * @param string $url request URL.
	 * @param array $params request params
	 * @param string $expectedUrl expected composed URL.
	 */
	public function testComposeUrl($url, array $params, $expectedUrl)
	{
		$oauthClient = $this->createOAuthClient();
		$composedUrl = $this->invokeOAuthClientMethod($oauthClient, 'composeUrl', [$url, $params]);
		$this->assertEquals($expectedUrl, $composedUrl);
	}

	/**
	 * Data provider for {@link testDetermineContentTypeByHeaders}.
	 * @return array test data.
	 */
	public function determineContentTypeByHeadersDataProvider()
	{
		return [
			[
				['content_type' => 'application/json'],
				'json'
			],
			[
				['content_type' => 'application/x-www-form-urlencoded'],
				'urlencoded'
			],
			[
				['content_type' => 'application/xml'],
				'xml'
			],
			[
				['some_header' => 'some_header_value'],
				'auto'
			],
			[
				['content_type' => 'unknown'],
				'auto'
			],
		];
	}

	/**
	 * @dataProvider determineContentTypeByHeadersDataProvider
	 *
	 * @param array $headers request headers.
	 * @param string $expectedResponseType expected response type.
	 */
	public function testDetermineContentTypeByHeaders(array $headers, $expectedResponseType)
	{
		$oauthClient = $this->createOAuthClient();
		$responseType = $this->invokeOAuthClientMethod($oauthClient, 'determineContentTypeByHeaders', [$headers]);
		$this->assertEquals($expectedResponseType, $responseType);
	}

	/**
	 * Data provider for [[testDetermineContentTypeByRaw]].
	 * @return array test data.
	 */
	public function determineContentTypeByRawDataProvider()
	{
		return array(
			['{name: value}', 'json'],
			['name=value', 'urlencoded'],
			['name1=value1&name2=value2', 'urlencoded'],
			['<?xml version="1.0" encoding="UTF-8"?><tag>Value</tag>', 'xml'],
			['<tag>Value</tag>', 'xml'],
		);
	}

	/**
	 * @dataProvider determineContentTypeByRawDataProvider
	 *
	 * @param string $rawResponse raw response content.
	 * @param string $expectedResponseType expected response type.
	 */
	public function testDetermineContentTypeByRaw($rawResponse, $expectedResponseType)
	{
		$oauthClient = $this->createOAuthClient();
		$responseType = $this->invokeOAuthClientMethod($oauthClient, 'determineContentTypeByRaw', [$rawResponse]);
		$this->assertEquals($expectedResponseType, $responseType);
	}

	/**
	 * Data provider for [[testApiUrl]].
	 * @return array test data.
	 */
	public function apiUrlDataProvider()
	{
		return [
			[
				'http://api.base.url',
				'sub/url',
				'http://api.base.url/sub/url',
			],
			[
				'http://api.base.url',
				'http://api.base.url/sub/url',
				'http://api.base.url/sub/url',
			],
			[
				'http://api.base.url',
				'https://api.base.url/sub/url',
				'https://api.base.url/sub/url',
			],
		];
	}

	/**
	 * @dataProvider apiUrlDataProvider
	 *
	 * @param $apiBaseUrl
	 * @param $apiSubUrl
	 * @param $expectedApiFullUrl
	 */
	public function testApiUrl($apiBaseUrl, $apiSubUrl, $expectedApiFullUrl)
	{
		$oauthClient = $this->createOAuthClient();
		$oauthClient->expects($this->any())->method('apiInternal')->will($this->returnArgument(1));

		$accessToken = new Token();
		$accessToken->setToken('test_access_token');
		$accessToken->setExpireDuration(1000);
		$oauthClient->setAccessToken($accessToken);

		$oauthClient->apiBaseUrl = $apiBaseUrl;

		$this->assertEquals($expectedApiFullUrl, $oauthClient->api($apiSubUrl));
	}
}