<?php
/**
 * Mail.ru oAuth2 client.
 */

namespace yii\authclient\clients;

use yii\authclient\OAuth2;

/**
 *
 * In order to use Mail.ru OAuth you must register your application at <http://api.mail.ru/sites/my/add>.
 *
 * Example application configuration:
 *
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'mailru' => [
 *                 'class' => 'yii\authclient\Mailru',
 *                 'clientId' => 'mailru_client_id',
 *                 'clientSecret' => 'mailru_client_secret',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @see http://api.mail.ru/sites/my/add
 * @see http://api.mail.ru/sites/my/
 * @see http://api.mail.ru/docs/reference/rest/users-getinfo/
 *
 * @author Roman Arkharov <arkharov@gmail.com>
 * @since 2.0
 */
class Mailru extends OAuth2
{
    /**
     * @inheritdoc
     */
    public $authUrl = 'https://connect.mail.ru/oauth/authorize';
    /**
     * @inheritdoc
     */
    public $tokenUrl = 'https://connect.mail.ru/oauth/token';
    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'http://www.appsmail.ru/platform/api?method=';

    public $api_method;

    /**
     * This method is overriden because mail.ru has custom  URL format
     *
     * @param string $apiSubUrl
     * @param string $method
     * @param array $params
     * @param array $headers
     * @return array
     * @throws Exception
     */
    public function api($apiSubUrl, $method = 'GET', array $params = [], array $headers = [])
    {
        $url = $this->apiBaseUrl . $apiSubUrl;

        $this->api_method = $apiSubUrl;

        $accessToken = $this->getAccessToken();
        if (!is_object($accessToken) || !$accessToken->getIsValid()) {
            throw new Exception('Invalid access token.');
        }
        return $this->apiInternal($accessToken, $url, $method, $params, $headers);
    }

    /**
     * Return clientId
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * return clientSecret
     *
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * Generate signature for API mail.ru
     *
     * @return string
     */
    public function getSignature(array $request_params, $secret_key) {
        $request_params['method'] = $this->api_method;

        ksort($request_params);
        $params = '';
        foreach ($request_params as $key => $value) {
            $params .= "$key=$value";
        }
        return md5($params . $secret_key);
    }

    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
        $attributes = $this->api('users.getInfo', 'GET');
        return $attributes[0];
    }

    /**
     * @inheritdoc
     */
    protected function apiInternal($accessToken, $url, $method, array $params, array $headers)
    {
        $params['uids'] = $accessToken->getParam('x_mailru_vid');

        $params['app_id'] = $this->getClientId();
        $params['secure'] = 1;

        $secret = $this->getClientSecret();
        $sig = $this->getSignature($params, $secret);

        $params['sig'] = $sig;

        return $this->sendRequest($method, $url, $params, $headers);
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'mailru';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return 'Mailru';
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

    /**
     * Override method of the class BaseOAuth, default method can't determine response type,
     * but I know that it is a JSON
     *
     * @param array $headers
     * @return string
     */
    protected function determineContentTypeByHeaders(array $headers)
    {
        return self::CONTENT_TYPE_JSON;
    }
}