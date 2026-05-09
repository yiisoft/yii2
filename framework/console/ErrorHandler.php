<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\console;

use Yii;
use yii\base\ErrorException;
use yii\base\UserException;
use yii\helpers\Console;

/**
 * ErrorHandler handles uncaught PHP errors and exceptions.
 *
 * ErrorHandler is configured as an application component in [[\yii\base\Application]] by default.
 * You can access that instance via `Yii::$app->errorHandler`.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ErrorHandler extends \yii\base\ErrorHandler
{
    /**
     * Renders an exception using ansi format for console output.
     * @param \Throwable $exception the exception to be rendered.
     */
    protected function renderException($exception)
    {
        $previous = $exception->getPrevious();
        if ($exception instanceof UnknownCommandException) {
            // display message and suggest alternatives in case of unknown command
            $message = $this->formatMessage($exception->getName() . ': ') . $exception->command;
            $alternatives = $exception->getSuggestedAlternatives();
            if (count($alternatives) === 1) {
                $message .= "\n\nDid you mean \"" . reset($alternatives) . '"?';
            } elseif (count($alternatives) > 1) {
                $message .= "\n\nDid you mean one of these?\n    - " . implode("\n    - ", $alternatives);
            }
        } elseif ($exception instanceof UserException && ($exception instanceof Exception || !YII_DEBUG)) {
            $message = $this->formatMessage($exception->getName() . ': ') . $exception->getMessage();
        } elseif (YII_DEBUG) {
            if ($exception instanceof Exception) {
                $message = $this->formatMessage("Exception ({$exception->getName()})");
            } elseif ($exception instanceof ErrorException) {
                $message = $this->formatMessage($exception->getName());
            } else {
                $message = $this->formatMessage('Exception');
            }
            $message .= $this->formatMessage(" '" . get_class($exception) . "'", [Console::BOLD, Console::FG_BLUE])
                . ' with message ' . $this->formatMessage("'{$exception->getMessage()}'", [Console::BOLD]) //. "\n"
                . "\n\nin " . dirname($exception->getFile()) . DIRECTORY_SEPARATOR . $this->formatMessage(basename($exception->getFile()), [Console::BOLD])
                . ':' . $this->formatMessage($exception->getLine(), [Console::BOLD, Console::FG_YELLOW]) . "\n";
            if ($exception instanceof \yii\db\Exception && !empty($exception->errorInfo)) {
                $message .= "\n" . $this->formatMessage("Error Info:\n", [Console::BOLD]) . print_r($exception->errorInfo, true);
            }
            if ($previous === null) {
                $message .= "\n" . $this->formatMessage("Stack trace:\n", [Console::BOLD]) . $exception->getTraceAsString();
            }
        } else {
            $message = $this->formatMessage('Error: ') . $exception->getMessage();
        }

        if (PHP_SAPI === 'cli') {
            Console::stderr($message . "\n");
        } else {
            echo $message . "\n";
        }
        if (YII_DEBUG && $previous !== null) {
            $causedBy = $this->formatMessage('Caused by: ', [Console::BOLD]);
            if (PHP_SAPI === 'cli') {
                Console::stderr($causedBy);
            } else {
                echo $causedBy;
            }
            $this->renderException($previous);
        }
    }

    /**
     * Colorizes a message for console output.
     * @param string $message the message to colorize.
     * @param array $format the message format.
     * @return string the colorized message.
     * @see Console::ansiFormat() for details on how to specify the message format.
     */
    protected function formatMessage($message, $format = [Console::FG_RED, Console::BOLD])
    {
        $stream = (PHP_SAPI === 'cli') ? \STDERR : \STDOUT;
        // try controller first to allow check for --color switch
        if (
            Yii::$app->controller instanceof \yii\console\Controller && Yii::$app->controller->isColorEnabled($stream)
            || Yii::$app instanceof \yii\console\Application && Console::streamSupportsAnsiColors($stream)
        ) {
            $message = Console::ansiFormat($message, $format);
        }

        return $message;
    }
}
