<?php

use yii\helpers\Inflector;

/**
 * This is the template for generating an action view file.
 *
 * @var yii\web\View $this
 * @var yii\gii\generators\form\Generator $generator
 */

echo "<?php\n";
?>

public function action<?= Inflector::id2camel(trim(basename($generator->viewName), '_')) ?>()
{
    $model = new <?= $generator->modelClass ?><?= empty($generator->scenarioName) ? "" : "(['scenario' => '{$generator->scenarioName}'])" ?>;

    if ($model->load(Yii::$app->request->post())) {
        if ($model->validate()) {
            // form inputs are valid, do something here
            return;
        }
    }

    return $this->render('<?= $generator->viewName ?>', [
        'model' => $model,
    ]);
}
