<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * AssetBundle 代表的资源文件的集合，如 CSS，JS，图片。
 *
 * 每个资源包有一个唯一的名称，可以在应用中所使用的全部资源包中全局标识到它。
 * 它的名字就是表示这个类的 [完全限定类名](http://php.net/manual/en/language.namespaces.rules.php)
 *
 *
 * 资源包可以依赖于其他资源包。当在视图中注册了某资源包，
 * 它所依赖的资源包都会自动注册进来。
 *
 * 关于 AssetBundle 的更多使用参考，请查看 [前端资源指南](guide:structure-assets)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AssetBundle extends BaseObject
{
    /**
     * @var string 包含此资源包的源资源文件的目录。
     * 源资源文件应是你的 Web 应用的源码仓库里的某些文件。
     *
     * 如果这个包含了源资源文件的目录不能通过 Web 访问，你必须设置此属性。
     * 这样，[[AssetManager]] 才能将源资源文件自动发布到一个可 Web 访问的目录里，
     * 当你在页面上注册资源包时。
     *
     * 如果未设置此属性，则表示源资源文件位于 [[basePath]] 下。
     *
     * 你可以使用目录或目录的别名。
     * @see $publishOptions
     */
    public $sourcePath;
    /**
     * @var string 此资源包里，包含着资源文件的可以 Web 访问的目录。
     *
     * 如果设置了 [[sourcePath]]，这个属性会被 [[AssetManager]] *覆盖*
     * 当它从 [[sourcePath]] 发布资源文件时。
     *
     * 你可以使用目录或目录的别名。
     */
    public $basePath;
    /**
     * @var string 属性 [[js]]、[[css]] 里定义的相对路径的资源文件的基本 URL。
     *
     * 如果设置了 [[sourcePath]]，这个属性会被 [[AssetManager]] *覆盖*
     * 当它从 [[sourcePath]] 发布资源文件时。
     *
     * You can use either a URL or an alias of the URL.
     */
    public $baseUrl;
    /**
     * @var array 此资源包所依赖的包的列表。
     *
     * 例如：
     *
     * ```php
     * public $depends = [
     *    'yii\web\YiiAsset',
     *    'yii\bootstrap\BootstrapAsset',
     * ];
     * ```
     */
    public $depends = [];
    /**
     * @var array 此资源包包含的 JavaScript 文件列表。
     * 每个 JavaScript 文件都能以下列格式指定：
     *
     * - 表示外站资源的的绝对 URL。比如：
     *   `http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js` 或者
     *   `//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js`。
     * - 表示站内资源的相对路径（比如，`js/main.js`）。 本地的实际文件路径
     *   会以 [[basePath]] 为前缀、拼接上相对路径， and the actual URL
     *   同时真实的 URL 会以 [[baseUrl]] 为前缀、、拼接上相对路径。
     * - 一个数组配置，第一个条目是前面描述的绝对 URL 或相对路径，接下来是键值对列表，
     *   将用于覆盖此条目的 [[jsOptions]] 设置。
     *   此功能自 2.0.7 版开始提供。
     *
     * 请注意，只能使用正斜杠“/”作为目录分隔符。
     */
    public $js = [];
    /**
     * @var array 此资源包包含的 CSS 文件列表。 Each CSS file can be specified
     * 每个 CSS 文件都能像 [[js]] 注释里的三种格式那样指定。
     *
     * 请注意，只能使用正斜杠“/”作为目录分隔符。
     */
    public $css = [];
    /**
     * @var array 将传递给 [[View::registerJsFile()]] 方法的选项，
     * 当此资源包注册 JS 文件时。
     */
    public $jsOptions = [];
    /**
     * @var array 将传递给 [[View::registerCssFile()]] 方法的选项，
     * 当此资源包注册 CSS 文件时。
     */
    public $cssOptions = [];
    /**
     * @var array 将传递给 [[AssetManager::publish()]] 方法的选项，
     *  当此资源包正在发布时。仅在设置了 [[sourcePath]] 时使用此属性。
     */
    public $publishOptions = [];


    /**
     * 注册资源包到某视图。
     * @param View $view 某视图
     * @return static 已注册的资源包实例
     */
    public static function register($view)
    {
        return $view->registerAssetBundle(get_called_class());
    }

    /**
     * 初始化资源包。
     * If you override this method, make sure you call the parent implementation in the last.
     */
    public function init()
    {
        if ($this->sourcePath !== null) {
            $this->sourcePath = rtrim(Yii::getAlias($this->sourcePath), '/\\');
        }
        if ($this->basePath !== null) {
            $this->basePath = rtrim(Yii::getAlias($this->basePath), '/\\');
        }
        if ($this->baseUrl !== null) {
            $this->baseUrl = rtrim(Yii::getAlias($this->baseUrl), '/');
        }
    }

    /**
     * 注册 CSS 和 JS 文件到所给的视图。
     * @param \yii\web\View $view 所给的视图。
     */
    public function registerAssetFiles($view)
    {
        $manager = $view->getAssetManager();
        foreach ($this->js as $js) {
            if (is_array($js)) {
                $file = array_shift($js);
                $options = ArrayHelper::merge($this->jsOptions, $js);
                $view->registerJsFile($manager->getAssetUrl($this, $file), $options);
            } else {
                if ($js !== null) {
                    $view->registerJsFile($manager->getAssetUrl($this, $js), $this->jsOptions);
                }
            }
        }
        foreach ($this->css as $css) {
            if (is_array($css)) {
                $file = array_shift($css);
                $options = ArrayHelper::merge($this->cssOptions, $css);
                $view->registerCssFile($manager->getAssetUrl($this, $file), $options);
            } else {
                if ($css !== null) {
                    $view->registerCssFile($manager->getAssetUrl($this, $css), $this->cssOptions);
                }
            }
        }
    }

    /**
     * 当源码不在 Web 可访问的目录下面时，发布资源包。
     * 它也会用 [[AssetManager::converter|asset converter]] 去编译 非 CSS 或者 JS 的文件
     * （比如，LESS，Sass）成为 CSS 和 JS 文件。
     * @param AssetManager $am 用于执行发布的资源管理器
     */
    public function publish($am)
    {
        if ($this->sourcePath !== null && !isset($this->basePath, $this->baseUrl)) {
            list($this->basePath, $this->baseUrl) = $am->publish($this->sourcePath, $this->publishOptions);
        }

        if (isset($this->basePath, $this->baseUrl) && ($converter = $am->getConverter()) !== null) {
            foreach ($this->js as $i => $js) {
                if (is_array($js)) {
                    $file = array_shift($js);
                    if (Url::isRelative($file)) {
                        $js = ArrayHelper::merge($this->jsOptions, $js);
                        array_unshift($js, $converter->convert($file, $this->basePath));
                        $this->js[$i] = $js;
                    }
                } elseif (Url::isRelative($js)) {
                    $this->js[$i] = $converter->convert($js, $this->basePath);
                }
            }
            foreach ($this->css as $i => $css) {
                if (is_array($css)) {
                    $file = array_shift($css);
                    if (Url::isRelative($file)) {
                        $css = ArrayHelper::merge($this->cssOptions, $css);
                        array_unshift($css, $converter->convert($file, $this->basePath));
                        $this->css[$i] = $css;
                    }
                } elseif (Url::isRelative($css)) {
                    $this->css[$i] = $converter->convert($css, $this->basePath);
                }
            }
        }
    }
}
