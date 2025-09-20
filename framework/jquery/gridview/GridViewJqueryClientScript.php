<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yii\jquery\gridview;

use Yii;
use yii\base\BaseObject;
use yii\grid\GridView;
use yii\grid\GridViewAsset;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\client\ClientScriptInterface;
use yii\web\View;

/**
 * GridViewJqueryClientScript provides client-side script registration for GridView widgets using jQuery.
 *
 * This class implements {@see ClientScriptInterface} to supply client-side options and register the corresponding
 * JavaScript code for GridView widgets in Yii2 applications using jQuery.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.2.0
 */
class GridViewJqueryClientScript implements ClientScriptInterface
{
    public function register(BaseObject $object, View $view): void
    {
        $view = $object->getView();

        GridViewAsset::register($view);

        $id = $object->options['id'];

        $options = Json::htmlEncode(
            array_merge($this->getClientOptions($object), ['filterOnFocusOut' => $object->filterOnFocusOut]),
        );

        $view->registerJs("jQuery('#$id').yiiGridView($options);");
    }

    public function getClientOptions(BaseObject $object): array
    {
        if (!$object instanceof GridView) {
            return [];
        }

        $filterUrl = isset($object->filterUrl) ? $object->filterUrl : Yii::$app->request->url;
        $id = $object->filterRowOptions['id'];
        $filterSelector = "#$id input, #$id select";

        if (isset($object->filterSelector)) {
            $filterSelector .= ', ' . $object->filterSelector;
        }

        return [
            'filterUrl' => Url::to($filterUrl),
            'filterSelector' => $filterSelector,
        ];
    }
}
