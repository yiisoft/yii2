<?php

use yii\helpers\StringHelper;

/**
 * This is the template for generating a CRUD controller class file.
 *
 * @var yii\base\View $this
 * @var yii\gii\generators\crud\Generator $generator
 */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);

$pks = $generator->getTableSchema()->primaryKey;
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();

echo "<?php\n";
?>

namespace <?php echo StringHelper::dirname(ltrim($generator->controllerClass, '\\')); ?>;

use <?php echo ltrim($generator->modelClass, '\\'); ?>;
use <?php echo ltrim($generator->searchModelClass, '\\'); ?>;
use yii\data\ActiveDataProvider;
use <?php echo ltrim($generator->baseControllerClass, '\\'); ?>;
use yii\web\HttpException;
use yii\web\VerbFilter;

/**
 * <?php echo $controllerClass; ?> implements the CRUD actions for <?php echo $modelClass; ?> model.
 */
class <?php echo $controllerClass; ?> extends <?php echo StringHelper::basename($generator->baseControllerClass) . "\n"; ?>
{
	public function behaviors()
	{
		return array(
			'verbs' => array(
				'class' => VerbFilter::className(),
				'actions' => array(
					'delete' => array('post'),
				),
			),
		);
	}

	/**
	 * Lists all <?php echo $modelClass; ?> models.
	 * @return mixed
	 */
	public function actionIndex()
	{
		$searchModel = new <?php echo $searchModelClass; ?>;
		$dataProvider = $searchModel->search($_GET);

		return $this->render('index', array(
			'dataProvider' => $dataProvider,
			'searchModel' => $searchModel,
		));
	}

	/**
	 * Displays a single <?php echo $modelClass; ?> model.
	 * <?php echo implode("\n\t * ", $actionParamComments) . "\n"; ?>
	 * @return mixed
	 */
	public function actionView(<?php echo $actionParams; ?>)
	{
		return $this->render('view', array(
			'model' => $this->findModel(<?php echo $actionParams; ?>),
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
			return $this->redirect(array('view', <?php echo $urlParams; ?>));
		} else {
			return $this->render('create', array(
				'model' => $model,
			));
		}
	}

	/**
	 * Updates an existing <?php echo $modelClass; ?> model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * <?php echo implode("\n\t * ", $actionParamComments) . "\n"; ?>
	 * @return mixed
	 */
	public function actionUpdate(<?php echo $actionParams; ?>)
	{
		$model = $this->findModel(<?php echo $actionParams; ?>);

		if ($model->load($_POST) && $model->save()) {
			return $this->redirect(array('view', <?php echo $urlParams; ?>));
		} else {
			return $this->render('update', array(
				'model' => $model,
			));
		}
	}

	/**
	 * Deletes an existing <?php echo $modelClass; ?> model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * <?php echo implode("\n\t * ", $actionParamComments) . "\n"; ?>
	 * @return mixed
	 */
	public function actionDelete(<?php echo $actionParams; ?>)
	{
		$this->findModel(<?php echo $actionParams; ?>)->delete();
		return $this->redirect(array('index'));
	}

	/**
	 * Finds the <?php echo $modelClass; ?> model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * <?php echo implode("\n\t * ", $actionParamComments) . "\n"; ?>
	 * @return <?php echo $modelClass; ?> the loaded model
	 * @throws HttpException if the model cannot be found
	 */
	protected function findModel(<?php echo $actionParams; ?>)
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
