<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ViewContent extends Component
{
	const POS_HEAD = 1;
	const POS_BEGIN = 2;
	const POS_END = 3;

	public $packages;

	public $title;
	public $metaTags;
	public $linkTags;
	public $css;
	public $js;
	public $cssFiles;
	public $jsFiles;

	public function populate($content)
	{
		return $content;
	}

	public function reset()
	{
		$this->title = null;
		$this->metaTags = null;
		$this->linkTags = null;
		$this->css = null;
		$this->js = null;
		$this->cssFiles = null;
		$this->jsFiles = null;
	}

	public function getMetaTag($key)
	{
		return isset($this->metaTags[$key]) ? $this->metaTags[$key] : null;
	}

	public function setMetaTag($key, $tag)
	{
		$this->metaTags[$key] = $tag;
	}

	public function getLinkTag($key)
	{
		return isset($this->linkTags[$key]) ? $this->linkTags[$key] : null;
	}

	public function setLinkTag($key, $tag)
	{
		$this->linkTags[$key] = $tag;
	}

	public function getCss($key)
	{
		return isset($this->css[$key]) ? $this->css[$key]: null;
	}

	public function setCss($key, $css)
	{
		$this->css[$key] = $css;
	}

	public function getCssFile($key)
	{
		return isset($this->cssFiles[$key]) ? $this->cssFiles[$key]: null;
	}

	public function setCssFile($key, $file)
	{
		$this->cssFiles[$key] = $file;
	}

	public function getJs($key, $position = self::POS_END)
	{
		return isset($this->js[$position][$key]) ? $this->js[$position][$key] : null;
	}

	public function setJs($key, $js, $position = self::POS_END)
	{
		$this->js[$position][$key] = $js;
	}

	public function getJsFile($key, $position = self::POS_END)
	{
		return isset($this->jsFiles[$position][$key]) ? $this->jsFiles[$position][$key] : null;
	}

	public function setJsFile($key, $file, $position = self::POS_END)
	{
		$this->jsFiles[$position][$key] = $file;
	}

}