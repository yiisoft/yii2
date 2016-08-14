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
    /**
     * @var boolean whether to collapse slashes in urls: 'site///index' => 'site/index'
     */
    public $collapseSlashes;
    /**
     * @var boolean|null true/false - add/remove trailing slash, null - do nothing
     */
    public $trailingSlash;
    /**
     * @var string action to perform if url was changed during normalization: 'route', 'redirect' or 'none'
     */
    public $action;
    /**
     * @var string new route for action 'route', e. g. 'site/redirect'
     */
    public $route;
    /**
     * @var string bolk set of other properties via single config option, strategies supported:
     * 'add-trailing-slash', 'remove-trailing-slash', 'safe-remove-trailing-slash', 'disabled'
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
        if ($this->strategy) switch ($this->strategy) {
            case 'disabled':
                $this->trailingSlash = null;
                $this->collapseSlashes = false;
                $this->action = false;
                break;
            case 'safe-remove-trailing-slash':
                // Removes trailing slash only if $suffix != '/'.
                // Redirects 'posts/' to 'posts' and 'posts.html/' to 'posts.html'
                // See issue #6498 for details.
                $suffix = (string)($rule->suffix === null ? $manager->suffix : $rule->suffix);

                // if $suffix != '/', set normalizer to remove trailing slash, otherwise don't act.
                $this->trailingSlash = ($suffix != '/') ? false : null;

                $this->collapseSlashes = false;
                $this->action = 'redirect';
                break;
            case 'add-trailing-slash':
                $this->trailingSlash = true;
                $this->collapseSlashes = true;
                $this->action = 'redirect';
                break;
            case 'remove-trailing-slash':
                $this->trailingSlash = false;
                $this->collapseSlashes = true;
                $this->action = 'redirect';
                break;
            default:
                throw new \Exception('Unknown normalization strategy ' . $this->strategy);
        }

        // set rule's $suffix if trailing slash is required (for createUrl())
        if ($this->trailingSlash) {
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
            switch ($this->action) {
                case 'redirect':
                    $e = new NormalizerActionException("Request should be normalized");
                    $e->setAction('redirect');
                    $e->setRedirectUrl($this->pathInfoNormalized);
                    $e->setOrigPathInfo($this->pathInfoOrig);
                    $e->setOrigRoute($origRoute);
                    throw $e;
                    break;
                case 'route':
                    // construct new route
                    $redirectParams = [
                        'redirectUrl' => $this->pathInfoNormalized,
                        'origPathInfo' => $this->pathInfoOrig,
                        'origRoute' => $origRoute,
                    ];
                    return [$this->route, $redirectParams];
                    break;
                case 'none':
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

        if (!is_null($this->trailingSlash)) {
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
