<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;
use yii\console\controllers\HelpController;

/**
 * Exception represents an exception caused by incorrect usage of a console command.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0.11
 */
class UnknownCommandException extends Exception
{
    public $command;
    /**
     * @var Application
     */
    public $application;

    public function __construct($route, $application, $code = 0, \Exception $previous = null)
    {
        $this->command = $route;
        $this->application = $application;
        parent::__construct("Unknown command \"$route\".", $code, $previous);
    }

    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Unknown command';
    }

    public function suggestAlternatives()
    {
        $help = $this->application->createController('help');
        if ($help === false) {
            return [];
        }
        /** @var $helpController HelpController */
        list($helpController, $actionID) = $help;

        $availableActions = [];
        $commands = $helpController->getCommands();
        foreach ($commands as $command) {
            $result = $this->application->createController($command);
            if ($result === false) {
                continue;
            }
            // add the command itself (default action)
            $availableActions[] = $command;

            // add all actions of this controller
            /** @var $controller Controller */
            list($controller, $actionID) = $result;
            $actions = $helpController->getActions($controller);
            if (!empty($actions)) {
                $prefix = $controller->getUniqueId();
                foreach ($actions as $action) {
                    $availableActions[] = $prefix . '/' . $action;
                }
            }
        }
        $availableActions = $this->filterBySimilarity($availableActions);

        asort($availableActions);
        return $availableActions;
    }

    private function filterBySimilarity($actions)
    {
        // TODO
        return $actions;
    }
}
