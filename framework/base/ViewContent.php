<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use yii\helpers\Html;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ViewContent extends Component
{
	const POS_HEAD = 1;
	const POS_BEGIN = 2;
	const POS_END = 3;

	const TOKEN_HEAD = '<![CDATA[YII-BLOCK-HEAD]]>';
	const TOKEN_BODY_BEGIN = '<![CDATA[YII-BLOCK-BODY-BEGIN]]>';
	const TOKEN_BODY_END = '<![CDATA[YII-BLOCK-BODY-END]]>';

	/**
	 * @var \yii\web\AssetManager
	 */
	public $assetManager;
	public $assetBundles;
	public $title;
	public $metaTags;
	public $linkTags;
	public $css;
	public $cssFiles;
	public $js;
	public $jsFiles;
	public $jsInHead;
	public $jsFilesInHead;
	public $jsInBody;
	public $jsFilesInBody;

	public function init()
	{
		parent::init();
		if ($this->assetManager === null) {
			$this->assetManager = Yii::$app->getAssetManager();
		}
	}

	public function reset()
	{
		$this->title = null;
		$this->metaTags = null;
		$this->linkTags = null;
		$this->css = null;
		$this->cssFiles = null;
		$this->js = null;
		$this->jsFiles = null;
		$this->jsInHead = null;
		$this->jsFilesInHead = null;
		$this->jsInBody = null;
		$this->jsFilesInBody = null;
	}

	public function begin()
	{
		ob_start();
		ob_implicit_flush(false);
	}

	public function end()
	{
		$content = ob_get_clean();
		echo $this->populate($content);
	}

	public function beginBody()
	{
		echo self::TOKEN_BODY_BEGIN;
	}

	public function endBody()
	{
		echo self::TOKEN_BODY_END;
	}

	public function head()
	{
		echo self::TOKEN_HEAD;
	}

	public function registerAssetBundle($name)
	{
		if (!isset($this->assetBundles[$name])) {
			$bundle = $this->assetManager->getBundle($name);
			if ($bundle !== null) {
				$this->assetBundles[$name] = false;
				$bundle->registerWith($this);
				$this->assetBundles[$name] = true;
			} else {
				throw new InvalidConfigException("Unknown asset bundle: $name");
			}
		} elseif ($this->assetBundles[$name] === false) {
			throw new InvalidConfigException("A cyclic dependency is detected for bundle '$name'.");
		}
	}

	public function registerMetaTag($options, $key = null)
	{
		if ($key === null) {
			$this->metaTags[] = Html::tag('meta', '', $options);
		} else {
			$this->metaTags[$key] = Html::tag('meta', '', $options);
		}
	}
	
	public function registerLinkTag($options, $key = null)
	{
		if ($key === null) {
			$this->linkTags[] = Html::tag('link', '', $options);
		} else {
			$this->linkTags[$key] = Html::tag('link', '', $options);
		}
	}

	public function registerCss($css, $options = array(), $key = null)
	{
		$key = $key ?: $css;
		$this->css[$key] = Html::style($css, $options);
	}

	public function registerCssFile($url, $options = array(), $key = null)
	{
		$key = $key ?: $url;
		$this->cssFiles[$key] = Html::cssFile($url, $options);
	}

	public function registerJs($js, $options = array(), $key = null)
	{
		$position = isset($options['position']) ? $options['position'] : self::POS_END;
		unset($options['position']);
		$key = $key ?: $js;
		$html = Html::script($js, $options);
		if ($position == self::POS_END) {
			$this->js[$key] = $html;
		} elseif ($position == self::POS_HEAD) {
			$this->jsInHead[$key] = $html;
		} elseif ($position == self::POS_BEGIN) {
			$this->jsInBody[$key] = $html;
		} else {
			throw new InvalidParamException("Unknown position: $position");
		}
	}

	public function registerJsFile($url, $options = array(), $key = null)
	{
		$position = isset($options['position']) ? $options['position'] : self::POS_END;
		unset($options['position']);
		$key = $key ?: $url;
		$html = Html::jsFile($url, $options);
		if ($position == self::POS_END) {
			$this->jsFiles[$key] = $html;
		} elseif ($position == self::POS_HEAD) {
			$this->jsFilesInHead[$key] = $html;
		} elseif ($position == self::POS_BEGIN) {
			$this->jsFilesInBody[$key] = $html;
		} else {
			throw new InvalidParamException("Unknown position: $position");
		}
	}

	protected function populate($content)
	{
		return strtr($content, array(
			self::TOKEN_HEAD => $this->getHeadHtml(),
			self::TOKEN_BODY_BEGIN => $this->getBodyBeginHtml(),
			self::TOKEN_BODY_END => $this->getBodyEndHtml(),
		));
	}

	protected function getHeadHtml()
	{
		$lines = array();
		if (!empty($this->metaTags)) {
			$lines[] = implode("\n", $this->cssFiles);
		}
		if (!empty($this->linkTags)) {
			$lines[] = implode("\n", $this->cssFiles);
		}
		if (!empty($this->cssFiles)) {
			$lines[] = implode("\n", $this->cssFiles);
		}
		if (!empty($this->css)) {
			$lines[] = implode("\n", $this->css);
		}
		if (!empty($this->jsFilesInHead)) {
			$lines[] = implode("\n", $this->jsFilesInHead);
		}
		if (!empty($this->jsInHead)) {
			$lines[] = implode("\n", $this->jsInHead);
		}
		return implode("\n", $lines);
	}

	protected function getBodyBeginHtml()
	{
		$lines = array();
		if (!empty($this->jsFilesInBody)) {
			$lines[] = implode("\n", $this->jsFilesInBody);
		}
		if (!empty($this->jsInHead)) {
			$lines[] = implode("\n", $this->jsInBody);
		}
		return implode("\n", $lines);
	}

	protected function getBodyEndHtml()
	{
		$lines = array();
		if (!empty($this->jsFiles)) {
			$lines[] = implode("\n", $this->jsFiles);
		}
		if (!empty($this->js)) {
			$lines[] = implode("\n", $this->js);
		}
		return implode("\n", $lines);
	}
}