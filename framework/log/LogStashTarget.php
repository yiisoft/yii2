<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2015/11/25
 * Time: 11:58
 */

namespace  yii\log;

use yii\web\Request;
use yii\helpers\VarDumper;
use \Yii;

/**
 * LogStashTarget records log messages in JSON format and can easily be imported into ELK.
 * The logs are saved into a file like FileTarget in JSON format, and you can configure your
 * logstash to monitor the log file and collect logs.
 *
 * Configure your logstash configuration file like this:
 * file {
 *   type => "yii"
 *   path => "/app/runtime/logs/stash.log"
 *   codec => "json"
 *   }
 *
 * Notice that the inherited property "logVars" does not work for this target, but some
 * context informations will be collected instead
 *
 *
 * @author QiHuajun <qihjun@gmail.com>
 */
class LogStashTarget extends FileTarget
{

    /**
     * @inheritdoc
     *
     * @return array
     */
    protected function getContextMessage()
    {
        return "";
    }

    /**
     * @inheritdoc
     *
     * @param array $message
     * @return string
     */
    public function getMessagePrefix($message)
    {
        if ($this->prefix !== null) {
            $prefix = call_user_func($this->prefix, $message);
            return [
                'prefix' => $prefix
            ];
        }

        if (Yii::$app === null) {
            return '';
        }

        $prefix = [];

        $request = Yii::$app->getRequest();
        $ip = $request instanceof Request ? $request->getUserIP() : '-';

        $prefix['ip'] = $ip;

        /* @var $user \yii\web\User */
        $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
        if ($user && ($identity = $user->getIdentity(false))) {
            $userID = $identity->getId();
        } else {
            $userID = '-';
        }

        $prefix['userID'] = $userID;

        /* @var $session \yii\web\Session */
        $session = Yii::$app->has('session', true) ? Yii::$app->get('session') : null;
        $sessionID = $session && $session->getIsActive() ? $session->getId() : '-';

        $prefix['sessionID'] = $sessionID;

        return $prefix;
    }


    /**
     * Formats a log message for display as a string.
     * @param array $message the log message to be formatted.
     * The message structure follows that in [[Logger::messages]].
     * @return string the formatted message
     */
    public function formatMessage($message)
    {
        list($text, $level, $category, $timestamp) = $message;

        $level = Logger::getLevelName($level);
        $data = [
            "time" => date('Y-m-d H:i:s', $timestamp),
            'level'=> $level,
            'category' => $category,
            'uri' => "",
            'route' => "",
            'params' => "",
        ];

        if(isset($_SERVER['REQUEST_URI'])){
            $data['uri'] = $_SERVER['REQUEST_URI'];
        }

        if(Yii::$app->request){
            list ($route, $params) = Yii::$app->request->resolve();
            $data['route'] = $route;


            $isConsole = Yii::$app->request->getIsConsoleRequest();
            if(!$isConsole){
                $data['params'] = http_build_query($params);
                $data['body'] = Yii::$app->request->getRawBody();
                $data['baseUrl'] = Yii::$app->request->getBaseUrl();
                $data['hostInfo'] = Yii::$app->request->getHostInfo();
                $data['isAjax'] = Yii::$app->request->getIsAjax();
                $data['method'] = Yii::$app->request->getMethod();
                $data['requestUrl'] = Yii::$app->request->getAbsoluteUrl();
            }else{
                $data['params'] = $params;
            }

        }


        if (!is_string($text)) {
            // exceptions may not be serializable if in the call stack somewhere is a Closure
            if ($text instanceof \Exception) {
                $data['file'] = $text->getFile();
                $data['line'] = $text->getLine();
                $data['code'] = $text->getCode();
                $data['trace']= $text->getTraceAsString();

                $text = $text->getMessage();
            }else {
                $text = VarDumper::export($text);
            }
        }

        $data['message'] = $text;

        $prefix = $this->getMessagePrefix($message);

        if(!empty($prefix)){
            $data =  array_merge($data,$prefix);
        }

        return json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    }
}