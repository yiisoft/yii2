<?php

use yii\helpers\StringHelper;

/**
 * This is the template for generating a CRUD controller class file.
 *
 * @var yii\base\View $this
 * @var yii\gii\generators\crud\Generator $generator
 */

$pos = strrpos($generator->controllerClass, '\\');
$ns = ltrim(substr($generator->controllerClass, 0, $pos), '\\');
$controllerClass = substr($generator->controllerClass, $pos + 1);
$pos = strrpos($generator->modelClass, '\\');
$modelClass = $pos === false ? $generator->modelClass : substr($generator->modelClass, $pos + 1);

/** @var \yii\db\ActiveRecord $class */
$class = $generator->modelClass;
$pks = $class::primaryKey();
$schema = $class::getTableSchema();
if (count($pks) === 1) {
	$ids = '$id';
	$params = "array('id' => \$model->{$pks[0]})";
	$paramComments = '@param ' . $schema->columns[$pks[0]]->phpType . ' $id';
} else {
	$ids = '$' . implode(', $', $pks);
	$params = array();
	$paramComments = array();
	foreach ($pks as $pk) {
		$paramComments[] = '@param ' . $schema->columns[$pk]->phpType . ' $' . $pk;
		$params[] = "'$pk' => \$model->$pk";
	}
	$params = implode(', ', $params);
	$paramComments = implode("\n\t * ", $paramComments);
}

echo "<?php\n";
?>

namespace <?php echo $ns; ?>;

use <?php echo ltrim($generator->modelClass, '\\'); ?>;
use yii\data\ActiveDataProvider;
use <?php echo ltrim($generator->baseControllerClass, '\\'); ?>;
use yii\web\HttpException;

/**
 * <?php echo $controllerClass; ?> implements the CRUD actions for <?php echo $modelClass; ?> model.
 */
class <?php echo $controllerClass; ?> extends <?php echo StringHelper::basename($generator->baseControllerClass) . "\n"; ?>
{
	/**
	 * Lists all <?php echo $modelClass; ?> models.
	 * @return mixed
	 */
	public function actionIndex()
	{
		$dataProvider = new ActiveDataProvider(array(
			'query' => <?php echo $modelClass; ?>::find(),
		));
		return $this->render('index', array(
			'dataProvider' => $dataProvider,
		));
	}

	/**
	 * Displays a single <?php echo $modelClass; ?> model.
	 * <?php echo $paramComments . "\n"; ?>
	 * @return mixed
	 */
	public function actionView(<?php echo $ids; ?>)
	{
		return $this->render('view', array(
			'model' => $this->findModel(<?php echo $ids; ?>),
		));
	}

	/**
	 * Creates a new <?php echo $modelClass; ?> model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 * @return mixed
	 */
	public function actionCreate()
	{
		$model = new <?php echo $modelClass; ?>;

		if ($model->load($_POST) && $model->save()) {
			return $this->redirect(array('view', <?php echo $params; ?>));
		} else {
			return $this->render('create', array(
				'model' => $model,
			));
		}
	}

	/**
	 * Updates an existing <?php echo $modelClass; ?> model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * <?php echo $paramComments . "\n"; ?>
	 * @return mixed
	 */
	public function actionUpdate(<?php echo $ids; ?>)
	{
		$model = $this->findModel(<?php echo $ids; ?>);

		if ($model->load($_POST) && $model->save()) {
			return $this->redirect(array('view', <?php echo $params; ?>));
		} else {
			return $this->render('update', array(
				'model' => $model,
			));
		}
	}

	/**
	 * Deletes an existing <?php echo $modelClass; ?> model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * <?php echo $paramComments . "\n"; ?>
	 * @return mixed
	 */
	public function actionDelete(<?php echo $ids; ?>)
	{
		$this->findModel(<?php echo $ids; ?>)->delete();
		return $this->redirect(array('index'));
	}

	/**
	 * Finds the <?php echo $modelClass; ?> model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * <?php echo $paramComments . "\n"; ?>
	 * @return <?php echo $modelClass; ?> the loaded model
	 * @throws HttpException if the model cannot be found
	 */
	protected function findModel(<?php echo $ids; ?>)
	{
<?php
if (count($pks) === 1) {
	$condition = '$id';
} else {
	$condition = array();
	foreach ($pks as $pk) {
		$condition[] = "'$pk' => \$$pk";
	}
	$condition = 'array(' . implode(', ', $condition) . ')';
}
?>
		if (($model = <?php echo $modelClass; ?>::find(<?php echo $condition; ?>)) !== null) {
			return $model;
		} else {
			throw new HttpException(404, 'The requested page does not exist.');
		}
	}
}
