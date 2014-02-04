<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\debug\models\search\Debug;

/**
 * Debugger controller
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DefaultController extends Controller
{
	/**
	 * @inheritdoc
	 */
	public $layout = 'main';
	/**
	 * @var \yii\debug\Module
	 */
	public $module;
	/**
	 * @var array the summary data (e.g. URL, time)
	 */
	public $summary;

	/**
	 * @inheritdoc
	 */
	public function actions()
	{
		$actions = [];
		foreach($this->module->panels as $panel) {
			$actions = array_merge($actions, $panel->actions);
		}
		return $actions;
	}

	public function actionIndex()
	{
		$searchModel = new Debug();
		$dataProvider = $searchModel->search($_GET, $this->getManifest());

		return $this->render('index', [
			'dataProvider' => $dataProvider,
			'searchModel' => $searchModel,
		]);
	}

	public function actionView($tag = null, $panel = null)
	{
		if ($tag === null) {
			$tags = array_keys($this->getManifest());
			$tag = reset($tags);
		}
		$this->loadData($tag);
		if (isset($this->module->panels[$panel])) {
			$activePanel = $this->module->panels[$panel];
		} else {
			$activePanel = $this->module->panels['request'];
		}
		return $this->render('view', [
			'tag' => $tag,
			'summary' => $this->summary,
			'manifest' => $this->getManifest(),
			'panels' => $this->module->panels,
			'activePanel' => $activePanel,
		]);
	}

	public function actionToolbar($tag)
	{
		$this->loadData($tag, 5);
		return $this->renderPartial('toolbar', [
			'tag' => $tag,
			'panels' => $this->module->panels,
			'position' => 'bottom',
		]);
	}

	public function actionPhpinfo()
	{
		phpinfo();
	}

	private $_manifest;

	protected function getManifest($forceReload = false)
	{
		if ($this->_manifest === null || $forceReload) {
			if ($forceReload) {
				clearstatcache();
			}
			$indexFile = $this->module->dataPath . '/index.data';
			if (is_file($indexFile)) {
				$this->_manifest = array_reverse(unserialize(file_get_contents($indexFile)), true);
			} else {
				$this->_manifest = [];
			}
		}
		return $this->_manifest;
	}

	public function loadData($tag, $maxRetry = 0)
	{
		// retry loading debug data because the debug data is logged in shutdown function
		// which may be delayed in some environment if xdebug is enabled.
		// See: https://github.com/yiisoft/yii2/issues/1504
		for ($retry = 0; $retry <= $maxRetry; ++$retry) {
			$manifest = $this->getManifest($retry > 0);
			if (isset($manifest[$tag])) {
				$dataFile = $this->module->dataPath . "/$tag.data";
				$data = unserialize(file_get_contents($dataFile));
				foreach ($this->module->panels as $id => $panel) {
					if (isset($data[$id])) {
						$panel->tag = $tag;
						$panel->load($data[$id]);
					} else {
						// remove the panel since it has not received any data
						unset($this->module->panels[$id]);
					}
				}
				$this->summary = $data['summary'];
				return;
			}
			sleep(1);
		}

		throw new NotFoundHttpException("Unable to find debug data tagged with '$tag'.");
	}
}
