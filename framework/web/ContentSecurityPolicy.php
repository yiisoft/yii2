<?php
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 4/8/17
 * Time: 12:34 AM
 */

namespace yii\web;

use Yii;
use yii\base\Component;

/**
 * Class ResponseCspNonce
 * @package yii\web
 */
class ContentSecurityPolicy extends Component
{
    const CSP_TAG_ATTRIBUTE = 'nonce';

    // main src directive
    const DIRECTIVE_DEFAULT_SRC ='default-src';

    // specialized src directives
    const DIRECTIVE_CHILD_SRC ='child-src';
    const DIRECTIVE_CONNECT_SRC ='connect-src';
    const DIRECTIVE_FONT_SRC ='font-src';
    const DIRECTIVE_IMG_SRC ='img-src';
    const DIRECTIVE_MEDIA_SRC ='media-src';
    const DIRECTIVE_OBJECT_SRC ='object-src';
    const DIRECTIVE_SCRIPT_SRC ='script-src';
    const DIRECTIVE_STYLE_SRC ='style-src';

    // other directives
    const DIRECTIVE_FORM_ACTION ='form-action';
    const DIRECTIVE_FRAME_ANCESTORS ='frame-ancestors';
    const DIRECTIVE_PLUGIN_TYPES ='plugin-types';
    const DIRECTIVE_SANDBOX ='sandbox';
    const DIRECTIVE_BASE_URI ='base-uri';
    const DIRECTIVE_REPORT ='report-uri';

    // deprecated
    const DIRECTIVE_FRAME_SRC ='frame-src';
    
    
    // Note that 'unsafe-inline' is ignored if either a hash or nonce value is present in the source list.
    public $addNonceScript=true;

    // Note that 'unsafe-inline' is ignored if either a hash or nonce value is present in the source list.
    public $addNonceStyle=true;
    public $addKeys=true;


    /**
     * SHA256 hash
     * @var string
     */
    const HASH_SHA_256 = 'sha256';
    /**
     * SHA384 hash.
     * @var string
     */
    const HASH_SHA_384 = 'sha384';
    /**
     * SHA512 hash.
     * @var string
     */
    const HASH_SHA_512 = 'sha512';
    /**
     * selected hashes.
     * @var array
     */
    public  $selectedHashAlgorithms = [
        self::HASH_SHA_256,
        self::HASH_SHA_384,
        self::HASH_SHA_512,
    ];
    protected $hashKeys=[];

    public $csp=[
        self::DIRECTIVE_DEFAULT_SRC => "'self'",
        self::DIRECTIVE_CHILD_SRC => null,
        self::DIRECTIVE_CONNECT_SRC => null,
        self::DIRECTIVE_FONT_SRC => null,
        self::DIRECTIVE_IMG_SRC => null,
        self::DIRECTIVE_MEDIA_SRC => null,
        self::DIRECTIVE_OBJECT_SRC => null,
        self::DIRECTIVE_SCRIPT_SRC => null,
        self::DIRECTIVE_STYLE_SRC => null,
        self::DIRECTIVE_FRAME_SRC => null,
        self::DIRECTIVE_BASE_URI => null,
        self::DIRECTIVE_FORM_ACTION => null,
        self::DIRECTIVE_FRAME_ANCESTORS => null,
        self::DIRECTIVE_PLUGIN_TYPES => null,
        self::DIRECTIVE_REPORT => null,
        self::DIRECTIVE_SANDBOX => null,
    ];

    /**
     * contain the CSP token
     * @var string
     */
    protected static $token;


    /**
     * Calc the HASH of the scripts previously registered with registerJs
     * @param View|null $view
     * @return array
     */
    public function getHashKeys(View $view=null)
    {
        if(count($this->hashKeys)==0 && $view !== null && is_array($view->js )&& count($view->js )>0 ){
            foreach ($this->selectedHashAlgorithms as $algorithm ){
                array_walk_recursive(Yii::$app->getView()->js,function ($element) use ($algorithm){
                    $this->hashKeys[] = sprintf("'%s-%s'",$algorithm,base64_encode(hash($algorithm, $element, true)));
                });
            }
        }
        return $this->hashKeys;
    }

    /**
     * Singleton of token
     * @return string
     */
    public static function getToken()
    {
        if(self::$token === null){
            self::$token= \Yii::$app->security->generateRandomString();
        }
        return self::$token;
    }

    /**
     * @param Response $response
     */
    public function run(Response $response)
    {
        $header="";
        $separator="";
        if($this->addNonceStyle) {
            $this->csp[self::DIRECTIVE_STYLE_SRC] .= " 'nonce-" . self::getToken() . "'";
        }
        if($this->addNonceScript){
            $this->csp[self::DIRECTIVE_SCRIPT_SRC] .= " 'nonce-".self::getToken()."'";
        }
        if($this->addKeys){
            $this->csp[self::DIRECTIVE_SCRIPT_SRC] .= " ".implode(" ",$this->getHashKeys());
        }
        foreach ($this->csp as $k => $v){
            if($v===null){
                continue;
            }
            $header.="$separator$k $v";
            $separator="; ";
        }

        $response->headers->set('Content-Security-Policy',$header);
    }

    public function populateStyleTagOptions($options)
    {
        if (!isset($options[self::CSP_TAG_ATTRIBUTE])) {
            $options[self::CSP_TAG_ATTRIBUTE] = self::getToken();
        }
        return $options;
    }

    public function populateScriptTagOptions($options)
    {
        if (!isset($options[self::CSP_TAG_ATTRIBUTE])) {
            $options[self::CSP_TAG_ATTRIBUTE] = self::getToken();
        }
        return $options;
    }

    public function init()
    {
        parent::init();
        Yii::$app->view->on(
            View::EVENT_END_PAGE,
            function ($event){
                $this->getHashKeys($event->sender);

            }
        );
        Yii::$app->response->on(
            Response::EVENT_AFTER_PREPARE,
            function ($data) {
                $this->run($data->sender);
            }
        );
    }


}