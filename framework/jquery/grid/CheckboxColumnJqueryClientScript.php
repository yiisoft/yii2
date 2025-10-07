<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yii\jquery\grid;

use yii\base\BaseObject;
use yii\grid\CheckboxColumn;
use yii\helpers\Json;
use yii\web\client\ClientScriptInterface;
use yii\web\View;

/**
 * CheckboxColumnJqueryClientScript provides client-side script registration for gridview checkbox columns.
 *
 * This class implements {@see ClientScriptInterface} to supply client-side options and register the corresponding
 * JavaScript code for checkbox selection columns in Yii2 gridviews using jQuery.
 *
 * @implements ClientScriptInterface<CheckboxColumn>
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.2.0
 */
class CheckboxColumnJqueryClientScript implements ClientScriptInterface
{
    public function register(BaseObject $object, View $view, array $options = []): void
    {
        $id = $object->grid->options['id'];

        $options = Json::encode(
            [
                'name' => $object->name,
                'class' => $object->cssClass,
                'multiple' => $object->multiple,
                'checkAll' => $options['checkAll'] ?? null,
            ],
        );

        $view->registerJs("jQuery('#$id').yiiGridView('setSelectionColumn', $options);");
    }

    public function getClientOptions(BaseObject $object, array $options = []): array
    {
        return [];
    }
}
