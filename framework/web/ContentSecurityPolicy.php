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
 * Class ContentSecurityPolicy
 * This component will release the Content Security Policty header based on 3 approaches:
 * - static src directives based approach
 * - per-request dynamic src directives based approach
 * - per-content-hash-calc src directives based approach
 *
 * The static src approach
 * In the src directives you can store, for example, a domain, a protocol some special keyword between single quote
 * like 'self' or 'none', please refer to:
 * https://www.w3.org/TR/CSP2/#directives
 *
 * Per-request approach
 * To allows a snippet of javascript to be added to the page, the attribute "nonce" will be added to the &lt;script&gt;
 * tag. The value of this attribute will change for each request and report in the CSP-header coherently.
 * Using the {@link Html#script} helper function will do the job
 *
 * To allows a snippet of CSS to be added to the page, the attribute "nonce" will be added to the &lt;style&gt; tag.
 * The value of this attribute will change for each request and report in the CSP-header coherently.
 * Using the {@link Html#style} helper function will do the job
 *
 * Per-content approach
 * To allows a snippet of javascript to be added to the page, the hash of the snippet will be calculated.
 * The value of the hash and will be added to the CSP-header.
 * Using the {@link View#registerJs} helper function will do the job
 *
 * To allows a snippet of CSS to be added to the page, the hash of the snippet will be calculated.
 * The value of the hash and will be added to the CSP-header.
 * Using the {@link View#registerCss} helper function will do the job
 *
 *
 * here's a configutation example:
 *    'components' => [
 *
 *        // other confs
 *        'csp' => [
 *            'class' => 'yii\web\ContentSecurityPolicy',
 *            'addNonceScript' => true,
 *            'addNonceStyle' => true,
 *            'addKeys' => true,
 *            'csp' => [
 *                'default-src' => "'self'",
 *                'img-src' => "'self' data:",
 *                'script-src' => "'self' www.google-analytics.com",
 *                'style-src' => "'self' maxcdn.bootstrapcdn.com",
 *            ],
 *            'selectedHashAlgorithms'=>[
 *                "sha256",
 *            ],
 *        ],
 *    ]
 *
 * @package yii\web
 */
class ContentSecurityPolicy extends Component
{
    /**
     * As described by https://www.w3.org/TR/CSP2/#script-src-the-nonce-attribute
     * the new attribute "nonce" was add to SCRIPT and to STYLE elements
     */
    const CSP_TAG_ATTRIBUTE = 'nonce';

    /**
     * default source directive
     * https://www.w3.org/TR/CSP2/#directive-default-src
     */
    const DIRECTIVE_DEFAULT_SRC ='default-src';

    /**
     *  List of directives names
     *  https://www.w3.org/TR/CSP2/#directives
     */
    const DIRECTIVE_CHILD_SRC ='child-src';
    const DIRECTIVE_CONNECT_SRC ='connect-src';
    const DIRECTIVE_FONT_SRC ='font-src';
    const DIRECTIVE_IMG_SRC ='img-src';
    const DIRECTIVE_MEDIA_SRC ='media-src';
    const DIRECTIVE_OBJECT_SRC ='object-src';
    const DIRECTIVE_SCRIPT_SRC ='script-src';
    const DIRECTIVE_STYLE_SRC ='style-src';
    const DIRECTIVE_FORM_ACTION ='form-action';
    const DIRECTIVE_FRAME_ANCESTORS ='frame-ancestors';
    const DIRECTIVE_PLUGIN_TYPES ='plugin-types';
    const DIRECTIVE_SANDBOX ='sandbox';
    const DIRECTIVE_BASE_URI ='base-uri';
    const DIRECTIVE_REPORT ='report-uri';

    /**
     * frame source directive deprecated in CSP2
     * https://www.w3.org/TR/CSP2/#directive-frame-src
     * @deprecated
     * @use child-src directive
     */
    const DIRECTIVE_FRAME_SRC ='frame-src';
    
    
    /**
     * Enable the nonce-source for script
     * https://www.w3.org/TR/CSP2/#script-src-nonce-usage
     * Note that 'unsafe-inline' is ignored if either a hash or nonce value is present in the source list.
     * when set to TRUE should add the nonce attribute to ALL <SCRIPT> tags when possible this process was
     * automated but some effort will be done on developer side to use the framework tools to add javascript
     * resources
     * @var bool
     */
    public $addNonceScript=true;

    /**
     * Enable the nonce-source for style
     * https://www.w3.org/TR/CSP2/#style-src-nonce-usage
     * Note that 'unsafe-inline' is ignored if either a hash or nonce value is present in the source list.
     * when set to TRUE should add the nonce attribute to ALL <STYLE> tags when possible this process was
     * automated but some effort will be done on developer side to use the framework tools to add CSS
     * @var bool
     */
    public $addNonceStyle=true;


    /**
     * Enable the hash-source for scripts
     * https://www.w3.org/TR/CSP2/#script-src-hash-usage
     * Note that 'unsafe-inline' is ignored if either a hash or nonce value is present in the source list.
     * when set to TRUE should calc the hash to ALL javascript source add by {@Link View#registerJs}
     * some effort will be done on developer side to using the framework tools (registerJs) to add javascript
     * snippets
     * @var bool
     */
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
     * can be specified one or more hash algorithms
     * will be calculated an hash for snippet for algorithm
     * @var array
     */
    public  $selectedHashAlgorithms = [
        self::HASH_SHA_256,
        self::HASH_SHA_384,
        self::HASH_SHA_512,
    ];
    protected $hashKeysScript=[];
    protected $hashKeysStyle=[];

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
    public function getHashKeysScript(View $view=null)
    {
        if(count($this->hashKeysScript)==0 && $view !== null && is_array($view->js )&& count($view->js )>0 ){
            foreach ($this->selectedHashAlgorithms as $algorithm ){
                array_walk_recursive(Yii::$app->getView()->js,function ($element) use ($algorithm){
                    $this->hashKeysScript[] = sprintf("'%s-%s'",$algorithm,base64_encode(hash($algorithm, $element, true)));
                });
            }
        }
        return $this->hashKeysScript;
    }

    /**
     * Calc the HASH of the scripts previously registered with registerJs
     * @param View|null $view
     * @return array
     */
    public function getHashKeysStyle(View $view=null)
    {
        if(count($this->hashKeysStyle)==0 && $view !== null && is_array($view->css )&& count($view->css )>0 ){
            foreach ($this->selectedHashAlgorithms as $algorithm ){
                array_walk_recursive(Yii::$app->getView()->css,function ($element) use ($algorithm){
                    $this->hashKeysStyle[] = sprintf("'%s-%s'",$algorithm,base64_encode(hash($algorithm, $element, true)));
                });
            }
        }
        return $this->hashKeysStyle;
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
     * Set the csp header
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
            $this->csp[self::DIRECTIVE_SCRIPT_SRC] .= " ".implode(" ",$this->getHashKeysScript());
        }
        if($this->addKeys){
            $this->csp[self::DIRECTIVE_STYLE_SRC] .= " ".implode(" ",$this->getHashKeysStyle());
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

    /**
     * add CSP attributes to the HtmlHelper Tag $options
     * @param $options[]
     * @return $options[]
     */
    public function populateStyleTagOptions($options)
    {
        if (!isset($options[self::CSP_TAG_ATTRIBUTE])) {
            $options[self::CSP_TAG_ATTRIBUTE] = self::getToken();
        }
        return $options;
    }

    /**
     * add CSP attributes to the HtmlHelper Tag $options
     * @param  $options[]
     * @return $options[]
     */
    public function populateScriptTagOptions($options)
    {
        if (!isset($options[self::CSP_TAG_ATTRIBUTE])) {
            $options[self::CSP_TAG_ATTRIBUTE] = self::getToken();
        }
        return $options;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if(!isset($this->csp[self::DIRECTIVE_STYLE_SRC])){
            $this->csp[self::DIRECTIVE_STYLE_SRC]=null;
        }
        if(!isset($this->csp[self::DIRECTIVE_SCRIPT_SRC])){
            $this->csp[self::DIRECTIVE_SCRIPT_SRC]=null;
        }
        Yii::$app->view->on(
            View::EVENT_END_PAGE,
            function ($event){
                $this->getHashKeysScript($event->sender);
                $this->getHashKeysStyle($event->sender);

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