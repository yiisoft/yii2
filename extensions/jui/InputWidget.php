<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jui;

use yii\base\Model;
use yii\base\InvalidConfigException;
use yii\helpers\Html;

/**
 * InputWidget is the base class for all jQuery UI input widgets.
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class InputWidget extends Widget
{
    /**
     * @var Model the data model that this widget is associated with.
     */
    public $model;
    /**
     * @var string the model attribute that this widget is associated with.
     */
    public $attribute;
    /**
     * @var string the input name. This must be set if [[model]] and [[attribute]] are not set.
     */
    public $name;
    /**
     * @var string the input value.
     */
    public $value;


    /**
     * Initializes the widget.
     * If you override this method, make sure you call the parent implementation first.
     */
    public function init()
    {
        if (!$this->hasModel() && $this->name === null) {
            throw new InvalidConfigException("Either 'name', or 'model' and 'attribute' properties must be specified.");
        }
        if ($this->hasModel() && !isset($this->options['id'])) {
            $this->options['id'] = Html::getInputId($this->model, $this->attribute);
        }
        parent::init();
    }

    /**
     * @return boolean whether this widget is associated with a data model.
     */
    protected function hasModel()
    {
        return $this->model instanceof Model && $this->attribute !== null;
    }
}
