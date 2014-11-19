<?php
/* @var $panel yii\debug\panels\AssetPanel */

use yii\helpers\Html;
use yii\helpers\Inflector;
?>
<h1>Asset Bundles</h1>

<?php if (empty($panel->data)) {
    echo '<p>No asset bundle was used.</p>';
    return;
} ?>
<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <caption>
            <p>Total <b><?= count($panel->data) ?></b> asset bundles were loaded.</p>
        </caption>
    <?php
    foreach ($panel->data as $name => $bundle) {
    ?>
        <thead>
            <tr>
                <td colspan="2"><h3 id="<?= Inflector::camel2id($name) ?>"><?= $name ?></h3></td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>sourcePath</th>
                <td><?= Html::encode($bundle['sourcePath'] !== null ? $bundle['sourcePath'] : $bundle['basePath']) ?></td>
            </tr>
            <?php if ($bundle['basePath'] !== null): ?>
                <tr>
                    <th>basePath</th>
                    <td><?= Html::encode($bundle['basePath']) ?></td>
                </tr>
            <?php endif; ?>
            <?php if ($bundle['baseUrl'] !== null): ?>
                <tr>
                    <th>baseUrl</th>
                    <td><?= Html::encode($bundle['baseUrl']) ?></td>
                </tr>
            <?php endif; ?>
            <?php if (!empty($bundle['css'])): ?>
            <tr>
                <th>css</th>
                <td><?= Html::ul($bundle['css'], ['class' => 'assets']) ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($bundle['js'])): ?>
            <tr>
                <th>js</th>
                <td><?= Html::ul($bundle['js'], ['class' => 'assets']) ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($bundle['depends'])): ?>
            <tr>
                <th>depends</th>
                <td><ul class="assets">
                    <?php foreach ($bundle['depends'] as $depend): ?>
                        <li><?= Html::a($depend, '#' . Inflector::camel2id($depend)) ?></li>
                    <?php endforeach; ?>
                </ul></td>
            </tr>
            <?php endif; ?>
        </tbody>
    <?php
    }
    ?>
    </table>
</div>
