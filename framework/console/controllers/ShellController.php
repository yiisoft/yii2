<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use yii\console\Controller;
use Psy\Shell;
use Psy\Configuration;

/**
 * Runs interactive shell
 *
 * @author Daniel Gomez Pan <pana_1990@hotmail.com>
 */
class ShellController extends Controller
{
    /**
     * @var array include file(s) before starting tinker shell
     */
    public $include = [];

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'include'
        ]);
    }

    public function actionIndex()
    {
        $config = new Configuration;

        $config->getPresenter()->addCasters(
            $this->getCasters()
        );

        $shell = new Shell($config);
        $shell->run();
    }

    /**
     * @return array casters for psysh
     */
    protected function getCasters()
    {
        return [
            'yii\db\ActiveRecord' => 'yii\console\ShellCaster::castModel',
        ];
    }
}
