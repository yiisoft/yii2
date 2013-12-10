<?php

namespace yiiunit\extensions\authclient\oauth;

use yii\authclient\oauth\Client2;
use yiiunit\extensions\authclient\TestCase;

class Client2Test extends TestCase
{
	protected function setUp()
	{
		$this->mockApplication([], '\yii\web\Application');
	}

	// Tests :

	public function testBuildAuthUrl()
	{
		$oauthClient = new Client2();
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