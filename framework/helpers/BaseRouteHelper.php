<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use Yii;

/**
 *
 *
 * Do not use BaseRouteHelper. Use [[RouteHelper]] instead.
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.0.10
 */
class BaseRouteHelper
{

    /**
     * Returns all available route names.
     * @return array all available route names
     */
    public static function getRoutes($action = false)
    {
        $routes = static::getModuleRoutes(Yii::$app, $action);
        sort($routes);
        return array_unique($routes);
    }

    /**
     * Returns available routes of a specified module.
     * @param \yii\base\Module $module the module instance
     * @return array the available route names
     */
    protected static function getModuleRoutes($module, $action)
    {
        $prefix = $module instanceof \yii\base\Application ? '' : $module->getUniqueId() . '/';

        $routes = [];
        foreach (array_keys($module->controllerMap) as $id) {
            $routes[] = $prefix . $id;
            if ($action) {
                $controller = Yii::createObject($module->controllerMap[$id], [$id, $module]);
                foreach (static::getActions($controller) as $actionId) {
                    $routes[] = $prefix . $id . '/' . $actionId;
                }
            }
        }

        foreach ($module->getModules() as $id => $child) {
            if (($child = $module->getModule($id)) === null) {
                continue;
            }
            foreach (static::getModuleRoutes($child) as $route) {
                $routes[] = $route;
            }
        }

        $controllerPath = $module->getControllerPath();
        if (is_dir($controllerPath)) {
            $files = scandir($controllerPath);
            foreach ($files as $file) {
                if (!empty($file) && substr_compare($file, 'Controller.php', -14, 14) === 0) {
                    $controllerClass = $module->controllerNamespace . '\\' . substr(basename($file), 0, -4);
                    if (static::validateControllerClass($controllerClass)) {
                        $id = Inflector::camel2id(substr(basename($file), 0, -14));
                        $routes[] = $prefix . $id;
                        if ($action) {
                            $controller = Yii::createObject($controllerClass, [$id, $module]);
                            foreach (static::getActions($controller) as $actionId) {
                                $routes[] = $prefix . $id . '/' . $actionId;
                            }
                        }
                    }
                }
            }
        }

        return $routes;
    }

    /**
     * Validates if the given class is a valid console controller class.
     * @param string $controllerClass
     * @return boolean
     */
    protected static function validateControllerClass($controllerClass)
    {
        if (class_exists($controllerClass)) {
            $class = new \ReflectionClass($controllerClass);
            $parentClass = Yii::$app instanceof \yii\console\Application ? 'yii\console\Controller' : 'yii\web\Controller';
            return !$class->isAbstract() && $class->isSubclassOf($parentClass);
        } else {
            return false;
        }
    }

    /**
     * Returns all available actions of the specified controller.
     * @param Controller $controller the controller instance
     * @return array all available action IDs.
     */
    public static function getActions($controller)
    {
        $actions = array_keys($controller->actions());
        $class = new \ReflectionClass($controller);
        foreach ($class->getMethods() as $method) {
            $name = $method->getName();
            if ($name !== 'actions' && $method->isPublic() && !$method->isStatic() && strpos($name, 'action') === 0) {
                $actions[] = Inflector::camel2id(substr($name, 6), '-', true);
            }
        }

        return array_unique($actions);
    }

    /**
     *
     * @param string $name
     * @param array $routes
     * @return array
     */
    public static function find($name, $routes)
    {
        $result = $routeParts = [];
        foreach ($routes as $route) {
            $routeParts[$route] = explode('/', $route);
        }

        foreach (explode('/', $name) as $subname) {
            if ($subname === '') {
                continue;
            }
            $len = strlen($subname);
            foreach ($routeParts as $route => $parts) {
                foreach ($parts as $part) {
                    $lev = (strpos($part, $subname) === false) ? 3.0 * levenshtein($subname, $part) / $len : 0;
                    if ($lev <= 1 && (!isset($result[$route]) || $lev < $result[$route])) {
                        $result[$route] = $lev;
                    }
                }
            }
        }

        foreach ($routes as $route) {
            $lev = (strpos($route, $name) === false) ? 3.0 * levenshtein($name, $route) / strlen($name) : 0;
            if ($lev <= 1 && (!isset($result[$route]) || $lev < $result[$route])) {
                $result[$route] = $lev;
            }
        }

        $result = array_filter($result, function ($lev) {
            return $lev <= 1;
        });
        asort($result);

        return array_keys($result);
    }
}
