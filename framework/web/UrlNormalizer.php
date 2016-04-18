<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\Object;

/**
 * UrlNormalizer normalizes urls for UrlRules.
 *
 */
class UrlNormalizer extends Object
{
    const TRAILING_SLASH_ADD = true;
    const TRAILING_SLASH_REMOVE = false;
    const TRAILING_SLASH_IGNORE = 'trailing-slash-ignore';

    const ACTION_REDIRECT = 'redirect';
    const ACTION_ROUTE = 'route';
    const ACTION_NONE = false;

    const STRATEGY_DISABLED = 'disabled';
    const STRATEGY_SAFE_REMOVE_TRAILING_SLASH = 'safe-remove-trailing-slash';
    const STRATEGY_REMOVE_TRAILING_SLASH = 'remove-trailing-slash';
    const STRATEGY_ADD_TRAILING_SLASH = 'add-trailing-slash';

    /**
     * @var boolean whether to collapse slashes in urls: 'site///index' => 'site/index'
     */
    public $collapseSlashes;
    /**
     * @var boolean|null use TRAILING_SLASH_* constants to set values; true/false supported
     * to simplify configuration
     */
    public $trailingSlash;
    /**
     * @var string action to perform if url was changed during normalization, see ACTION_* constants
     * for possible values
     */
    public $action;
    /**
     * @var string new route for action 'route', e. g. 'site/redirect'
     */
    public $route;
    /**
     * @var string bulk set of other properties via single config option, supported strategies
     * are STRATEGY_* constants
     */
    public $strategy;

    /**
     * @var string pathinfo after normalization
     */
    protected $pathInfoNormalized;
    /**
     * @var string original pathinfo
     */
    protected $pathInfoOrig;
    /**
     * @var string suffix for UrlRule
     */
    protected $suffix;

    /**
     * Prepares normalizer for work. Should be called before usage. Depends on current
     * context: UrlManager and UrlRule.
     * @param UrlManager $manager the URL manager
     * @param UrlRule $rule
     * @throws \Exception if config is not understood
     */
    public function prepare($manager, $rule) {
        $config = [];

        if ($this->strategy) switch ($this->strategy) {
            case self::STRATEGY_DISABLED:
                $config = [
                    'trailingSlash' => self::TRAILING_SLASH_IGNORE,
                    'collapseSlashes' => false,
                    'action' => self::ACTION_NONE
                ];
                break;
            case self::STRATEGY_SAFE_REMOVE_TRAILING_SLASH:
                // Removes trailing slash only if $suffix != '/'.
                // Redirects 'posts/' to 'posts' and 'posts.html/' to 'posts.html'
                // See issue #6498 for details.
                $suffix = (string)($rule->suffix === null ? $manager->suffix : $rule->suffix);

                $config = [
                    // if $suffix != '/', set normalizer to remove trailing slash, otherwise don't act.
                    'trailingSlash' =>  ($suffix != '/') ? self::TRAILING_SLASH_REMOVE : self::TRAILING_SLASH_IGNORE,
                    'collapseSlashes' => null,
                    'action' => self::ACTION_REDIRECT
                ];
                break;
            case self::STRATEGY_ADD_TRAILING_SLASH:
                $config = [
                    'trailingSlash' => self::TRAILING_SLASH_ADD,
                    'collapseSlashes' => true,
                    'action' => self::ACTION_REDIRECT
                ];
                break;
            case self::STRATEGY_REMOVE_TRAILING_SLASH:
                $config = [
                    'trailingSlash' => self::TRAILING_SLASH_REMOVE,
                    'collapseSlashes' => true,
                    'action' => self::ACTION_REDIRECT
                ];
                break;
            default:
                throw new \Exception('Unknown normalization strategy ' . $this->strategy);
        }

        foreach ($config as $name => $value) {
            // explicit property configuration overrides strategy
            $config[$name] = !is_null($this->$name) ? $this->$name : $value;
        }

        Yii::configure($this, $config);

        // set rule's $suffix if trailing slash is required (for createUrl())
        if ($this->trailingSlash === self::TRAILING_SLASH_ADD) {
            $this->suffix = '/';
        } else {
            $this->suffix = $rule->suffix;
        }
    }

    /**
     * Process request, remember original pathinfo and normalize it.
     * @param Request $request the Request
     */
    public function processRequest($request) {
        $this->pathInfoOrig = $request->getPathInfo();
        $this->pathInfoNormalized = $this->normalizePathInfo($request->getPathInfo());
    }

    /**
     * @return string normalized pathinfo
     */
    public function getPathInfoNormalized() {
        return $this->pathInfoNormalized;
    }

    /**
     * @return string suffix for UrlRule
     */
    public function getSuffix() {
        return $this->suffix;
    }

    /**
     * If pathinfo was changed, performs action if required.
     * @return array new/original route
     * @throws \Exception if configuration is wrong
     */
    public function getNormalizedRoute($origRoute) {
        if ($this->pathInfoNormalized != $this->pathInfoOrig) {
            switch (true) {
                case $this->action === self::ACTION_REDIRECT:
                    $e = new NormalizerActionException("Request should be normalized");
                    $e->setAction('redirect');
                    $e->setRedirectUrl($this->pathInfoNormalized);
                    $e->setOrigPathInfo($this->pathInfoOrig);
                    $e->setOrigRoute($origRoute);
                    throw $e;
                    break;
                case $this->action === self::ACTION_ROUTE:
                    // construct new route
                    $redirectParams = [
                        'redirectUrl' => $this->pathInfoNormalized,
                        'origPathInfo' => $this->pathInfoOrig,
                        'origRoute' => $origRoute,
                    ];
                    return [$this->route, $redirectParams];
                    break;
                case !$this->action:
                case $this->action === self::ACTION_NONE:
                    // no action
                    return $origRoute;
                default:
                    throw new \Exception("Unknown normalizer action " . $this->action);
                    break;
            }
        }

        return $origRoute;
    }

    /**
     * Normalizes supplied pathInfo by normalization rules.
     *
     * @param $pathInfo string original pathInfo
     * @return string normalized pathInfo
     */
    protected function normalizePathInfo($pathInfo) {
        if (!$pathInfo) {
            return $pathInfo; // nothing to normalize
        }

        if ($this->collapseSlashes) {
            $pathInfo = ltrim(preg_replace('#/+#', '/', $pathInfo), '/');
        }

        if ($this->trailingSlash !== self::TRAILING_SLASH_IGNORE) {
            if ($this->trailingSlash) {
                // add trailing slash
                if (substr($pathInfo, -1) != '/') {
                    $pathInfo .= '/';
                }
            } else {
                // remove trailing slash
                if (substr($pathInfo, -1) == '/') {
                    $pathInfo = rtrim($pathInfo, '/');
                }
            }
        }

        return $pathInfo;
    }


}
