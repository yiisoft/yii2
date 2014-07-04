<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters\auth;

use Yii;
use yii\web\UnauthorizedHttpException;

/**
 * HttpWsseAuth is an action filter that supports the HTTP WSSE authentication method.
 *
 * You may use HttpWsseAuth by attaching it as a behavior to a controller or module, like the following:
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'wsseAuth' => [
 *             'class' => \yii\filters\auth\HttpWsseAuth::className(),
 *         ],
 *     ];
 * }
 * ```
 * the method findIdentityByAccessToken should be adapted like this to handle WSSE :
 *
 * ```php
 * public static function findIdentityByAccessToken($token, $type=null)
 * {
 *     $identity = null;
 *     if($type == \yii\filters\auth\HttpWsseAuth::className()) {
 *         if(static::checkNonce($token['Nonce']) && static::checkTime($token['Created'])) {
 *            $user = static::findOne(['username' => $token['Username']]);
 *            if(($user !== null) && HttpWsseAuth::validateDigest($token, $user->password)){
 *                 $identity = $user;
 *             }
 *         }
 *     } else {
 *         $identity = static::findOne(['access_token' => $token]);
 *     }
 *     return $identity;
 * }
 *
 * // *Created* date should be checked and accepted or rejected to avoid replay attacks.
 * // For example a request should be valid for server time +/- 15mn
 * public static function checkNonce($created)
 * {
 *      $requestTimeStamp = strtotime($created);
 *      $currentTime = time();
 *      $drift = abs($requestTimeStamp - $currentTime)
 *      return ($drift < (15 * 60));
 * }
 *
 * // *Nonce* should be checked and accepted or rejected to avoid replay attacks.
 * // For example a nonce should be unique during the validity of the request (timeframe)
 * public static function checkNonce($nonce)
 * {
 *     return true;
 * }
 *
 * ```
 *
 *
 * @author Philippe Gaultier <pgaultier@gmail.com>
 * @since 2.0
 */
class HttpWsseAuth extends AuthMethod
{
    /**
     * @var string the HTTP authentication realm
     */
    public $realm = 'api';

    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $authToken = $this->extractWsseData($request);
        if ($authToken !== null) {
            $identity = $user->loginByAccessToken($authToken, get_class($this));
            if ($identity === null) {
                $this->handleFailure($response);
            }
            return $identity;
        }
        return null;
    }

    /**
     * Extract Wsse data from request headers
     * @param \yii\web\Request $request Current Request
     * @return array token filled with user information
     */
    public function extractWsseData($request)
    {
        $authToken = null;
        $authType = $request->getHeaders()->get('Authorization');
        if (isset($_SERVER['HTTP_X_WSSE']) && preg_match('/WSSE\s+profile="UsernameToken"/', $authType) > 0) {
            $authHeader = $_SERVER['HTTP_X_WSSE'];
            $res = preg_match_all('/\s+([^=]+)="([^"]*)"[,]?/m', $authHeader, $matches);
            if ($res>0) {
                $authToken = [];
                for ($i=0; $i<$res; $i++) {
                    if (in_array($matches[1][$i], ['Username', 'Created', 'Nonce', 'PasswordDigest'])) {
                        $authToken[$matches[1][$i]] = $matches[2][$i];
                    }
                }
            }
        }
        return $authToken;
    }

    /**
     * Check if the digest is valid using WSSE algo
     * @param array $token Original data from the request headers
     * @param string $password the user password used to generate the digest
     * @return boolean true if the digest is OK false otherwise
     */
    public static function validateDigest($token, $password)
    {
        $digest = base64_encode(sha1(base64_decode($token['Nonce']).$token['Created'].$password, true));
        return ($digest === $token['PasswordDigest']);
    }
    /**
     * @inheritdoc
     */
    public function handleFailure($response)
    {
        $response->getHeaders()->set('WWW-Authenticate', 'WSSE realm="'.$this->realm.'", profile="UsernameToken"');
        throw new UnauthorizedHttpException('You are requesting with an invalid WSSE token.');
    }
}
