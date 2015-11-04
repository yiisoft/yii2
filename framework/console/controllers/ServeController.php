<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Runs PHP built-in web server
 *
 * In order to access server from remote machines use 0.0.0.0:8000. That is especially useful when running server in
 * a virtual machine.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0.7
 */
class ServeController extends Controller
{
    const EXIT_CODE_NO_DOCUMENT_ROOT = 2;
    const EXIT_CODE_NO_ROUTING_FILE = 3;
    const EXIT_CODE_ADDRESS_TAKEN_BY_ANOTHER_SERVER = 4;
    const EXIT_CODE_ADDRESS_TAKEN_BY_ANOTHER_PROCESS = 5;

    /**
     * @var int port to serve on. Either "host" or "host:port".
     */
    public $port = 8080;

    /**
     * @var string path to directory to serve
     */
    public $docroot = 'web';

    /**
     * @var string path to router script.
     * See https://secure.php.net/manual/en/features.commandline.webserver.php
     */
    public $router;

    /**
     * Runs PHP built-in web server
     *
     * @param string $address address to serve on
     *
     * @return int
     */
    public function actionIndex($address = 'localhost')
    {
        $basePath = Yii::$app->basePath;
        $documentRoot = $basePath . '/' . $this->docroot;

        if (strpos($address, ':') === false) {
            $address = $address . ':' . $this->port;
        }

        if (!is_dir($documentRoot)) {
            $this->stdout("Document root \"$documentRoot\" does not exist.\n", Console::FG_RED);
            return self::EXIT_CODE_NO_DOCUMENT_ROOT;
        }

        if ($this->isOtherServerProcessRunning($address)) {
            $this->stdout("There's another server running on http://$address.\n", Console::FG_RED);
            return self::EXIT_CODE_ADDRESS_TAKEN_BY_ANOTHER_SERVER;
        }

        if ($this->isAddressTaken($address)) {
            $this->stdout("http://$address is taken by another process.\n", Console::FG_RED);
            return self::EXIT_CODE_ADDRESS_TAKEN_BY_ANOTHER_PROCESS;
        }

        if ($this->router !== null && !file_exists($this->router)) {
            $this->stdout("Routing file \"$this->router\" does not exist.\n", Console::FG_RED);
            return self::EXIT_CODE_NO_ROUTING_FILE;
        }

        $this->stdout("Server started on http://{$address}/\n");
        $this->stdout("Document root is \"{$documentRoot}\"\n");
        if ($this->router) {
            $this->stdout("Routing file is \"$this->router\"\n");
        }
        $this->stdout("Quit the server with CTRL-C or COMMAND-C.\n");

        $lock = $this->getLockFile($address);
        touch($lock);

        passthru('"' . PHP_BINARY . '"' . " -S {$address} -t \"{$documentRoot}\" $this->router");

        unlink($lock);
    }

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'docroot',
            'router',
            'port',
        ]);
    }

    /**
     * @param string $address server address
     * @return string path to pid file
     */
    protected function getLockFile($address)
    {
        return sys_get_temp_dir() . '/' . strtr($address, '.:', '--') . '.pid';
    }

    /**
     * @param string $address server address
     * @return boolean if address is already in use
     */
    protected function isAddressTaken($address)
    {
        list($hostname, $port) = explode(':', $address);
        $fp = @fsockopen($hostname, $port, $errno, $errstr, 3);
        if ($fp === false) {
            return false;
        }
        fclose($fp);
        return true;
    }

    /**
     * @param string $address server address
     * @return boolean if another server is running at address specified
     */
    protected function isOtherServerProcessRunning($address)
    {
        return file_exists($this->getLockFile($address));
    }
}
