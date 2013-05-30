<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use yii\base\InvalidConfigException;
use yii\helpers\Html;


/**
 * Button renders a bootstrap button.
 *
 * For example,
 *
 * ```php
 * echo Button::widget(array(
 *     'label' => 'Action',
 *     'options' => array('class' => 'btn-large'),
 * ));
 * ```
 * @see http://twitter.github.io/bootstrap/javascript.html#buttons
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class Button extends Widget
{
    /**
     * @var string the tag to use to render the button
     */
    public $tagName = 'button';
    /**
     * @var string the button label
     */
    public $label;
    /**
     * @var boolean whether the label should be HTML-encoded.
     */
    public $encodeLabel = true;


    /**
     * Initializes the widget.
     * If you override this method, make sure you call the parent implementation first.
     * @throws InvalidConfigException
     */
    public function init()
    {
        if ($this->label === null) {
            throw new InvalidConfigException("The 'label' option is required.");
        }
        parent::init();
        $this->clientOptions = false;
        $this->addCssClass($this->options, 'btn');
        $this->label = $this->encodeLabel ? Html::encode($this->label) : $this->label;
    }

    /**
     * Renders the widget.
     */
    public function run()
    {
        echo Html::tag($this->tagName, $this->label, $this->options) . "\n";
        $this->registerPlugin('button');
    }
}