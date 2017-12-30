<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jquery;

use Yii;
use yii\base\Behavior;
use yii\base\Widget;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * GridViewClientScript is a behavior for [[\yii\grid\GridView]] widget, which allows automatic filter submission via jQuery component.
 *
 * A basic usage looks like the following:
 *
 * ```php
 * <?= yii\grid\GridView::widget([
 *     'dataProvider' => $dataProvider,
 *     'as clientScript' => [
 *         'class' => yii\jquery\GridViewClientScript::class
 *     ],
 *     'columns' => [
 *         'id',
 *         'name',
 *         'created_at:datetime',
 *         // ...
 *     ],
 * ]) ?>
 * ```
 *
 * @see \yii\grid\GridView
 * @see GridViewAsset
 *
 * @property \yii\grid\GridView $owner the owner of this behavior.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
class GridViewClientScript extends Behavior
{
    /**
     * @var string additional jQuery selector for selecting filter input fields.
     */
    public $filterSelector;


    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            Widget::EVENT_BEFORE_RUN => 'beforeRun'
        ];
    }

    /**
     * Handles [[Widget::EVENT_BEFORE_RUN]] event, registering related client script.
     * @param \yii\base\Event $event event instance.
     */
    public function beforeRun($event)
    {
        $id = $this->owner->options['id'];
        $options = Json::htmlEncode($this->getClientOptions());
        $view = $this->owner->getView();
        GridViewAsset::register($view);
        $view->registerJs("jQuery('#$id').yiiGridView($options);");
    }

    /**
     * Returns the options for the grid view JS widget.
     * @return array the options
     */
    protected function getClientOptions()
    {
        $filterUrl = isset($this->owner->filterUrl) ? $this->owner->filterUrl : Yii::$app->request->url;
        $id = $this->owner->filterRowOptions['id'];
        $filterSelector = "#$id input, #$id select";
        if (isset($this->filterSelector)) {
            $filterSelector .= ', ' . $this->filterSelector;
        }

        return [
            'filterUrl' => Url::to($filterUrl),
            'filterSelector' => $filterSelector,
        ];
    }
}