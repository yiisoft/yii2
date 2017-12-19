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
 * MimeTypeController generates a map of file extensions to MIME types.
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
     * @var array MIME type aliases
     */
    private $aliases = [
        'text/xml' => 'application/xml',
    ];

    /**
     * @param string $outFile the mime file to update. Defaults to @yii/helpers/mimeTypes.php
     * @param string $aliasesOutFile the aliases file to update. Defaults to @yii/helpers/mimeAliases.php
     */
    public function actionIndex($outFile = null, $aliasesOutFile = null)
    {
        if ($outFile === null) {
            $outFile = Yii::getAlias('@yii/helpers/mimeTypes.php');
        }

        if ($aliasesOutFile === null) {
            $aliasesOutFile = Yii::getAlias('@yii/helpers/mimeAliases.php');
        }

        $this->stdout('downloading mime-type file from apache httpd repository...');
        if ($apacheMimeTypesFileContent = file_get_contents('http://svn.apache.org/viewvc/httpd/httpd/trunk/docs/conf/mime.types?view=co')) {
            $this->stdout("done.\n", Console::FG_GREEN);
            $this->generateMimeTypesFile($outFile, $apacheMimeTypesFileContent);
            $this->generateMimeAliasesFile($aliasesOutFile);
        } else {
            $this->stderr("Failed to download mime.types file from apache SVN.\n");
        }
    }

    /**
     * @param string $outFile
     * @param string $content
     */
    private function generateMimeTypesFile($outFile, $content)
    {
        $this->stdout("generating file $outFile...");
        $mimeMap = [];
        foreach (explode("\n", $content) as $line) {
            $line = trim($line);
            if (empty($line) || $line[0] === '#') { // skip comments and empty lines
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
    }

    /**
     * @param string $outFile
     */
    private function generateMimeAliasesFile($outFile)
    {
        $this->stdout("generating file $outFile...");
        $array = VarDumper::export($this->aliases);
        $content = <<<EOD
<?php
/**
 * MIME aliases.
 *
 * This file contains aliases for MIME types.
 */
return $array;

EOD;
        file_put_contents($outFile, $content);
        $this->stdout("done.\n", Console::FG_GREEN);
    }
}
