<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * View represents a view object in the MVC pattern.
 *
 * View provides a set of methods (e.g. [[render()]]) for rendering purpose.
 *
 * View is configured as an application component in [[\yii\base\Application]] by default.
 * You can access that instance via `Yii::$app->view`.
 *
 * You can modify its configuration by adding an array to your application config under `components`
 * as it is shown in the following example:
 *
 * ```php
 * 'view' => [
 *     'theme' => 'app\themes\MyTheme',
 *     'renderers' => [
 *         // you may add Smarty or Twig renderer here
 *     ]
 *     // ...
 * ]
 * ```
 *
 * For more details and usage information on View, see the [guide article on views](guide:structure-views).
 *
 * @property \yii\web\AssetManager $assetManager The asset manager. Defaults to the "assetManager" application
 * component.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class View extends \yii\base\View implements ViewInterface
{
    /**
     * @event Event an event that is triggered by [[beginBody()]].
     */
    const EVENT_BEGIN_BODY = 'beginBody';
    /**
     * @event Event an event that is triggered by [[endBody()]].
     */
    const EVENT_END_BODY = 'endBody';
    /**
     * The location of registered JavaScript code block or files.
     * This means the location is in the head section.
     */
    const POS_HEAD = 1;
    /**
     * The location of registered JavaScript code block or files.
     * This means the location is at the beginning of the body section.
     */
    const POS_BEGIN = 2;
    /**
     * The location of registered JavaScript code block or files.
     * This means the location is at the end of the body section.
     */
    const POS_END = 3;
    /**
     * The location of registered JavaScript code block.
     * This means the JavaScript code block will be enclosed within `jQuery(document).ready()`.
     */
    const POS_READY = 4;
    /**
     * The location of registered JavaScript code block.
     * This means the JavaScript code block will be enclosed within `jQuery(window).load()`.
     */
    const POS_LOAD = 5;
    /**
     * This is internally used as the placeholder for receiving the content registered for the head section.
     */
    const PH_HEAD = '<![CDATA[YII-BLOCK-HEAD]]>';
    /**
     * This is internally used as the placeholder for receiving the content registered for the beginning of the body section.
     */
    const PH_BODY_BEGIN = '<![CDATA[YII-BLOCK-BODY-BEGIN]]>';
    /**
     * This is internally used as the placeholder for receiving the content registered for the end of the body section.
     */
    const PH_BODY_END = '<![CDATA[YII-BLOCK-BODY-END]]>';

    /**
     * @var AssetBundle[] list of the registered asset bundles. The keys are the bundle names, and the values
     * are the registered [[AssetBundle]] objects.
     * @see registerAssetBundle()
     */
    public $assetBundles = [];
    /**
     * @var string the page title
     */
    public $title;
    /**
     * @var array the registered meta tags.
     * @see registerMetaTag()
     */
    public $metaTags = [];
    /**
     * @var array the registered link tags.
     * @see registerLinkTag()
     */
    public $linkTags = [];
    /**
     * @var array the registered CSS code blocks.
     * @see registerCss()
     */
    public $css = [];
    /**
     * @var array the registered CSS files.
     * @see registerCssFile()
     */
    public $cssFiles = [];
    /**
     * @var array the registered JS code blocks
     * @see registerJs()
     */
    public $js = [];
    /**
     * @var array the registered JS files.
     * @see registerJsFile()
     */
    public $jsFiles = [];

    private $_assetManager;


    /**
     * @inheritdoc
     */
    public function head()
    {
        echo self::PH_HEAD;
    }

    /**
     * @inheritdoc
     */
    public function beginBody()
    {
        echo self::PH_BODY_BEGIN;
        $this->trigger(self::EVENT_BEGIN_BODY);
    }

    /**
     * @inheritdoc
     */
    public function endBody()
    {
        $this->trigger(self::EVENT_END_BODY);
        echo self::PH_BODY_END;

        foreach (array_keys($this->assetBundles) as $bundle) {
            $this->registerAssetFiles($bundle);
        }
    }

    /**
     * @inheritdoc
     */
    public function endPage($ajaxMode = false)
    {
        $this->trigger(self::EVENT_END_PAGE);

        $content = ob_get_clean();

        echo strtr($content, [
            self::PH_HEAD => $this->renderHeadHtml(),
            self::PH_BODY_BEGIN => $this->renderBodyBeginHtml(),
            self::PH_BODY_END => $this->renderBodyEndHtml($ajaxMode),
        ]);

        $this->clear();
    }

    /**
     * @inheritdoc
     */
    public function renderAjax($view, $params = [], $context = null)
    {
        $viewFile = $this->findViewFile($view, $context);

        ob_start();
        ob_implicit_flush(false);

        $this->beginPage();
        $this->head();
        $this->beginBody();
        echo $this->renderFile($viewFile, $params, $context);
        $this->endBody();
        $this->endPage(true);

        return ob_get_clean();
    }

    /**
     * @inheritdoc
     */
    public function getAssetManager()
    {
        return $this->_assetManager ?: Yii::$app->getAssetManager();
    }

    /**
     * @inheritdoc
     */
    public function setAssetManager($value)
    {
        $this->_assetManager = $value;
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->metaTags = [];
        $this->linkTags = [];
        $this->css = [];
        $this->cssFiles = [];
        $this->js = [];
        $this->jsFiles = [];
        $this->assetBundles = [];
    }

    /**
     * Registers all files provided by an asset bundle including depending bundles files.
     * Removes a bundle from [[assetBundles]] once files are registered.
     * @param string $name name of the bundle to register
     */
    protected function registerAssetFiles($name)
    {
        if (!isset($this->assetBundles[$name])) {
            return;
        }
        $bundle = $this->assetBundles[$name];
        if ($bundle) {
            foreach ($bundle->depends as $dep) {
                $this->registerAssetFiles($dep);
            }
            $bundle->registerAssetFiles($this);
        }
        unset($this->assetBundles[$name]);
    }

    /**
     * @inheritdoc
     */
    public function registerAssetBundle($name, $position = null)
    {
        if (!isset($this->assetBundles[$name])) {
            $am = $this->getAssetManager();
            $bundle = $am->getBundle($name);
            $this->assetBundles[$name] = false;
            // register dependencies
            $pos = isset($bundle->jsOptions['position']) ? $bundle->jsOptions['position'] : null;
            foreach ($bundle->depends as $dep) {
                $this->registerAssetBundle($dep, $pos);
            }
            $this->assetBundles[$name] = $bundle;
        } elseif ($this->assetBundles[$name] === false) {
            throw new InvalidConfigException("A circular dependency is detected for bundle '$name'.");
        } else {
            $bundle = $this->assetBundles[$name];
        }

        if ($position !== null) {
            $pos = isset($bundle->jsOptions['position']) ? $bundle->jsOptions['position'] : null;
            if ($pos === null) {
                $bundle->jsOptions['position'] = $pos = $position;
            } elseif ($pos > $position) {
                throw new InvalidConfigException("An asset bundle that depends on '$name' has a higher javascript file position configured than '$name'.");
            }
            // update position for all dependencies
            foreach ($bundle->depends as $dep) {
                $this->registerAssetBundle($dep, $pos);
            }
        }

        return $bundle;
    }

    /**
     * @inheritdoc
     */
    public function registerMetaTag($options, $key = null)
    {
        if ($key === null) {
            $this->metaTags[] = Html::tag('meta', '', $options);
        } else {
            $this->metaTags[$key] = Html::tag('meta', '', $options);
        }
    }

    /**
     * @inheritdoc
     */
    public function registerCsrfMetaTags()
    {
        $this->metaTags['csrf_meta_tags'] = $this->renderDynamic('return yii\helpers\Html::csrfMetaTags();');
    }

    /**
     * @inheritdoc
     */
    public function registerLinkTag($options, $key = null)
    {
        if ($key === null) {
            $this->linkTags[] = Html::tag('link', '', $options);
        } else {
            $this->linkTags[$key] = Html::tag('link', '', $options);
        }
    }

    /**
     * @inheritdoc
     */
    public function registerCss($css, $options = [], $key = null)
    {
        $key = $key ?: md5($css);
        $this->css[$key] = Html::style($css, $options);
    }

    /**
     * @inheritdoc
     */
    public function registerCssFile($url, $options = [], $key = null)
    {
        $url = Yii::getAlias($url);
        $key = $key ?: $url;

        $depends = ArrayHelper::remove($options, 'depends', []);

        if (empty($depends)) {
            $this->cssFiles[$key] = Html::cssFile($url, $options);
        } else {
            $this->getAssetManager()->bundles[$key] = Yii::createObject([
                'class' => AssetBundle::className(),
                'baseUrl' => '',
                'css' => [strncmp($url, '//', 2) === 0 ? $url : ltrim($url, '/')],
                'cssOptions' => $options,
                'depends' => (array) $depends,
            ]);
            $this->registerAssetBundle($key);
        }
    }

    /**
     * @inheritdoc
     */
    public function registerJs($js, $position = self::POS_READY, $key = null)
    {
        $key = $key ?: md5($js);
        $this->js[$position][$key] = $js;
        if ($position === self::POS_READY || $position === self::POS_LOAD) {
            JqueryAsset::register($this);
        }
    }

    /**
     * @inheritdoc
     */
    public function registerJsFile($url, $options = [], $key = null)
    {
        $url = Yii::getAlias($url);
        $key = $key ?: $url;

        $depends = ArrayHelper::remove($options, 'depends', []);

        if (empty($depends)) {
            $position = ArrayHelper::remove($options, 'position', self::POS_END);
            $this->jsFiles[$position][$key] = Html::jsFile($url, $options);
        } else {
            $this->getAssetManager()->bundles[$key] = Yii::createObject([
                'class' => AssetBundle::className(),
                'baseUrl' => '',
                'js' => [strncmp($url, '//', 2) === 0 ? $url : ltrim($url, '/')],
                'jsOptions' => $options,
                'depends' => (array) $depends,
            ]);
            $this->registerAssetBundle($key);
        }
    }

    /**
     * Renders the content to be inserted in the head section.
     * The content is rendered using the registered meta tags, link tags, CSS/JS code blocks and files.
     * @return string the rendered content
     */
    protected function renderHeadHtml()
    {
        $lines = [];
        if (!empty($this->metaTags)) {
            $lines[] = implode("\n", $this->metaTags);
        }

        if (!empty($this->linkTags)) {
            $lines[] = implode("\n", $this->linkTags);
        }
        if (!empty($this->cssFiles)) {
            $lines[] = implode("\n", $this->cssFiles);
        }
        if (!empty($this->css)) {
            $lines[] = implode("\n", $this->css);
        }
        if (!empty($this->jsFiles[self::POS_HEAD])) {
            $lines[] = implode("\n", $this->jsFiles[self::POS_HEAD]);
        }
        if (!empty($this->js[self::POS_HEAD])) {
            $lines[] = Html::script(implode("\n", $this->js[self::POS_HEAD]), ['type' => 'text/javascript']);
        }

        return empty($lines) ? '' : implode("\n", $lines);
    }

    /**
     * Renders the content to be inserted at the beginning of the body section.
     * The content is rendered using the registered JS code blocks and files.
     * @return string the rendered content
     */
    protected function renderBodyBeginHtml()
    {
        $lines = [];
        if (!empty($this->jsFiles[self::POS_BEGIN])) {
            $lines[] = implode("\n", $this->jsFiles[self::POS_BEGIN]);
        }
        if (!empty($this->js[self::POS_BEGIN])) {
            $lines[] = Html::script(implode("\n", $this->js[self::POS_BEGIN]), ['type' => 'text/javascript']);
        }

        return empty($lines) ? '' : implode("\n", $lines);
    }

    /**
     * Renders the content to be inserted at the end of the body section.
     * The content is rendered using the registered JS code blocks and files.
     * @param bool $ajaxMode whether the view is rendering in AJAX mode.
     * If true, the JS scripts registered at [[POS_READY]] and [[POS_LOAD]] positions
     * will be rendered at the end of the view like normal scripts.
     * @return string the rendered content
     */
    protected function renderBodyEndHtml($ajaxMode)
    {
        $lines = [];

        if (!empty($this->jsFiles[self::POS_END])) {
            $lines[] = implode("\n", $this->jsFiles[self::POS_END]);
        }

        if ($ajaxMode) {
            $scripts = [];
            if (!empty($this->js[self::POS_END])) {
                $scripts[] = implode("\n", $this->js[self::POS_END]);
            }
            if (!empty($this->js[self::POS_READY])) {
                $scripts[] = implode("\n", $this->js[self::POS_READY]);
            }
            if (!empty($this->js[self::POS_LOAD])) {
                $scripts[] = implode("\n", $this->js[self::POS_LOAD]);
            }
            if (!empty($scripts)) {
                $lines[] = Html::script(implode("\n", $scripts), ['type' => 'text/javascript']);
            }
        } else {
            if (!empty($this->js[self::POS_END])) {
                $lines[] = Html::script(implode("\n", $this->js[self::POS_END]), ['type' => 'text/javascript']);
            }
            if (!empty($this->js[self::POS_READY])) {
                $js = "jQuery(function ($) {\n" . implode("\n", $this->js[self::POS_READY]) . "\n});";
                $lines[] = Html::script($js, ['type' => 'text/javascript']);
            }
            if (!empty($this->js[self::POS_LOAD])) {
                $js = "jQuery(window).on('load', function () {\n" . implode("\n", $this->js[self::POS_LOAD]) . "\n});";
                $lines[] = Html::script($js, ['type' => 'text/javascript']);
            }
        }

        return empty($lines) ? '' : implode("\n", $lines);
    }
}
