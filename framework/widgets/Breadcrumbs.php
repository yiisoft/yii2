<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Breadcrumbs extends Widget
{
	/**
	 * @var array the HTML attributes for the breadcrumb container tag. The "tag" element is
	 * specially handled which specifies the tag name of the container element. If not set, it will default to "ul".
	 */
	public $options = array('tag' => 'ul', 'class' => 'breadcrumb');
	/**
	 * @var boolean whether to HTML encode the link labels. Defaults to true.
	 */
	public $encodeLabels = true;
	/**
	 * @var string the first hyperlink in the breadcrumbs (called home link).
	 * If this property is not set, it defaults to a link pointing to {@link CWebApplication::homeUrl} with label 'Home'.
	 * If this property is false, the home link will not be rendered.
	 */
	public $homeLink;
	/**
	 * @var array list of hyperlinks to appear in the breadcrumbs. If this property is empty,
	 * the widget will not render anything. Each key-value pair in the array
	 * will be used to generate a hyperlink by calling CHtml::link(key, value). For this reason, the key
	 * refers to the label of the link while the value can be a string or an array (used to
	 * create a URL). For more details, please refer to {@link CHtml::link}.
	 * If an element's key is an integer, it means the element will be rendered as a label only (meaning the current page).
	 *
	 * The following example will generate breadcrumbs as "Home > Sample post > Edit", where "Home" points to the homepage,
	 * "Sample post" points to the "index.php?r=post/view&id=12" page, and "Edit" is a label. Note that the "Home" link
	 * is specified via {@link homeLink} separately.
	 *
	 * <pre>
	 * array(
	 *     'Sample post'=>array('post/view', 'id'=>12),
	 *     'Edit',
	 * )
	 * </pre>
	 */
	public $links = array();
	/**
	 * @var string String, specifies how each active item is rendered. Defaults to
	 * "<a href="{url}">{label}</a>", where "{label}" will be replaced by the corresponding item
	 * label while "{url}" will be replaced by the URL of the item.
	 * @since 1.1.11
	 */
	public $itemTemplate = "<li>{link} <span class=\"divider\">/</span></li>\n";
	/**
	 * @var string String, specifies how each inactive item is rendered. Defaults to
	 * "<span>{label}</span>", where "{label}" will be replaced by the corresponding item label.
	 * Note that inactive template does not have "{url}" parameter.
	 * @since 1.1.11
	 */
	public $activeItemTemplate = "<li class=\"active\">{link}</li>\n";
	/**
	 * @var string the separator between links in the breadcrumbs. Defaults to ' &raquo; '.
	 */
	public $separator = ' &raquo; ';

	/**
	 * Renders the content of the portlet.
	 */
	public function run()
	{
		if (!empty($this->links)) {
			return;
		}
		$links = array();
		if ($this->homeLink === null) {
			$links[] = Html::a(Html::encode('yii|Home'), Yii::$app->homeUrl);
		} elseif ($this->homeLink !== false) {
			$links[] = $this->homeLink;
		}
		foreach ($this->links as $link) {
			if (strpos($link, '<a') !== false) {
				$links[] = strtr($this->itemTemplate, array('{link}' => $link));
			} else {
				$links[] = strtr($this->activeItemTemplate, array('{link}' => $link));
			}
		}
		$tagName = isset($this->options['tag']) ? $this->options['tag'] : 'ul';
		unset($this->options['tag']);
		echo Html::tag($tagName, implode('', $this->links), $this->options);
	}
}