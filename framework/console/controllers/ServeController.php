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
 * 运行 PHP 内置 web 服务器。
 *
 * 要从远程计算机访问服务器，请使用 0.0.0.0:8000。这在虚拟机中运行服务器
 * 时特别有用。
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
     * @var int 提供服务的端口。
     */
    public $port = 8080;
    /**
     * @var string 路径或 [path alias](guide:concept-aliases) 到服务的目录
     */
    public $docroot = '@app/web';
    /**
     * @var string 路由器脚本的路径。
     * See https://secure.php.net/manual/en/features.commandline.webserver.php
     */
    public $router;


    /**
     * 运行 PHP 内置 web 服务器。
     *
     * @param string $address 服务器地址。"host" 或 "host:port"。
     *
     * @return int
     */
    public function actionIndex($address = 'localhost')
    {
        $documentRoot = Yii::getAlias($this->docroot);

        if (strpos($address, ':') === false) {
            $address = $address . ':' . $this->port;
        }

        if (!is_dir($documentRoot)) {
            $this->stdout("Document root \"$documentRoot\" does not exist.\n", Console::FG_RED);
            return self::EXIT_CODE_NO_DOCUMENT_ROOT;
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

        passthru('"' . PHP_BINARY . '"' . " -S {$address} -t \"{$documentRoot}\" $this->router");
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     * @since 2.0.8
     */
    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            't' => 'docroot',
            'p' => 'port',
            'r' => 'router',
        ]);
    }

    /**
     * @param string $address 服务器地址
     * @return bool 地址是否已被使用
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
}
