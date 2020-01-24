<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters;

use Yii;
use yii\base\ActionFilter;
use yii\base\BootstrapInterface;
use yii\web\BadRequestHttpException;
use yii\web\NotAcceptableHttpException;
use yii\web\Request;
use yii\web\Response;

/**
 * ContentNegotiator supports response format negotiation and application language negotiation.
 *
 * When the [[formats|supported formats]] property is specified, ContentNegotiator will support response format
 * negotiation based on the value of the GET parameter [[formatParam]] and the `Accept` HTTP header.
 * If a match is found, the [[Response::format]] property will be set as the chosen format.
 * The [[Response::acceptMimeType]] as well as [[Response::acceptParams]] will also be updated accordingly.
 *
 * When the [[languages|supported languages]] is specified, ContentNegotiator will support application
 * language negotiation based on the value of the GET parameter [[languageParam]] and the `Accept-Language` HTTP header.
 * If a match is found, the [[\yii\base\Application::language]] property will be set as the chosen language.
 *
 * You may use ContentNegotiator as a bootstrapping component as well as an action filter.
 *
 * The following code shows how you can use ContentNegotiator as a bootstrapping component. Note that in this case,
 * the content negotiation applies to the whole application.
 *
 * ```php
 * // in application configuration
 * use yii\web\Response;
 *
 * return [
 *     'bootstrap' => [
 *         [
 *             'class' => 'yii\filters\ContentNegotiator',
 *             'formats' => [
 *                 'application/json' => Response::FORMAT_JSON,
 *                 'application/xml' => Response::FORMAT_XML,
 *             ],
 *             'languages' => [
 *                 'en',
 *                 'de',
 *             ],
 *         ],
 *     ],
 * ];
 * ```
 *
 * The following code shows how you can use ContentNegotiator as an action filter in either a controller or a module.
 * In this case, the content negotiation result only applies to the corresponding controller or module, or even
 * specific actions if you configure the `only` or `except` property of the filter.
 *
 * ```php
 * use yii\web\Response;
 *
 * public function behaviors()
 * {
 *     return [
 *         [
 *             'class' => 'yii\filters\ContentNegotiator',
 *             'only' => ['view', 'index'],  // in a controller
 *             // if in a module, use the following IDs for user actions
 *             // 'only' => ['user/view', 'user/index']
 *             'formats' => [
 *                 'application/json' => Response::FORMAT_JSON,
 *             ],
 *             'languages' => [
 *                 'en',
 *                 'de',
 *             ],
 *         ],
 *     ];
 * }
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ContentNegotiator extends ActionFilter implements BootstrapInterface
{
    /**
     * @var string the name of the GET parameter that specifies the response format.
     * Note that if the specified format does not exist in [[formats]], a [[NotAcceptableHttpException]]
     * exception will be thrown.  If the parameter value is empty or if this property is null,
     * the response format will be determined based on the `Accept` HTTP header only.
     * @see formats
     */
    public $formatParam = '_format';
    /**
     * @var string the name of the GET parameter that specifies the [[\yii\base\Application::$language|application language]].
     * Note that if the specified language does not match any of [[languages]], the first language in [[languages]]
     * will be used. If the parameter value is empty or if this property is null,
     * the application language will be determined based on the `Accept-Language` HTTP header only.
     * @see languages
     */
    public $languageParam = '_lang';
    /**
     * @var array list of supported response formats. The keys are MIME types (e.g. `application/json`)
     * while the values are the corresponding formats (e.g. `html`, `json`) which must be supported
     * as declared in [[\yii\web\Response::$formatters]].
     *
     * If this property is empty or not set, response format negotiation will be skipped.
     */
    public $formats;
    /**
     * @var array a list of supported languages. The array keys are the supported language variants (e.g. `en-GB`, `en-US`),
     * while the array values are the corresponding language codes (e.g. `en`, `de`) recognized by the application.
     *
     * Array keys are not always required. When an array value does not have a key, the matching of the requested language
     * will be based on a language fallback mechanism. For example, a value of `en` will match `en`, `en_US`, `en-US`, `en-GB`, etc.
     *
     * If this property is empty or not set, language negotiation will be skipped.
     */
    public $languages;
    /**
     * @var Request the current request. If not set, the `request` application component will be used.
     */
    public $request;
    /**
     * @var Response the response to be sent. If not set, the `response` application component will be used.
     */
    public $response;


    /**
     * {@inheritdoc}
     */
    public function bootstrap($app)
    {
        $this->negotiate();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $this->negotiate();
        return true;
    }

    /**
     * Negotiates the response format and application language.
     */
    public function negotiate()
    {
        $request = $this->request ?: Yii::$app->getRequest();
        $response = $this->response ?: Yii::$app->getResponse();
        if (!empty($this->formats)) {
            if (\count($this->formats) > 1) {
                $response->getHeaders()->add('Vary', 'Accept');
            }
            $this->negotiateContentType($request, $response);
        }
        if (!empty($this->languages)) {
            if (\count($this->languages) > 1) {
                $response->getHeaders()->add('Vary', 'Accept-Language');
            }
            Yii::$app->language = $this->negotiateLanguage($request);
        }
    }

    /**
     * Negotiates the response format.
     * @param Request $request
     * @param Response $response
     * @throws BadRequestHttpException if an array received for GET parameter [[formatParam]].
     * @throws NotAcceptableHttpException if none of the requested content types is accepted.
     */
    protected function negotiateContentType($request, $response)
    {
        if (!empty($this->formatParam) && ($format = $request->get($this->formatParam)) !== null) {
            if (is_array($format)) {
                throw new BadRequestHttpException("Invalid data received for GET parameter '{$this->formatParam}'.");
            }

            if (in_array($format, $this->formats)) {
                $response->format = $format;
                $response->acceptMimeType = null;
                $response->acceptParams = [];
                return;
            }

            throw new NotAcceptableHttpException('The requested response format is not supported: ' . $format);
        }

        $types = $request->getAcceptableContentTypes();
        if (empty($types)) {
            $types['*/*'] = [];
        }

        foreach ($types as $type => $params) {
            if (isset($this->formats[$type])) {
                $response->format = $this->formats[$type];
                $response->acceptMimeType = $type;
                $response->acceptParams = $params;
                return;
            }
        }

        foreach ($this->formats as $type => $format) {
            $response->format = $format;
            $response->acceptMimeType = $type;
            $response->acceptParams = [];
            break;
        }

        if (isset($types['*/*'])) {
            return;
        }

        throw new NotAcceptableHttpException('None of your requested content types is supported.');
    }

    /**
     * Negotiates the application language.
     * @param Request $request
     * @return string the chosen language
     */
    protected function negotiateLanguage($request)
    {
        if (!empty($this->languageParam) && ($language = $request->get($this->languageParam)) !== null) {
            if (is_array($language)) {
                // If an array received, then skip it and use the first of supported languages
                return reset($this->languages);
            }
            if (isset($this->languages[$language])) {
                return $this->languages[$language];
            }
            foreach ($this->languages as $key => $supported) {
                if (is_int($key) && $this->isLanguageSupported($language, $supported)) {
                    return $supported;
                }
            }

            return reset($this->languages);
        }

        foreach ($request->getAcceptableLanguages() as $language) {
            if (isset($this->languages[$language])) {
                return $this->languages[$language];
            }
            foreach ($this->languages as $key => $supported) {
                if (is_int($key) && $this->isLanguageSupported($language, $supported)) {
                    return $supported;
                }
            }
        }

        return reset($this->languages);
    }

    /**
     * Returns a value indicating whether the requested language matches the supported language.
     * @param string $requested the requested language code
     * @param string $supported the supported language code
     * @return bool whether the requested language is supported
     */
    protected function isLanguageSupported($requested, $supported)
    {
        $supported = str_replace('_', '-', strtolower($supported));
        $requested = str_replace('_', '-', strtolower($requested));
        return strpos($requested . '-', $supported . '-') === 0;
    }
}
