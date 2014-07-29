<?php

namespace yiiunit\extensions\authclient\oauth;

use yii\authclient\OAuth1;
use yii\authclient\signature\PlainText;
use yii\authclient\OAuthToken;
use yiiunit\extensions\authclient\TestCase;

class OAuth1Test extends TestCase
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

    /**
     * Invokes the OAuth client method even if it is protected.
     * @param  OAuth1 $oauthClient OAuth client instance.
     * @param  string $methodName  name of the method to be invoked.
     * @param  array  $arguments   method arguments.
     * @return mixed  method invoke result.
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

    public function testSignRequest()
    {
        $oauthClient = new OAuth1();

        $oauthSignatureMethod = new PlainText();
        $oauthClient->setSignatureMethod($oauthSignatureMethod);

        $signedParams = $this->invokeOAuthClientMethod($oauthClient, 'signRequest', ['GET', 'http://test.url', []]);
        $this->assertNotEmpty($signedParams['oauth_signature'], 'Unable to sign request!');
    }

    /**
     * Data provider for [[testComposeAuthorizationHeader()]].
     * @return array test data.
     */
    public function composeAuthorizationHeaderDataProvider()
    {
        return [
            [
                '',
                [
                    'oauth_test_name_1' => 'oauth_test_value_1',
                    'oauth_test_name_2' => 'oauth_test_value_2',
                ],
                'Authorization: OAuth oauth_test_name_1="oauth_test_value_1", oauth_test_name_2="oauth_test_value_2"'
            ],
            [
                'test_realm',
                [
                    'oauth_test_name_1' => 'oauth_test_value_1',
                    'oauth_test_name_2' => 'oauth_test_value_2',
                ],
                'Authorization: OAuth realm="test_realm", oauth_test_name_1="oauth_test_value_1", oauth_test_name_2="oauth_test_value_2"'
            ],
            [
                '',
                [
                    'oauth_test_name_1' => 'oauth_test_value_1',
                    'test_name_2' => 'test_value_2',
                ],
                'Authorization: OAuth oauth_test_name_1="oauth_test_value_1"'
            ],
        ];
    }

    /**
     * @dataProvider composeAuthorizationHeaderDataProvider
     *
     * @param string $realm                       authorization realm.
     * @param array  $params                      request params.
     * @param string $expectedAuthorizationHeader expected authorization header.
     */
    public function testComposeAuthorizationHeader($realm, array $params, $expectedAuthorizationHeader)
    {
        $oauthClient = new OAuth1();
        $authorizationHeader = $this->invokeOAuthClientMethod($oauthClient, 'composeAuthorizationHeader', [$params, $realm]);
        $this->assertEquals($expectedAuthorizationHeader, $authorizationHeader);
    }

    public function testBuildAuthUrl()
    {
        $oauthClient = new OAuth1();
        $authUrl = 'http://test.auth.url';
        $oauthClient->authUrl = $authUrl;

        $requestTokenToken = 'test_request_token';
        $requestToken = new OAuthToken();
        $requestToken->setToken($requestTokenToken);

        $builtAuthUrl = $oauthClient->buildAuthUrl($requestToken);

        $this->assertContains($authUrl, $builtAuthUrl, 'No auth URL present!');
        $this->assertContains($requestTokenToken, $builtAuthUrl, 'No token present!');
    }
}
