<?php
namespace yii\base;

/**
 * Lazy is used for constructor injection with the dependency injection container. 
 * The Lazy object works as a wrapper around dependencies that are expensive to instantiate.
 * Instead of instantiating the required dependency immediately,
 * it will only be done once it is used for the first time.
 *
 * Example:
 *
 * -- in the dependency injection configuration during bootstrapping
 * -- register an alias for an expensive dependency
 * 
 *       \Yii::$container->set('expensiveService', 'app\models\BloatedService');
 *
 * -- then use the alias as a constructor parameter name where the dependency is required
 *
 *       class SiteController extends \yii\web\Controller
 *       {
 *           private $cheapRepo, $costlyService;
 *       
 *           function __construct($id, $module,
 *               \app\models\SomeRepository $cheapRepository,
 * // type must be \yii\base\Lazy and parameter name must match registered alias
 *               \yii\base\Lazy $expensiveService,
 *               $config = [])
 *           {
 *               parent::__construct($id, $module, $config);
 *               $this->cheapRepo = $cheapRepository;
 * // an 'empty' Lazy wrapper is injected
 *               $this->costlyService = $expensiveService;
 *           }
 *       
 *           function actionIndex()
 *           {
 *               $someModel = $this->cheapRepo->getSome();
 * //first call to getInstance() lazily instantiates BloatedService
 *               $this->costlyService->getInstance()->workOn($someModel);
 * //subsequent calls to getInstance() use cached instance of BloatedService
 *               $this->costlyService->getInstance()->workSomeMoreOn($someModel);
 *               return $this->render("index", ['model' => $someModel]);
 *           }
 *       }
 */ 
class Lazy
{
    private $alias, $instance;

    /**
     * Creates the lazy wrapper.
     * @param string $alias An alias for a type registered with the dependency injection container.
     * @return Lazy The Lazy wrapper instance.
     */
    function __construct($alias)
    {
        $this->alias = $alias;
    }

    /**
     * Returns an instance of the wrapped dependency.
     * The instance is created on the first access and cached for subsequent calls.
     * @return mixed The instance of the wrapped dependency.
     */
    function getInstance()
    {
        if($this->instance === null) {
            $this->instance = \Yii::$container->get($this->alias);
        }

        return $this->instance;
    }
}
?>