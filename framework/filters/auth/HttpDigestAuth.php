<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters\auth;

use Yii;
use yii\filters\auth\AuthMethod;
use yii\web\UnauthorizedHttpException;

/**
 * HttpDigestAuth is an action filter that supports the HTTP Digest authentication method.
 *
 * You may use HttpDigestAuth by attaching it as a behavior to a controller or module, like the following:
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'digestAuth' => [
 *             'class' => \common\filters\auth\HttpDigestAuth::className(),
 *         ],
 *     ];
 * }
 * ```
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 * @since 2.0
 */
class HttpDigestAuth extends AuthMethod
{
    /**
     * @var string the HTTP authentication realm
     */
    public $realm = 'api';
    /**
     * @var callable a PHP callable that will authenticate the user with the HTTP basic auth information.
     * The callable receives a username and a password as its parameters. It should return an identity object
     * that matches the username and password. Null should be returned if there is no such identity.
     *
     * The following code is a typical implementation of this callable:
     *
     * ```php
     * function ($data,$realm) {
     *     $user =  \app\models\User::findOne([
     *                  'username' => $data['username'],
     *               ]);
     *      if($user === null){
     *          return null;
     *      }
     *      $A1 = $user->password_digest;
     *      $A2 = md5(\Yii::$app->request->getMethod().':'.$data['uri']);
     *      $valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);
     *  
     *      if ($data['response'] != $valid_response){
     *          return null;
     *      }        
     *      return $user;
     * }
     * ```
     *
     * If this property is not set, the username information will be considered as an access token
     * while the password information will be ignored. The [[\yii\web\User::loginByAccessToken()]]
     * method will be called to authenticate and login the user.
     */
    public $auth;
    
    public $text;


    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $digest = $request->getAuthDigest();
        if($digest === null){
            return null;
        }
        
        // analyze the PHP_AUTH_DIGEST variable
        if (!$data = $this->http_digest_parse($digest)){
            $this->text = "Can't parse digest data";
            $this->handleFailure($response);
        }
        
        if($this->auth){
            $identity = call_user_func($this->auth, $data,  $this->realm);
            if ($identity !== null) {
                $user->setIdentity($identity);
            } else {
                $this->handleFailure($response);
            }
            return $identity;
        }else{
            $identity = $user->loginByAccessToken(['digest' => $data,'realm' => $this->realm], get_class($this));
            if ($identity === null) {
                $this->text = "Can't authenticate with digest data";
                $this->handleFailure($response);
            }
            return $identity;
        }
    }

    /**
     * @inheritdoc
     */
    public function handleFailure($response)
    {
        $response->getHeaders()->set('WWW-Authenticate', sprintf('Digest realm="%s",qop="auth",nonce="%s",opaque="%s"', $this->realm, uniqid(), md5($this->realm)));
        throw new UnauthorizedHttpException($this->text);
    }
    
    /**
     * 
     * @param string $txt Digest data available from Server variables
     */
    private function http_digest_parse($txt) {
        // protect against missing data
        $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
        $data = array();
        $keys = implode('|', array_keys($needed_parts));

        preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

        if(is_array($matches)){
            foreach ($matches as $m) {
                $data[$m[1]] = $m[3] ? $m[3] : $m[4];
                unset($needed_parts[$m[1]]);
            }
        }

        return $needed_parts ? false : $data;
    }
}
