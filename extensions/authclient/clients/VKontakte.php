<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OAuth2;

/**
 * VKontakte allows authentication via VKontakte OAuth.
 *
 * In order to use VKontakte OAuth you must register your application at <http://vk.com/editapp?act=create>.
 *
 * Example application configuration:
 *
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'vkontakte' => [
 *                 'class' => 'yii\authclient\clients\VKontakte',
 *                 'clientId' => 'vkontakte_client_id',
 *                 'clientSecret' => 'vkontakte_client_secret',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @see http://vk.com/editapp?act=create
 * @see http://vk.com/developers.php?oid=-1&p=users.get
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class VKontakte extends OAuth2
{
    /**
     * @inheritdoc
     */
    public $authUrl = 'http://api.vk.com/oauth/authorize';
    /**
     * @inheritdoc
     */
    public $tokenUrl = 'https://api.vk.com/oauth/access_token';
    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'https://api.vk.com/method';

    /**
     * @inheritdoc
     */
    public function  fetchAccessToken($authCode, array $params = []) {
        $defaultParams = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $authCode,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->getReturnUrl(),
        ];
        $response = $this->sendRequest('POST', $this->tokenUrl, array_merge($defaultParams, $params));
        $token = $this->createToken(['params' => $response]);
        $this->setAccessToken($token);
        $this->setUserAttributes( array_merge( $this->getUserAttributes(), ['email' => $response['email']] ));
        return $token;
    }

    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
        $attributes = $this->api('users.get.json', 'GET', [
            'fields' => implode(',', [
                'uid',
                'first_name',
                'last_name',
                'nickname',
                'screen_name',
                'sex',
                'bdate',
                'city',
                'country',
                'timezone',
                'photo'
            ]),
        ]);
        return array_shift($attributes['response']);
    }

    /**
     * @inheritdoc
     */
    protected function apiInternal($accessToken, $url, $method, array $params, array $headers)
    {
        $params['uids'] = $accessToken->getParam('user_id');
        $params['access_token'] = $accessToken->getToken();
        return $this->sendRequest($method, $url, $params, $headers);
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'vkontakte';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return 'VKontakte';
    }

    /**
     * @inheritdoc
     */
    protected function defaultNormalizeUserAttributeMap()
    {
        return [
            'id' => 'uid'
        ];
    }
}
