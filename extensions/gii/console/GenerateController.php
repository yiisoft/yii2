<?php
/**
 * @link      http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license   http://www.yiiframework.com/license/
 */

namespace yii\gii\console;

use Yii;
use yii\console\Controller;

/**
 * Allows you to run Gii from the command line.
 * Example command:
 *
 * ```
 * $ ./yii gii/<generator> --property1=foo --property2=bar --generate=true
 * ```
 *
 * @author Tobias Munk <schmunk@usrbin.de>
 * @since  2.0
 */
class GenerateController extends Controller
{
    /**
     * @var \yii\gii\Module
     */
    public $module;
    /**
     * @var boolean whether to generate all files and overwrite existing files
     */
    public $generate = false;
    public $generators = [];

    /**
     * @var array generator option values
     */
    private $_options = [];


    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        return isset($this->_options[$name]) ? $this->_options[$name] : null;
        // todo: should determine which options are valid
        if ($this->action) {
            $options = $this->options($this->action->id);
            if (in_array($name, $options)) {
                return isset($this->_options[$name]) ? $this->_options[$name] : null;
            } else {
                return parent::__get($name);
            }
        } elseif (array_key_exists($name, $this->_options)) {
            return $this->_options[$name];
        } else {
            return parent::__get($name);
        }
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        $this->_options[$name] = $value;
        return;
        // todo: should determine which options are valid
        if ($this->action) {
            $options = $this->options($this->action->id);
            if (in_array($name, $options)) {
                $this->_options[$name] = $value;
            } else {
                parent::__set($name, $value);
            }
        } else {
            $this->_options[$name] = $value;
        }
    }

    public function init()
    {
        parent::init();
        foreach ($this->generators as $id => $config) {
            $this->generators[$id] = Yii::createObject($config);
        }
    }

    public function createAction($id)
    {
        $action = parent::createAction($id);
        foreach ($this->_options as $name => $value) {
            $action->generator->$name = $value;
        }
        return $action;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = [];
        foreach ($this->generators as $name => $generator) {
            $actions[$name] = [
                'class' => 'yii\gii\console\Action',
                'generator' => $generator,
            ];
        }
        return $actions;
    }

    public function getUniqueID()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function options($id)
    {
        if (isset($this->generators[$id])) {
            $attributes = $this->generators[$id]->attributes;
            unset($attributes['templates']);
            return array_merge(
                parent::options($id),
                array_keys($attributes)
            );
        } else {
            return parent::options($id);
        }
    }

    /**
     * @inheritdoc
     */
    public function getActionHelpSummary($action)
    {
        /** @var $action Action */
        return $action->generator->getName();
    }

    /**
     * @inheritdoc
     */
    public function getActionHelp($action)
    {
        /** @var $action Action */
        $description = $action->generator->getDescription();
        return wordwrap(preg_replace('/\s+/', ' ', $description));
    }

    /**
     * @inheritdoc
     */
    public function getActionArgsHelp($action)
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getActionOptionsHelp($action)
    {
        /** @var $action Action */
        $attributes = $action->generator->attributes;
        unset($attributes['templates']);
        $hints = $action->generator->hints();

        $options = [];
        foreach ($attributes as $name => $value) {
            $type = gettype($value);
            $options[$name] = [
                'type' => $type === 'NULL' ? 'string' : $type,
                'required' => $action->generator->isAttributeRequired($name),
                'default' => $value,
                'comment' => isset($hints[$name]) ? $this->formatHint($hints[$name]) : '',
            ];
        }

        return $options;
    }

    protected function formatHint($hint)
    {
        $hint = preg_replace('%<code>(.*?)</code>%', '\1', $hint);
        $hint = preg_replace('/\s+/', ' ', $hint);
        return wordwrap($hint);
    }
}
