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
use yii\web\Request;
use yii\web\Response;
use yii\web\UnsupportedMediaTypeHttpException;

/**
 * ContentNegotiator 支持响应格式协商和应用程序语言协商。
 *
 * 如果指定 [[formats|supported formats]] 属性， ContentNegotiator 将支持
 * 基于 GET 参数 [[formatParam]] 的值和`Accept` HTTP header 的响应格式协商。
 * 如果找到匹配，[[Response::format]] 属性将设置为所选的格式。
 * [[Response::acceptMimeType]] 以及 [[Response::acceptParams]] 也将相应更新。
 *
 * 如果指定了 [[languages|supported languages]]， ContentNegotiator 程序将支持
 * 基于 GET 参数 [[languageParam]] 和`Accept-Language` HTTP header 的值进行应用程序语言协商。
 * 如果找到匹配，[[\yii\base\Application::language]] 属性将设置为选择的语言。
 *
 * 您可以将 ContentNegotiator 程序用作引导组件和操作筛选器。
 *
 * 下面的代码显示如何将 ContentNegotiator 程序用作引导组件。注意在这种情况下，
 * 内容协商适用于整个应用程序。
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
 * 下面的代码显示了如何在控制器或模块中将 ContentNegotiator 程序用作操作筛选器。
 * 在这种情况下，内容协商结果仅适用于相应的控制器或模块，或者
 * 如果您配置过滤器的 `only` 或`except` 属性甚至还可以配置特定的操作。
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
     * @var string 指定响应格式的 GET 参数的名称。
     * 请注意如果 [[formats]] 中不存在指定的格式，
     * 则将引发 [[UnsupportedMediaTypeHttpException]] 异常。如果参数值为空或此属性为空，
     * 响应格式将仅根据 `Accept` HTTP header 确定。
     * @see formats
     */
    public $formatParam = '_format';
    /**
     * @var string 指定 [[\yii\base\Application::language|application language]] 的 GET 参数的名称。
     * 请注意，如果指定的语言与 [[languages]] 中的任何一种不匹配，将使用 [[languages]] 的
     * 第一种语言。如果参数值为空或此属性为空，
     * 应用程序语言将仅根据`Accept-Language` HTTP header 确定。
     * @see languages
     */
    public $languageParam = '_lang';
    /**
     * @var array 支持的响应格式列表。键是MIME类型（例如 `application/json`）
     * 而值是相应的格式（例如 `html`，`json`）
     * 必须按照 [[\yii\web\Response::formatters]] 中的声明予以支持。
     *
     * 如果此属性为空或未设置，则将跳过响应格式协商。
     */
    public $formats;
    /**
     * @var array 支持的语言列表。数组键是受支持的语言变体（例如，`en-GB`，`en-US`），
     * 数组值是应用程序识别的相应语言代码（例如。 `en`, `de`）。
     *
     * 并非总是需要阵列密钥。当数组值没有键时，所请求的语言的匹配
     * 将基于语言回退机制。例如，值 `en` 将要与 `en`，`en_US`，`en-US`，`en-GB`，等匹配。
     *
     * 如果此属性为空或未设置，则将跳过语言协商。
     */
    public $languages;
    /**
     * @var Request 当前请求。如果未设置，将使用 `request` 应用程序组件。
     */
    public $request;
    /**
     * @var Response 要发送的响应。如果未设置，将使用 `response` 应用程序组件。
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
     * 协商响应格式和应用程序语言。
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
     * 协商响应格式。
     * @param Request $request
     * @param Response $response
     * @throws BadRequestHttpException 如果接收到用于 GET 参数的数组 [[formatParam]]。
     * @throws UnsupportedMediaTypeHttpException 如果没有接受任何请求的内容类型。
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

            throw new UnsupportedMediaTypeHttpException('The requested response format is not supported: ' . $format);
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

        throw new UnsupportedMediaTypeHttpException('None of your requested content types is supported.');
    }

    /**
     * 协商应用程序语言。
     * @param Request $request
     * @return string 所选语言
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
     * 返回一个值该值指示请求的语言是否与支持的语言匹配。
     * @param string $requested 请求的语言代码
     * @param string $supported 支持的语言代码
     * @return bool 是否支持所请求的语言
     */
    protected function isLanguageSupported($requested, $supported)
    {
        $supported = str_replace('_', '-', strtolower($supported));
        $requested = str_replace('_', '-', strtolower($requested));
        return strpos($requested . '-', $supported . '-') === 0;
    }
}
