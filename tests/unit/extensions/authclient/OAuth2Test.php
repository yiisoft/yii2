<?php

namespace yiiunit\extensions\authclient\oauth;

use yii\authclient\OAuth2;
use yiiunit\extensions\authclient\TestCase;

class OAuth2Test extends TestCase
{
	protected function setUp()
	{
		$config = [
			'components' => [
				'request' => [
					'hostInfo' => 'http://testdomain.com',
					'scriptUrl' => '/index.php',
				],
			]
		];
		$this->mockApplication($config, '\yii\web\Application');
	}

	// Tests :

	public function testBuildAuthUrl()
	{
		$oauthClient = new OAuth2();
		$authUrl = 'http://test.auth.url';
		$oauthClient->authUrl = $authUrl;
		$clientId = 'test_client_id';
		$oauthClient->clientId = $clientId;
		$returnUrl = 'http://test.return.url';
		$oauthClient->setReturnUrl($returnUrl);

		$builtAuthUrl = $oauthClient->buildAuthUrl();

		$this->assertContains($authUrl, $builtAuthUrl, 'No auth URL present!');
		$this->assertContains($clientId, $builtAuthUrl, 'No client id present!');
		$this->assertContains(rawurlencode($returnUrl), $builtAuthUrl, 'No return URL present!');
	}
}
