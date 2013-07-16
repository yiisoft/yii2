<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\Exception;

/**
 * This command provider development web server
 *
 * @author Likai <youyuge@gmail.com>
 * @since 2.0
 */
class ServeController extends Controller
{
    /**
     * @var string host name
     */
    public $host = '127.0.0.1';

    /**
     * @var string host port
     */
    public $port = '8888';

    /**
     * @var string document root
     */
    public $root = 'www';

    /**
     * @var string router script file
     */
    public $router = 'router.php';

	/**
	 * Starting Yii Development Web Server
	 */
	public function actionIndex()
	{
		if (version_compare(PHP_VERSION, '5.4.0', '<')) {
			throw new Exception('Require PHP5.4 or later');
		}

        $root = realpath($this->root);

        if (!is_dir($root)) {
            throw new Exception('Document root does not exist.');
        }

        echo "Yii Development Server started on http://{$this->host}:{$this->port}\n";
        echo "Document root is  $root\n";
        echo "Press Ctrl-C to quit\n";

        passthru("php -S $this->host:$this->port -t $root $this->router");
	}

	/**
	 * Returns the names of the global options for this command.
	 * @return array the names of the global options for this command.
	 */
	public function globalOptions()
	{
		return array('host', 'port', 'root', 'router');
	}
}
