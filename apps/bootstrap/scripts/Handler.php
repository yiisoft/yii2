<?php

namespace scripts;

use Composer\Script\Event;

define('BASE_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . "..");

class Handler
{
	public static function postInstall(Event $event)
	{
		chmod(BASE_PATH . DIRECTORY_SEPARATOR . "runtime", 0777);
		chmod(BASE_PATH . DIRECTORY_SEPARATOR . "www" . DIRECTORY_SEPARATOR . "assets", 0777);
		echo "\n\nInstallation completed.\n\n";
	}
}
