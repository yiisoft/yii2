<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use Yii;
use yii\data\ActiveDataProvider;
use yii\data\SearchInterface;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class IndexAction extends Action
{
    /**
     * @var callable a PHP callable that will be called to prepare a data provider that
     * should return a collection of the models. If not set, [[prepareDataProvider()]] will be used instead.
     * The signature of the callable should be:
     *
     * ```php
     * function ($action) {
     *     // $action is the action object currently running
     * }
     * ```
     *
     * The callable should return an instance of [[ActiveDataProvider]].
     */
    public $prepareDataProvider;
    /**
     * @var string class name of the search class related to this model. It can be optionally involved but not required.
     * The called search class should implement [[\yii\data\SearchInterface]] to prepare an instance of [[ActiveDataProvider]].
     * If not set, the default data provider returned by [[prepareDataProvider()]] will be used instead.
     *
     * Note that [[\yii\base\Model::formName()]] is used by default in a search class generated by **gii** when retrieving the attributes names from the URL query request.
     * If the URL endpoint should look like `/resources?attribute=value` instead of `/resources?FormName[attribute]=value`
     * then be sure that your search class is using `load($params, '')` instead of `load($params)`.
     * See [[\yii\base\Model::load()]] for more details.
     *
     * Or Alternatively you can override [[\yii\base\Model::formName()]] inside your model class to force returning an empty string:
     *
     * ```php
     * public function formName()
     * {
     *    return '';
     * }
     * ```
     * See [[\yii\base\Model::formName()]] for more details.
     *
     * @since 2.0.10
     */
    public $searchClass;


    /**
     * @return ActiveDataProvider
     */
    public function run()
    {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }

        return $this->prepareDataProvider();
    }

    /**
     * Prepares the data provider that should return the requested collection of the models.
     * @return ActiveDataProvider
     */
    protected function prepareDataProvider()
    {
        if ($this->prepareDataProvider !== null) {
            return call_user_func($this->prepareDataProvider, $this);
        }

        if ($this->searchClass instanceof SearchInterface) {
            return (new $this->searchClass)->search(Yii::$app->request->queryParams);
        }

        /* @var $modelClass \yii\db\BaseActiveRecord */
        $modelClass = $this->modelClass;

        return new ActiveDataProvider([
            'query' => $modelClass::find(),
        ]);
    }
}
