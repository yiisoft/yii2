<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
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
 * https://svn.apache.org/viewvc/httpd/httpd/trunk/docs/conf/mime.types?view=markup
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
        'text/rtf' => 'application/rtf',
        'text/xml' => 'application/xml',
        'image/svg' => 'image/svg+xml',
        'image/x-bmp' => 'image/bmp',
        'image/x-bitmap' => 'image/bmp',
        'image/x-xbitmap' => 'image/bmp',
        'image/x-win-bitmap' => 'image/bmp',
        'image/x-windows-bmp' => 'image/bmp',
        'image/ms-bmp' => 'image/bmp',
        'image/x-ms-bmp' => 'image/bmp',
        'application/bmp' => 'image/bmp',
        'application/x-bmp' => 'image/bmp',
        'application/x-win-bitmap' => 'image/bmp',
    ];

    /**
     * @var array MIME types to add to the ones parsed from Apache files
     */
    private $additionalMimeTypes = [
        'apng' => 'image/apng',
        'avif' => 'image/avif',
        'jfif' => 'image/jpeg',
        'mjs' => 'text/javascript',
        'pjp' => 'image/jpeg',
        'pjpeg' => 'image/jpeg',
    ];

    /**
     * @param string $outFile the mime file to update. Defaults to @yii/helpers/mimeTypes.php
     * @param string $aliasesOutFile the aliases file to update. Defaults to @yii/helpers/mimeAliases.php
     */
    public function actionIndex($outFile = null, $aliasesOutFile = null, $extensionsOutFile = null)
    {
        if ($outFile === null) {
            $outFile = Yii::getAlias('@yii/helpers/mimeTypes.php');
        }

        if ($aliasesOutFile === null) {
            $aliasesOutFile = Yii::getAlias('@yii/helpers/mimeAliases.php');
        }

        if ($extensionsOutFile === null) {
            $extensionsOutFile = Yii::getAlias('@yii/helpers/mimeExtensions.php');
        }

        $this->stdout('Downloading mime-type file from apache httpd repository...');
        if ($apacheMimeTypesFileContent = file_get_contents('https://svn.apache.org/viewvc/httpd/httpd/trunk/docs/conf/mime.types?view=co')) {
            $this->stdout("Done.\n", Console::FG_GREEN);
            $this->generateMimeTypesFile($outFile, $apacheMimeTypesFileContent);
            $this->generateMimeAliasesFile($aliasesOutFile);
            $this->generateMimeExtensionsFile($extensionsOutFile, $apacheMimeTypesFileContent);
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
        $this->stdout("Generating file $outFile...");
        $mimeMap = [];
        foreach (explode("\n", $content) as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) { // skip comments and empty lines
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
        $mimeMap = array_replace($mimeMap, $this->additionalMimeTypes);
        ksort($mimeMap, SORT_STRING);
        $array = VarDumper::export($mimeMap);

        $content = <<<EOD
<?php
/**
 * MIME types.
 *
 * This file contains most commonly used MIME types
 * according to file extension names.
 * Its content is generated from the apache http mime.types file.
 * https://svn.apache.org/viewvc/httpd/httpd/trunk/docs/conf/mime.types?view=markup
 * This file has been placed in the public domain for unlimited redistribution.
 *
 * All extra changes made to this file must be comitted to /build/controllers/MimeTypeController.php
 * otherwise they will be lost on next build.
 */
\$mimeTypes = $array;

# fix for bundled libmagic bug, see also https://github.com/yiisoft/yii2/issues/19925
if ((PHP_VERSION_ID >= 80100 && PHP_VERSION_ID < 80122) || (PHP_VERSION_ID >= 80200 && PHP_VERSION_ID < 80209)) {
    \$mimeTypes = array_replace(\$mimeTypes, array('xz' => 'application/octet-stream'));
}

return \$mimeTypes;

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
 *
 * All extra changes made to this file must be comitted to /build/controllers/MimeTypeController.php
 * otherwise they will be lost on next build.
 */
return $array;

EOD;
        file_put_contents($outFile, $content);
        $this->stdout("done.\n", Console::FG_GREEN);
    }

    /**
     * @param string $outFile
     * @param string $content
     */
    private function generateMimeExtensionsFile($outFile, $content)
    {
        $this->stdout("Generating file $outFile...");

        $extensionMap = [];
        foreach (explode("\n", $content) as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) { // skip comments and empty lines
                continue;
            }
            $parts = preg_split('/\s+/', $line);
            $mime = array_shift($parts);
            if (!empty($parts)) {
                $extensionMap[$mime] = [];
                foreach ($parts as $ext) {
                    if (!empty($ext)) {
                        $extensionMap[$mime][] = $ext;
                    }
                }
            }
        }

        foreach ($this->additionalMimeTypes as $ext => $mime) {
            if (!array_key_exists($mime, $extensionMap)) {
                $extensionMap[$mime] = [];
            }
            $extensionMap[$mime][] = $ext;
        }

        foreach ($extensionMap as $mime => $extensions) {
            if (count($extensions) === 1) {
                $extensionMap[$mime] = $extensions[0];
            }
        }

        ksort($extensionMap, SORT_STRING);
        $array = VarDumper::export($extensionMap);

        $content = <<<EOD
<?php
/**
 * MIME type extensions.
 *
 * This file contains most commonly used extensions for MIME types.
 * If there are multiple extensions for a singe MIME type
 * they are ordered from most to least common.
 * Its content is generated from the apache http mime.types file.
 * https://svn.apache.org/viewvc/httpd/httpd/trunk/docs/conf/mime.types?view=markup
 * This file has been placed in the public domain for unlimited redistribution.
 *
 * All extra changes made to this file must be comitted to /build/controllers/MimeTypeController.php
 * otherwise they will be lost on next build.
 */
return $array;

EOD;
        file_put_contents($outFile, $content);
        $this->stdout("done.\n", Console::FG_GREEN);
    }
}
