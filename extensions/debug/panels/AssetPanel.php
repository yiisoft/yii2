<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\helpers\Html;
use yii\debug\Panel;
use yii\web\AssetBundle;
use yii\web\AssetManager;

/**
 * Debugger panel that collects and displays asset bundles data.
 *
 * @author Artur Fursa <arturfursa@gmail.com>
 * @since 2.0
 */
class AssetPanel extends Panel
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Asset Bundles';
    }

    /**
     * @inheritdoc
     */
    public function getSummary()
    {
        return Yii::$app->view->render('panels/assets/summary', ['panel' => $this]);
    }

    /**
     * @inheritdoc
     */
    public function getDetail()
    {
        return Yii::$app->view->render('panels/assets/detail', ['panel' => $this]);
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        $bundles = Yii::$app->view->assetManager->bundles;
        if (empty($bundles)) {
            return [];
        }
        $data = [];
        foreach ($bundles as $name => $bundle) {
            if ($bundle instanceof AssetBundle) {
                $data[$name] = (array) $bundle;
            }
        }
        return $data;
    }

    /**
     * Additional formatting for view.
     * 
     * @param AssetBundle[] $bundles Array of bundles to formatting.
     * 
     * @return AssetManager
     */
    protected function format(array $bundles)
    {
        foreach ($bundles as $bundle) {

            $this->cssCount += count($bundle->css);
            $this->jsCount += count($bundle->js);
            
            array_walk($bundle->css, function(&$file, $key, $userdata) {
                $file = Html::a($file, $userdata->baseUrl . '/' . $file, ['target' => '_blank']);
            }, $bundle);

            array_walk($bundle->js, function(&$file, $key, $userdata) {
                $file = Html::a($file, $userdata->baseUrl . '/' . $file, ['target' => '_blank']);
            }, $bundle);

            array_walk($bundle->depends, function(&$depend) {
                $depend = Html::a($depend, '#' . $depend);
            });
            
            $this->formatOptions($bundle->publishOptions);
            $this->formatOptions($bundle->jsOptions);
            $this->formatOptions($bundle->cssOptions);
        }
        
        return $bundles;
    }

    /**
     * Format associative array of params to simple value.
     * 
     * @param array $params
     *
     * @return array
     */
    protected function formatOptions(array &$params)
    {
        if (!is_array($params)) {
            return $params;
        }
        
        foreach ($params as $param => $value) {
            $params[$param] = Html::tag('strong', '\'' . $param . '\' => ') . (string) $value;
        }
        
        return $params;
    }
}
