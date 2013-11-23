<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap\helpers;

/**
 * Provides implementation for various bootstrap HTML helper functions
 *
 * @see http://getbootstrap.com/css
 * @see http://getbootstrap.com/components
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 2.0
 */
class Html extends \yii\helpers\Html
{
	/**
	 * Bootstrap CSS helpers
	 */
	const FLOAT_LEFT         = 'pull-left';
	const FLOAT_RIGHT        = 'pull-right';
	const FLOAT_CENTER       = 'center-block';
	const NAVBAR_FLOAT_LEFT  = 'navbar-left';
	const NAVBAR_FLOAT_RIGHT = 'navbar-right';
	const CLEAR_FLOAT        = 'clearfix';
	const SHOW               = 'show';
	const HIDDEN             = 'hidden';
	const INVISIBLE          = 'invisible';
	const SCREEN_READER      = 'sr-only';
	const IMAGE_REPLACER     = 'text-hide';
	
	/**
	 * Bootstrap size modifier suffixes
	 */
	const TINY   = 'xs';
	const SMALL  = 'sm';
	const MEDIUM = 'md';
	const LARGE  = 'lg';

	/**
	 * Bootstrap color modifier classes
	 */
	const TYPE_DEFAULT = 'default';
	const TYPE_PRIMARY = 'primary';
	const TYPE_SUCCESS = 'success';
	const TYPE_INFO    = 'info';
	const TYPE_WARNING = 'warning';
	const TYPE_DANGER  = 'danger';
	
	/**
	 * Generates an icon.
	 * @param string $icon the bootstrap icon name without prefix (e.g. 'plus', 'pencil', 'trash')
	 * @param array $options html options for the icon container
	 * @param string $prefix the css class prefix - defaults to 'glyphicon glyphicon-'
	 * @param string $tag the icon container tag (usually 'span' or 'i') - defaults to 'span'
	 *
	 * Example(s): 
	 * ```php
	 * echo Html::icon('trash');
	 * echo Html::icon('pencil', ['style' => 'color: red; font-size: 2em']);
	 * echo Html::icon('pencil', ['class' => 'text-success']);
	 * ```
	 *
	 * @see http://getbootstrap.com/components/#glyphicons
	 */
	public static function icon($icon, $options = [], $prefix = 'glyphicon glyphicon-', $tag = 'span')
	{
		$class = isset($options['class']) ? ' ' . $options['class'] : '';
		$options['class'] = $prefix . $icon . $class;
		return static::tag($tag, '', $options);
	}
	
	/**
	 * Generates a label.
	 * @param string $content the label content
	 * @param string $type the bootstrap label type - defaults to 'default'
	 *  - is one of 'default, 'primary', 'success', 'info', 'danger', 'warning'
	 * @param array $options html options for the label container
	 * @param string $prefix the css class prefix - defaults to 'label label-'
	 * @param string $tag the label container tag - defaults to 'span'
	 *
	 * Example(s): 
	 * ```php
	 * echo Html::label('Default');
	 * echo Html::label('Primary', Html::TYPE_PRIMARY);
	 * echo Html::label('Success', Html::TYPE_SUCCESS);
	 * ```
	 * Styling:
	 * ```php
	 * echo Html::icon('trash', ['style' => 'color: red; font-size: 2em']);
	 * ```
	 *
	 * @see http://getbootstrap.com/components/#labels
	 */
	public static function label($content, $type = '', $options = [], $prefix = 'label label-', $tag = 'span')
	{
		if (empty($type)) {
			$type = static::TYPE_DEFAULT;
		}
		$class = isset($options['class']) ? ' ' . $options['class'] : '';
		$options['class'] = $prefix . $type . $class;
		return static::tag($tag, $content, $options);
	}
	
	/**
	 * Generates a badge.
	 * @param string $content the badge content
	 * @param array $options html options for the badge container
	 * @param string $tag the badge container tag - defaults to 'span'
	 */
	public static function badge($content, $options = [], $tag = 'span')
	{
		static::addCssClass($options, 'badge');
		return static::tag($tag, $content, $options);
	}
	
	/**
	 * Generates a jumbotron - a lightweight, flexible component that can optionally 
	 * extend the entire viewport to showcase key content on your site.
	 * @param string $title the title heading shown in the jumbotron
	 * @param string $content the content below the heading in the jumbotron
	 * @param boolean $fullWidth whether this is a full width jumbotron without any corners - defaults to false
	 * @param array $options html options for the jumbotron
	 * @see http://getbootstrap.com/components/#jumbotron
	 */
	public static function jumbotron($title, $content, $fullWidth = false, $options = []) {
		static::addCssClass($options, 'jumbotron');
		$title = "<h1>{$title}</h1>\n";
		$chkPara = preg_replace('/\s+/', '', $content);
		if (substr($chkPara, 0, 3) != '<p>') {
			$content = static::tag('p', $content);
		}
		if ($fullWidth) {
			return static::tag('div', $title . static::tag('div', $content, ['class'=>'container']), $options);
		}
		else {
			return static::tag('div', $title . $content, $options);
		}
	}
	
	/**
	 * Generates a panel for boxing content.
	 * @param string $heading the panel box heading (optional)
	 * @param string $body the panel body content (either this or $content must be passed)
	 * @param string $content other/additional content not embedded in panel-body (optional)
	 * @param string $footer the panel footer (optional)
	 * @param string $type the panel type - defaults to 'default'
	 *  - is one of 'default, 'primary', 'success', 'info', 'danger', 'warning'
	 * @param array $options html options for the panel
	 * @see http://getbootstrap.com/components/#panels
	 */
	public static function panel($heading = '', $body = '', $content = '', $footer = '', $type = 'default', $options = []) {
		static::addCssClass($options, 'panel panel-' . $type);
		$heading = (!empty($heading)) ? static::tag('div', $heading, ['class'=>'panel-heading']) : '';
		$body = (!empty($body)) ? static::tag('div', $body, ['class'=>'panel-body']) : '';
		$footer = (!empty($footer)) ? static::tag('div', $footer, ['class'=>'panel-footer']) : '';
		return static::tag('div', $heading . $body . $content . $footer, $options);
	}
	
	/**
	 * Generates a page header.
	 * @param string $title the title to be shown
	 * @param string $subTitle the subtitle to be shown as subtext within the title
	 * @param array $options html options for the page header
	 *
	 * Example: 
	 * ```php
	 * echo Html::pageHeader(
	 * 		'Example page header',
	 * 		'Subtext for header',
	 * );
	 * ```
	 * @see http://getbootstrap.com/components/#page-header
	 */
	public static function pageHeader($title, $subTitle = '', $options = []) {
		static::addCssClass($options, 'page-header');
		if (!empty($subTitle)) {
			$title = "<h1>{$title} <small>{$subTitle}</small></h1>";
		}
		else {
			$title = "<h1>{$title}</h1>";
		}
		return static::tag('div', $title, $options);
	}

	/**
	 * Generates a well container.
	 * @param string $content the content
	 * @param string $size the well size - one of the size constants
	 * - TINY   = 'xs';
	 * - SMALL  = 'sm';
	 * - MEDIUM = 'md';
	 * - LARGE  = 'lg';
	 * @param array $options html options for the well container.
	 *
	 * Example: 
	 * ```php
	 * echo Html::well(
	 * 		'Cras sit amet nibh libero, in gravida nulla. Nulla vel metus scelerisque ante sollicitudin commodo.',
	 * 		Html::LARGE,
	 * );
	 * ```
	 *
	 * @see http://getbootstrap.com/components/#wells
	 */
	public static function well($content, $size = '', $options = []) {
		static::addCssClass($options, 'well');
		if (!empty($size)) {
			static::addCssClass($options, 'well-' . $size);
		}
		return static::tag('div', $content, $options);
	}	
	
	/**
	 * Generates a media object. Abstract object styles for building various types of 
	 * components (like blog comments, Tweets, etc) that feature a left-aligned or 
	 * right-aligned  image alongside textual content.
	 * @param string $heading the media heading
	 * @param string $body the media content
	 * @param string $src URL for the media article source 
	 * @param string $img URL for the media image source 
	 * @param array $srcOptions html options for the media article link
	 * @param array $imgOptions html options for the media image
	 * @param array $options html options for the media object container
	 * @param string $tag the media container tag - defaults to 'div'
	 *
	 * Example: 
	 * ```php
	 * echo Html::media(
	 * 		'Media heading 1', 
	 * 		'Cras sit amet nibh libero, in gravida nulla. Nulla vel metus scelerisque ante sollicitudin commodo.',
	 * 		'#',
	 * 		'http://placehold.it/64x64'
	 * );
	 * ```
	 *
	 * @see http://getbootstrap.com/components/#media
	 */
	public static function media($heading = '', $body = '', $src = '', $img = '', $srcOptions = [], $imgOptions = [], $options = [], $tag = 'div') {
		static::addCssClass($options, 'media');
		
		if (!isset($srcOptions['class'])) {
			static::addCssClass($srcOptions, 'pull-left');
		}
		
		static::addCssClass($imgOptions, 'media-object');
		
		$source = static::a(static::img($img, $imgOptions), $src, $srcOptions);
		$heading = (!empty($heading)) ? static::tag('h4', $heading, ['class'=>'media-heading']) : '';
		$content = (!empty($body)) ? static::tag('div', $heading . "\n" . $body, ['class'=>'media-body']) : $heading;
		
		return static::tag($tag, $source . "\n" . $content, $options);
	}

	/**
	 * Generates list of media (useful for comment threads or articles lists).
	 * @param array $items the media items - each element in the array must contain these keys:
	 * - @param string $items the sub media items
	 * - @param string $heading the media heading
	 * - @param string $body the media content
	 * - @param string $src URL for the media article source 
	 * - @param string $img URL for the media image source 
	 * - @param array $srcOptions html options for the media article link (optional)
	 * - @param array $imgOptions html options for the media image (optional)
	 * - @param array $itemOptions html options for each media item (optional)
	 * @param array $options html options for the media list container
	 *
	 * Example: 
	 * ```php
	 * echo Html::mediaList([
	 * 	[
	 * 		'heading' => 'Media heading 1', 
	 * 		'body' => 'Cras sit amet nibh libero, in gravida nulla. Nulla vel metus scelerisque ante sollicitudin commodo. '.
	 *                'Cras purus odio, vestibulum in vulputate at, tempus viverra turpis.', 
	 * 		'src' => '#',
	 * 		'img' => 'http://placehold.it/64x64',
	 * 		'items' => [
	 * 			[
	 * 				'heading' => 'Media heading 1.1', 
	 * 				'body' => 'Cras sit amet nibh libero, in gravida nulla. Nulla vel metus scelerisque ante sollicitudin commodo. ' .
	 *                        'Cras purus odio, vestibulum in vulputate at, tempus viverra turpis.', 
	 * 				'src' => '#',
	 * 				'img' => 'http://placehold.it/64x64'
	 * 			],
	 * 			[
	 * 				'heading' => 'Media heading 1.2', 
	 * 				'body' => 'Cras sit amet nibh libero, in gravida nulla. Nulla vel metus scelerisque ante sollicitudin commodo. ' .
	 *                        'Cras purus odio, vestibulum in vulputate at, tempus viverra turpis.', 
	 * 				'src' => '#',
	 * 				'img' => 'http://placehold.it/64x64'
	 * 			],
	 * 		]
	 * 	],
	 * 	[
	 * 		'heading' => 'Media heading 2', 
	 * 		'body' => 'Cras sit amet nibh libero, in gravida nulla. Nulla vel metus scelerisque ante sollicitudin commodo. '.
	 *                'Cras purus odio, vestibulum in vulputate at, tempus viverra turpis.', 
	 * 		'src' => '#',
	 * 		'img' => $img
	 * 	],
	 * ]);
	 * ```
	 *
	 * @see http://getbootstrap.com/components/#media
	 */
	public static function mediaList($items = [], $options = []) {
		static::addCssClass($options, 'media-list');
		$content = static::generateMediaList($items);
		return static::tag('ul', $content, $options);
	}
	
	/**
	 * Processes media items array to generate a recursive list.
	 * @param array $items the media items
	 * @param boolean $top whether item is the topmost parent
	 */
	 protected static function generateMediaList($items, $top = true) {
		$content = '';
		foreach ($items as $item) {
			$tag = ($top) ? 'li' : 'div';
			if (isset($item['items'])) {
				$item['body'] .= static::generateMediaList($item['items'], false);
			}
			$content .= static::generateMediaItem($item, $tag) . "\n";
		}
		return $content;
	}
	
	/**
	 * Processes and generates each media item
	 * @param array $item the media item configuration
	 * @param string $tag the media item container tag
	 */	
	protected static function generateMediaItem($item, $tag) {
		$heading = isset($item['heading']) ? $item['heading'] : '';
		$body = isset($item['body']) ? $item['body'] : '';
		$src = isset($item['src']) ? $item['src'] : '#';
		$img = isset($item['img']) ? $item['img'] : '';
		$srcOptions = isset($item['srcOptions']) ? $item['srcOptions'] : [];
		$imgOptions = isset($item['imgOptions']) ? $item['imgOptions'] : [];
		$itemOptions = isset($item['itemOptions']) ? $item['itemOptions'] : [];
		return static::media($heading, $body, $src, $img, $srcOptions, $imgOptions, $itemOptions, $tag);
	}
	
	/**
	 * Generates a generic close icon button for 
	 * dismissing content like modals and alerts.
	 * @param array $options html options for the close icon button
	 * @param string $label the close icon label - defaults to '&times;'
	 * @param string $tag the html tag for rendering the close icon - defaults to 'button'
	 *
	 * Example: 
	 * ```php
	 * echo Html::closeButton();
	 * ```
	 *
	 * @see http://getbootstrap.com/css/#helper-classes-close
	 */
	public static function closeButton($options = [], $label = '&times;', $tag = 'button')
	{
		static::addCssClass($options, 'close');
		$options['type'] = 'button';
		$options['aria-hidden'] = 'true';
		return static::tag('button', $label, $options);
	}
	
	/**
	 * Generates a caret.
	 * @param array $options html options for the caret container.
	 * @param string $tag the html tag for rendering the caret - defaults to 'span'
	 *
	 * Example: 
	 * ```php
	 * echo Html::caret();
	 * ```
	 *
	 * @see http://getbootstrap.com/css/#helper-classes-carets
	 */
	public static function caret($options = [], $tag = 'span')
	{
		static::addCssClass($options, 'caret');
		return static::tag($tag, '', $options);
	}
	
	/**
	 * Generates a lead body copy - makes a paragraph stand out.
	 * @param string $content the content to be formatted
	 * @param array $options html options.
	 * @param string $tag the html tag for rendering - defaults to 'p'
	 *
	 * Example: 
	 * ```php
	 * echo Html::lead('Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Duis mollis, est non commodo luctus.');
	 * ```
	 *
	 * @see http://getbootstrap.com/css/#type-body-copy
	 */
	public static function lead($content, $options = [], $tag = 'p')
	{
		static::addCssClass($options, 'lead');
		return static::tag($tag, $content, $options);
	}

	/**
	 * Generates an abbreviation.
	 * @param string $title the abbreviation title
	 * @param string $content the abbreviation content
	 * @param boolean $initialism if set to true, will display a slightly smaller font-size.
	 * @param array $options html options for the abbreviation
	 *
	 * Example: 
	 * ```php
	 * echo Html::abbr(
	 *	'HyperText Markup Language'
	 *	'HTML',
	 *	true
	 * );
	 * ```
	 *
	 * @see http://getbootstrap.com/css/#type-abbreviations
	 */
	public static function abbr($title, $content, $initialism = false, $options = [])
	{
		$options['title'] = $title;
		if ($initialism) {
			static::addCssClass($options, 'initialism');
		}
		return static::tag('abbr', $content, $options);
	}
	
	/**
	 * Generates a blockquote.
	 * @param string $content the blockquote content
	 * @param string $citeContent the content of the citation (optional) - this should typically 
	 * include the wildtag '{source}' to embed the cite source 
	 * @param string $citeTitle the cite source title (optional)
	 * @param string $citeSource the cite source (optional)
	 * 
	 * Example: 
	 * ```php
	 *		Html::blockquote(
	 *			'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat a ante.',
	 *			'Someone famous in {source}',
	 *			'International Premier League',
	 *			'IPL'
	 *		);
	 * ```
	 *			
	 * @param array $options html options for the blockquote
	 * @see http://getbootstrap.com/css/#type-blockquotes
	 */
	public static function blockquote($content, $citeContent = '', $citeTitle = '', $citeSource = '', $options = [])
	{
		$content = static::tag('p', $content);
		if ($citeContent != '') {
			$source = static::tag('cite', $citeSource, ['title'=>$citeTitle]);
			$content .= "\n<small>" . str_replace('{source}', $source, $citeContent) . "</small>";
		}
		return static::tag('blockquote', $content, $options);
	}
	
	/**
	 * Generates an address block.
	 * @param string $name the addressee name
	 * @param array $lines the lines of address information
	 * @param array $phone the list of phone numbers - passed as $key => $value, where:
	 * - $key is the phone type could be 'Res', 'Off', 'Cell', 'Fax'
	 * - $value is the phone number
	 * @param array $email the list of email addresses - passed as $key => $value, where:
	 * - $key is the email type could be 'Res', 'Off'
	 * - $value is the email address
	 * @param string $phoneLabel the prefix label for each phone - defaults to '(P)'
	 * @param string $emailLabel the prefix label for each email - defaults to '(E)'
	 *
	 * Example: 
	 * ```php
	 *		Html::address(
	 *			'Twitter, Inc.',
	 *			['795 Folsom Ave, Suite 600', 'San Francisco, CA 94107'],
	 *			['Res' => '(123) 456-7890', 'Off'=> '(456) 789-0123'],
	 *			['Res' => 'first.last@example.com', 'Off' => 'last.first@example.com']
	 *		);
	 * ```
	 *
	 * @see http://getbootstrap.com/css/#type-addresses
	 */	
	public static function address($name, $lines = [], $phone = [], $email = [], $phoneLabel = '(P)', $emailLabel = '(E)') {
		$addresses = '';
		if (!empty($lines)) {
			$addresses = implode('<br>', $lines) . "<br>\n";
		}
		
		$phones = '';
		foreach ($phone as $type => $number) {
			if (is_numeric($type)) {	// no keys were passed to the phone array
				$type = '<abbr title="Phone">' . $phoneLabel . '</abbr>: ';
			}
			else {
				$type = '<abbr title="Phone">' . $phoneLabel . ' ' . $type . '</abbr>: ';
			}
			$phones .= "{$type}{$number}<br>\n";
		}
		
		$emails = '';
		foreach ($email as $type => $addr) {
			if (is_numeric($type)) {	// no keys were passed to the email array
				$type = '<abbr title="Email">' . $emailLabel . '</abbr>: ';
			}
			else {
				$type = '<abbr title="Email">' . $emailLabel . ' ' . $type . '</abbr>: ';
			}
			$emails .= $type . static::mailto($addr, $addr) . "<br>\n";
		}
		
		return "<address>\n" .
			"<strong>{$name}</strong><br>\n" .
			$addresses .
			$phones .
			$emails .
			"</address>"; 
	}
	
	/**
	 * Generates a static input.
	 * @param string $value the static input value
	 * @param array $options html options for the static input
	 * @param string $tag the html tag for rendering the static input - defaults to 'p'
	 *
	 * Example(s): 
	 * ```php
	 * echo Html::staticInput('email@example.com');
	 * ```
	 * With model
	 * ```php
	 * echo Html::activeLabel($model, email);
	 * echo Html::staticInput($model->email);
	 * ```
	 *
	 * @see http://getbootstrap.com/css/#forms-controls-static
	 */
	public static function staticInput($value, $options = [], $tag = 'p')
	{
		static::addCssClass($options, 'form-control-static');
		return static::tag($tag, $value, $options);
	}
}
