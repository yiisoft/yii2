<?php

namespace yiiunit\extensions\authclient\oauth;

use yii\authclient\OAuthToken;
use yiiunit\extensions\authclient\TestCase;

class TokenTest extends TestCase
{
    public function testCreate()
    {
        $config = [
            'tokenParamKey' => 'test_token_param_key',
            'tokenSecretParamKey' => 'test_token_secret_param_key',
        ];
        $oauthToken = new OAuthToken($config);
        $this->assertTrue(is_object($oauthToken), 'Unable to create access token!');
        foreach ($config as $name => $value) {
            $this->assertEquals($value, $oauthToken->$name, 'Unable to setup attributes by constructor!');
        }
        $this->assertTrue($oauthToken->createTimestamp > 0, 'Unable to fill create timestamp!');
    }

    public function testSetupParams()
    {
        $oauthToken = new OAuthToken();

        $params = [
            'name_1' => 'value_1',
            'name_2' => 'value_2',
        ];
        $oauthToken->setParams($params);
        $this->assertEquals($params, $oauthToken->getParams(), 'Unable to setup params!');

        $newParamName = 'new_param_name';
        $newParamValue = 'new_param_value';
        $oauthToken->setParam($newParamName, $newParamValue);
        $this->assertEquals($newParamValue, $oauthToken->getParam($newParamName), 'Unable to setup param by name!');
    }

    /**
     * @depends testSetupParams
     */
    public function testSetupParamsShortcuts()
    {
        $oauthToken = new OAuthToken();

        $token = 'test_token_value';
        $oauthToken->setToken($token);
        $this->assertEquals($token, $oauthToken->getToken(), 'Unable to setup token!');

        $tokenSecret = 'test_token_secret';
        $oauthToken->setTokenSecret($tokenSecret);
        $this->assertEquals($tokenSecret, $oauthToken->getTokenSecret(), 'Unable to setup token secret!');

        $tokenExpireDuration = rand(1000, 2000);
        $oauthToken->setExpireDuration($tokenExpireDuration);
        $this->assertEquals($tokenExpireDuration, $oauthToken->getExpireDuration(), 'Unable to setup expire duration!');
    }

    /**
     * Data provider for {@link testAutoFetchExpireDuration}.
     * @return array test data.
     */
    public function autoFetchExpireDurationDataProvider()
    {
        return [
            [
                ['expire_in' => 123345],
                123345
            ],
            [
                ['expire' => 233456],
                233456
            ],
            [
                ['expiry_in' => 34567],
                34567
            ],
            [
                ['expiry' => 45678],
                45678
            ],
        ];
    }

    /**
     * @depends testSetupParamsShortcuts
     * @dataProvider autoFetchExpireDurationDataProvider
     *
     * @param array $params
     * @param $expectedExpireDuration
     */
    public function testAutoFetchExpireDuration(array $params, $expectedExpireDuration)
    {
        $oauthToken = new OAuthToken();
        $oauthToken->setParams($params);
        $this->assertEquals($expectedExpireDuration, $oauthToken->getExpireDuration());
    }

    /**
     * @depends testSetupParamsShortcuts
     */
    public function testGetIsExpired()
    {
        $oauthToken = new OAuthToken();
        $expireDuration = 3600;
        $oauthToken->setExpireDuration($expireDuration);

        $this->assertFalse($oauthToken->getIsExpired(), 'Not expired token check fails!');

        $oauthToken->createTimestamp = $oauthToken->createTimestamp - ($expireDuration +1);
        $this->assertTrue($oauthToken->getIsExpired(), 'Expired token check fails!');
    }

    /**
     * @depends testGetIsExpired
     */
    public function testGetIsValid()
    {
        $oauthToken = new OAuthToken();
        $expireDuration = 3600;
        $oauthToken->setExpireDuration($expireDuration);

        $this->assertFalse($oauthToken->getIsValid(), 'Empty token is valid!');

        $oauthToken->setToken('test_token');
        $this->assertTrue($oauthToken->getIsValid(), 'Filled up token is invalid!');

        $oauthToken->createTimestamp = $oauthToken->createTimestamp - ($expireDuration +1);
        $this->assertFalse($oauthToken->getIsValid(), 'Expired token is valid!');
    }
}
