<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/**
 * Modal renders a bootstrap modal on the page for its use on your application.
 *
 * Basic usage:
 *
 * ```php
 * $this->widget(Modal::className(), array(
 * 	'id' => 'myModal',
 * 	'header' => 'Modal Heading',
 * 	'content' => '<p>One fine body...</p>',
 * 	'footer' => 'Modal Footer',
 *  // if we wish to display a modal button
 * 	'buttonOptions' => array(
 * 		'label' => 'Show Modal',
 * 		'class' => 'btn btn-primary'
 * 		)
 * ));
 * ```
 * @see http://twitter.github.io/bootstrap/javascript.html#modals
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class Modal extends Widget
{
	/**
	 * @var array  The additional HTML attributes of the button that will show the modal. If empty array, only
	 * the markup of the modal will be rendered on the page, so users can easily call the modal manually with their own
	 * scripts. The following special attributes are available:
	 * <ul>
	 *    <li>label: string, the label of the button</li>
	 * </ul>
	 *
	 * For available options of the button trigger, see http://twitter.github.com/bootstrap/javascript.html#modals.
	 */
	public $buttonOptions = array();

	/**
	 * @var boolean indicates whether the modal should use transitions. Defaults to 'true'.
	 */
	public $fade = true;

	/**
	 * @var bool $keyboard, closes the modal when escape key is pressed.
	 */
	public $keyboard = true;

	/**
	 * @var bool $show, shows the modal when initialized.
	 */
	public $show = false;

	/**
	 * @var mixed includes a modal-backdrop element. Alternatively, specify `static` for a backdrop which doesn't close
	 * the modal on click.
	 */
	public $backdrop = true;

	/**
	 * @var mixed the remote url. If a remote url is provided, content will be loaded via jQuery's load method and
	 * injected into the .modal-body of the modal.
	 */
	public $remote;

	/**
	 * @var string a javascript function that will be invoked immediately when the `show` instance method is called.
	 */
	public $onShow;

	/**
	 * @var string a javascript function that will be invoked when the modal has been made visible to the user
	 *     (will wait for css transitions to complete).
	 */
	public $onShown;

	/**
	 * @var string a javascript function that will be invoked immediately when the hide instance method has been called.
	 */
	public $onHide;

	/**
	 * @var string a javascript function that will be invoked when the modal has finished being hidden from the user
	 *     (will wait for css transitions to complete).
	 */
	public $onHidden;

	/**
	 * @var string[] the Javascript event handlers.
	 */
	protected $events = array();

	/**
	 * @var array $pluginOptions the plugin options.
	 */
	protected $pluginOptions = array();

	/**
	 * @var string
	 */
	public $closeText = '&times;';

	/**
	 * @var string header content. Header can also be a path to a view file.
	 */
	public $header;

	/**
	 * @var string body of modal. Body can also be a path to a view file.
	 */
	public $content;

	/**
	 * @var string footer content. Content can also be a path to a view file.
	 */
	public $footer;

	/**
	 * Widget's init method
	 */
	public function init()
	{
		parent::init();

		$this->name = 'modal';

		$this->defaultOption('id', $this->getId());

		$this->defaultOption('role', 'dialog');
		$this->defaultOption('tabindex', '-1');

		$this->addClassName('modal');
		$this->addClassName('hide');

		if ($this->fade)
			$this->addClassName('fade');

		$this->initPluginOptions();
		$this->initPluginEvents();
	}

	/**
	 * Initialize plugin events if any
	 */
	public function initPluginEvents()
	{
		foreach (array('onShow', 'onShown', 'onHide', 'onHidden') as $event) {
			if ($this->{$event} !== null) {
				$modalEvent = strtolower(substr($event, 2));
				if ($this->{$event} instanceof JsExpression)
					$this->events[$modalEvent] = $this->$event;
				else
					$this->events[$modalEvent] = new JsExpression($this->{$event});
			}
		}
	}

	/**
	 * Initialize plugin options.
	 * ***Important***: The display of the button overrides the initialization of the modal bootstrap widget.
	 */
	public function initPluginOptions()
	{
		if (null !== $this->remote)
			$this->pluginOptions['remote'] = Html::url($this->remote);

		foreach (array('backdrop', 'keyboard', 'show') as $option) {
			$this->pluginOptions[$option] = isset($this->pluginOptions[$option])
				? $this->pluginOptions[$option]
				: $this->{$option};
		}
	}

	/**
	 * Widget's run method
	 */
	public function run()
	{
		$this->renderModal();
		$this->renderButton();
		$this->registerScript();
	}

	/**
	 * Renders the button that will open the modal if its options have been configured
	 */
	public function renderButton()
	{
		if (!empty($this->buttonOptions)) {

			$this->buttonOptions['data-toggle'] = isset($this->buttonOptions['data-toggle'])
				? $this->buttonOptions['data-toggle']
				: 'modal';

			if ($this->remote !== null && !isset($this->buttonOptions['data-remote']))
				$this->buttonOptions['data-remote'] = Html::url($this->remote);

			$label = ArrayHelper::remove($this->buttonOptions, 'label', 'Button');
			$name = ArrayHelper::remove($this->buttonOptions, 'name');
			$value = ArrayHelper::remove($this->buttonOptions, 'value');

			$attr = isset($this->buttonOptions['data-remote'])
				? 'data-target'
				: 'href';

			$this->buttonOptions[$attr] = isset($this->buttonOptions[$attr])
				? $this->buttonOptions[$attr]
				: '#' . ArrayHelper::getValue($this->options, 'id');

			echo Html::button($label, $name, $value, $this->buttonOptions);
		}
	}

	/**
	 * Renders the modal markup
	 */
	public function renderModal()
	{
		echo Html::beginTag('div', $this->options);

		$this->renderModalHeader();
		$this->renderModalBody();
		$this->renderModalFooter();

		echo Html::endTag('div');
	}

	/**
	 * Renders the header HTML markup of the modal
	 */
	public function renderModalHeader()
	{
		echo Html::beginTag('div', array('class'=>'modal-header'));
		if ($this->closeText)
			echo Html::button($this->closeText, null, null, array('data-dismiss' => 'modal', 'class'=>'close'));
		echo $this->header;
		echo Html::endTag('div');
	}

	/**
	 * Renders the HTML markup for the body of the modal
	 */
	public function renderModalBody()
	{
		echo Html::beginTag('div', array('class'=>'modal-body'));
		echo $this->content;
		echo Html::endTag('div');
	}

	/**
	 * Renders the HTML markup for the footer of the modal
	 */
	public function renderModalFooter()
	{

		echo Html::beginTag('div', array('class'=>'modal-footer'));
		echo $this->footer;
		echo Html::endTag('div');
	}

	/**
	 * Registers client scripts
	 */
	public function registerScript()
	{
		// do we render a button? If so, bootstrap will handle its behavior through its
		// mark-up, otherwise, register the plugin.
		if(empty($this->buttonOptions))
			$this->registerPlugin('modal', $this->pluginOptions);

		// register events
		$this->registerEvents($this->events);
	}

}