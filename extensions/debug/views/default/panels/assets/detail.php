<?php
/* @var $panel yii\debug\panels\AssetPanel */
/* @var $bundles \yii\web\AssetBundle[] array */

use yii\helpers\Html;
?>
<h1>Asset bundles</h1>


<table class="table table-striped table-bordered">
    <caption>
        <p><b>Total bundles: <?= $panel->data['totalBundles'] ?></b>.</p>
        <p>CSS files: <?= $panel->data['totalCssFiles'] ?>, JS files: <?= $panel->data['totalJsFiles'] ?></p>
    </caption>
<?php
foreach ($panel->data['bundles'] as $key => $bundle) {
?>
    <thead>
        <tr>
            <td colspan="2"><h3 id="<?= $key ?>"><?= $key ?></h3></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th>sourcePath</th>
            <td><?= Html::ul([$bundle->sourcePath], ['class' => 'trace', 'encode' => false]) ?></td>
        </tr>
        <tr>
            <th>css</th>
            <td><?= Html::ul($bundle->css, ['class' => 'trace', 'encode' => false]) ?></td>
        </tr>
        <tr>
            <th>js</th>
            <td><?= Html::ul($bundle->js, ['class' => 'trace', 'encode' => false]) ?></td>
        </tr>
        <tr>
            <th>depends</th>
            <td><?= Html::ul($bundle->depends, ['class' => 'trace', 'encode' => false]) ?></td>
        </tr>
        <tr>
            <th>publishOptions</th>
            <td><?= Html::ul($bundle->publishOptions, ['class' => 'trace', 'encode' => false]) ?></td>
        </tr>
        <tr>
            <th>basePath</th>
            <td><?= Html::ul([$bundle->basePath], ['class' => 'trace', 'encode' => false]) ?></td>
        </tr>
        <tr>
            <th>baseUrl</th>
            <td><?= Html::ul([$bundle->baseUrl], ['class' => 'trace', 'encode' => false]) ?></td>
        </tr>
        <tr>
            <th>jsOptions</th>
            <td><?= Html::ul($bundle->jsOptions, ['class' => 'trace']) ?></td>
        </tr>
        <tr>
            <th>cssOptions</th>
            <td><?= Html::ul($bundle->cssOptions, ['class' => 'trace']) ?></td>
        </tr>
    </tbody>
<?php
}
?>
</table>
