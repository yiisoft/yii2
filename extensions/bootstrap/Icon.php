<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use yii\helpers\Html;

/**
 * Icon renders a glyphicon icon.
 *
 * For example:
 *
 * ```php
 * echo Button::widget([
 *     'label' => 'Action',
 *     'options' => ['class' => 'btn-lg btn-primary'],
 *     'icon' => Icon::icon('user'),
 * ]);
 * ```
 */
class Icon extends \yii\base\Widget
{
    /**
     * @var string the tag to use to render the icon
     */
    public $tagName = 'span';

    /**
     * @var string the icon name
     */
    public $icon;

    /**
     * @var array the HTML attributes for the icon tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        Html::addCssClass($this->options, 'glyphicon glyphicon-' . $this->icon);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return Html::tag($this->tagName, '', $this->options);
    }

    /**
     * Render an icon.
     *
     * @param string $icon the icon name
     * @return string the rendering result.
     */
    public static function icon($icon)
    {
        return static::widget(['icon' => $icon]);
    }
}
