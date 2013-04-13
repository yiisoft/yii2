<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ViewContent extends Component
{
	const POS_HEAD = 1;
	const POS_BEGIN = 2;
	const POS_END = 3;

	/**
	 * @var array
	 *
	 * Each asset bundle should be declared with the following structure:
	 *
	 * ~~~
	 * array(
	 *     'basePath' => '...',
	 *     'baseUrl' => '...',  // if missing, the bundle will be published to the "www/assets" folder
	 *     'js' => array(
	 *         'js/main.js',
	 *         'js/menu.js',
	 *         'js/base.js' => self::POS_HEAD,
	 *     'css' => array(
	 *         'css/main.css',
	 *         'css/menu.css',
	 *     ),
	 *     'depends' => array(
	 *         'jquery',
	 *         'yii',
	 *         'yii/treeview',
	 *     ),
	 * )
	 * ~~~
	 */
	public $bundles;
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
		$this->cssFiles = null;
		$this->js = null;
		$this->jsFiles = null;
		$this->jsInHead = null;
		$this->jsFilesInHead = null;
		$this->jsInBody = null;
		$this->jsFilesInBody = null;
	}

	public function renderScripts($pos)
	{
	}
}