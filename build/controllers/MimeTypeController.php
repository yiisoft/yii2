<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\build\controllers;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\VarDumper;

/**
 * MimeTypeController generates a map of file extensions to MIME types
 *
 * It uses `mime.types` file from apache http located under
 * http://svn.apache.org/viewvc/httpd/httpd/trunk/docs/conf/mime.types?view=markup
 *
 * This file has been placed in the public domain for unlimited redistribution,
 * so we can use it and ship it with Yii.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class MimeTypeController extends Controller
{
    /**
     * @param string $outFile the file to update. Defaults to @yii/helpers/mimeTypes.php
     */
    public function actionIndex($outFile = null)
    {
        if ($outFile === null) {
            $outFile = Yii::getAlias('@yii/helpers/mimeTypes.php');
        }
        $this->stdout('downloading mime-type file from apache httpd repository...');
        if ($content = file_get_contents('http://svn.apache.org/viewvc/httpd/httpd/trunk/docs/conf/mime.types?view=co')) {
            $this->stdout("done.\n", Console::FG_GREEN);
            $this->stdout("generating file $outFile...");
            $mimeMap = [];
            foreach (explode("\n", $content) as $line) {
                $line = trim($line);
                if (empty($line) || $line[0] == '#') { // skip comments and empty lines
                    continue;
                }
                $parts = preg_split('/\s+/', $line);
                $mime = array_shift($parts);
                foreach ($parts as $ext) {
                    if (!empty($ext)) {
                        $mimeMap[$ext] = $mime;
                    }
                }
            }
            ksort($mimeMap);
            $array = VarDumper::export($mimeMap);
            $content = <<<EOD
<?php
/**
 * MIME types.
 *
 * This file contains most commonly used MIME types
 * according to file extension names.
 * Its content is generated from the apache http mime.types file.
 * http://svn.apache.org/viewvc/httpd/httpd/trunk/docs/conf/mime.types?view=markup
 * This file has been placed in the public domain for unlimited redistribution.
 */
return $array;

EOD;
            file_put_contents($outFile, $content);
            $this->stdout("done.\n", Console::FG_GREEN);
        } else {
            $this->stderr("Failed to download mime.types file from apache SVN.\n");
        }
    }
}
