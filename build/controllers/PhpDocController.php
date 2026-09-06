<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\build\controllers;

use Yii;
use yii\base\Model;
use yii\console\Application;
use yii\console\Controller as ConsoleController;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\web\Controller as WebController;

/**
 * PhpDocController is there to help to maintain PHPDoc annotation in class files.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 *
 * @extends ConsoleController<Application>
 */
class PhpDocController extends ConsoleController
{
    /**
     * {@inheritdoc}
     */
    public $defaultAction = 'fix';
    /**
     * @var bool whether to add copyright header to php files. This should be skipped in application code.
     */
    public $skipFrameworkRequirements = false;


    /**
     * Fix some issues with PHPDoc in files.
     *
     * @param string $root the directory to parse files from. Defaults to YII2_PATH.
     */
    public function actionFix($root = null)
    {
        $files = $this->findFiles($root, false);

        $nFilesTotal = 0;
        $nFilesUpdated = 0;
        foreach ($files as $file) {
            $contents = file_get_contents($file);
            $hash = $this->hash($contents);

            // fix line endings
            $lines = preg_split('/(\r\n|\n|\r)/', $contents);

            if (!$this->skipFrameworkRequirements) {
                $this->fixFileDoc($lines);
            }
            $this->fixDocBlockIndentation($lines);
            $lines = array_values($this->fixLineSpacing($lines));

            $newContent = implode("\n", $lines);
            if ($hash !== $this->hash($newContent)) {
                file_put_contents($file, $newContent);
                $nFilesUpdated++;
            }
            $nFilesTotal++;
        }

        $this->stdout("\nParsed $nFilesTotal files.\n");
        $this->stdout("Updated $nFilesUpdated files.\n");
    }

    /**
     * {@inheritdoc}
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['skipFrameworkRequirements']);
    }

    /**
     * @param string $root
     * @param bool $needsInclude
     * @return array list of files.
     */
    protected function findFiles($root, $needsInclude = true)
    {
        $except = [];
        if ($needsInclude) {
            $extensionExcept = [
                'apidoc' => [
                    '/helpers/PrettyPrinter.php',
                    '/extensions/apidoc/helpers/ApiIndexer.php',
                    '/extensions/apidoc/helpers/ApiMarkdownLaTeX.php',
                ],
                'codeception' => [
                    '/TestCase.php',
                    '/DbTestCase.php',
                ],
                'gii' => [
                    '/components/DiffRendererHtmlInline.php',
                    '/generators/extension/default/AutoloadExample.php',
                ],
                'swiftmailer' => [
                    'src/Logger.php',
                ],
                'twig' => [
                    '/Extension.php',
                    '/Optimizer.php',
                    '/Template.php',
                    '/TwigSimpleFileLoader.php',
                    '/ViewRendererStaticClassProxy.php',
                ],
            ];
        } else {
            $extensionExcept = [];
        }

        if ($root === null) {
            $root = \dirname(YII2_PATH);
            $extensionPath = "$root/extensions";
            $this->setUpExtensionAliases($extensionPath);

            $except = [
                '/apps/',
                '/build/',
                '/docs/',
                '/extensions/composer/',
                '/framework/BaseYii.php',
                '/framework/Yii.php',
                'assets/',
                'tests/',
                'vendor/',
            ];
            foreach ($extensionExcept as $ext => $paths) {
                foreach ($paths as $path) {
                    $except[] = "/extensions/$ext$path";
                }
            }
        } elseif (preg_match('~extensions/([\w-]+)[\\\\/]?$~', $root, $matches)) {
            $extensionPath = \dirname(rtrim($root, '\\/'));
            $this->setUpExtensionAliases($extensionPath);

            list(, $extension) = $matches;
            Yii::setAlias("@yii/$extension", (string)$root);
            if (is_file($autoloadFile = Yii::getAlias("@yii/$extension/vendor/autoload.php"))) {
                include $autoloadFile;
            }

            if (isset($extensionExcept[$extension])) {
                foreach ($extensionExcept[$extension] as $path) {
                    $except[] = $path;
                }
            }
            $except[] = '/vendor/';
            $except[] = '/tests/';
            $except[] = '/docs/';

            //            // composer extension does not contain yii code
            //            if ($extension === 'composer') {
            //                return [];
            //            }
        } elseif (preg_match('~apps/([\w-]+)[\\\\/]?$~', $root, $matches)) {
            $extensionPath = \dirname(\dirname(rtrim($root, '\\/'))) . '/extensions';
            $this->setUpExtensionAliases($extensionPath);

            list(, $appName) = $matches;
            Yii::setAlias("@app-$appName", (string)$root);
            if (is_file($autoloadFile = Yii::getAlias("@app-$appName/vendor/autoload.php"))) {
                include $autoloadFile;
            }

            $except[] = '/runtime/';
            $except[] = '/vendor/';
            $except[] = '/tests/';
            $except[] = '/docs/';
        }
        $root = FileHelper::normalizePath($root);
        $options = [
            'filter' => function ($path) {
                if (is_file($path)) {
                    $file = basename($path);
                    if ($file[0] < 'A' || $file[0] > 'Z') {
                        return false;
                    }
                }

                return null;
            },
            'only' => ['*.php'],
            'except' => array_merge($except, [
                '.git/',
                'views/',
                'requirements/',
                'gii/generators/',
                'vendor/',
                '_support/',
            ]),
        ];

        return FileHelper::findFiles($root, $options);
    }

    /**
     * @param string $extensionPath root path containing extension repositories.
     */
    private function setUpExtensionAliases($extensionPath)
    {
        foreach (scandir($extensionPath) as $extension) {
            if (ctype_alpha($extension) && is_dir($extensionPath . '/' . $extension)) {
                Yii::setAlias("@yii/$extension", "$extensionPath/$extension");

                $composerConfigFile = $extensionPath . '/' . $extension . '/composer.json';
                if (file_exists($composerConfigFile)) {
                    $composerConfig = Json::decode(file_get_contents($composerConfigFile));
                    if (isset($composerConfig['autoload']['psr-4'])) {
                        foreach ($composerConfig['autoload']['psr-4'] as $namespace => $subPath) {
                            $alias = '@' . str_replace('\\', '/', $namespace);
                            $path = rtrim("$extensionPath/$extension/$subPath", '/');
                            Yii::setAlias($alias, $path);
                        }
                    }
                }
            }
        }
    }

    /**
     * Fix file PHPDoc.
     */
    protected function fixFileDoc(&$lines)
    {
        // find namespace
        $namespace = false;
        $namespaceLine = '';
        $contentAfterNamespace = false;
        foreach ($lines as $i => $line) {
            $line = trim($line);
            if (!empty($line)) {
                if (strncmp($line, 'namespace', 9) === 0) {
                    $namespace = $i;
                    $namespaceLine = $line;
                } elseif ($namespace !== false) {
                    $contentAfterNamespace = $i;
                    break;
                }
            }
        }

        if ($namespace !== false && $contentAfterNamespace !== false) {
            while ($contentAfterNamespace > 0) {
                array_shift($lines);
                $contentAfterNamespace--;
            }
            $lines = array_merge([
                '<?php',
                '',
                '/**',
                ' * @link https://www.yiiframework.com/',
                ' * @copyright Copyright (c) 2008 Yii Software LLC',
                ' * @license https://www.yiiframework.com/license/',
                ' */',
                '',
                $namespaceLine,
                '',
            ], $lines);
        }
    }

    /**
     * Markdown aware fix of whitespace issues in doc comments.
     * @param array $lines
     */
    protected function fixDocBlockIndentation(&$lines)
    {
        $docBlock = false;
        $codeBlock = false;
        $listIndent = '';
        $tag = false;
        $phpstanType = false;
        $indent = '';
        foreach ($lines as $i => $line) {
            if (preg_match('~^(\s*)/\*\*$~', $line, $matches)) {
                $docBlock = true;
                $indent = $matches[1];
            } elseif (preg_match('~^(\s*)\*+/~', $line)) {
                if ($docBlock) { // could be the end of normal comment
                    $lines[$i] = $indent . ' */';
                }
                $docBlock = false;
                $codeBlock = false;
                $listIndent = '';
                $tag = false;
                $phpstanType = false;
            } elseif ($docBlock) {
                $line = ltrim($line);
                if (strpos($line, '*') === 0) {
                    $line = substr($line, 1);
                }
                if (strpos($line, ' ') === 0) {
                    $line = substr($line, 1);
                }
                $docLine = str_replace("\t", '    ', rtrim($line));
                if ($phpstanType && $docLine !== '' && strpos($docLine, '@') !== 0) {
                    $lines[$i] = rtrim($indent . ' * ' . $docLine);
                    continue;
                }
                $phpstanType = false;
                if (empty($docLine)) {
                    $listIndent = '';
                } elseif (strpos($docLine, '@') === 0) {
                    $listIndent = '';
                    $codeBlock = false;
                    $tag = true;
                    if (preg_match('/^@phpstan-type(?:\s|$)/', $docLine)) {
                        $tag = false;
                        $phpstanType = true;
                        $lines[$i] = rtrim($indent . ' * ' . $docLine);
                        continue;
                    }
                    $docLine = preg_replace('/\s+/', ' ', $docLine);
                    $docLine = $this->fixParamTypes($docLine);
                } elseif (strpos($docLine, '```') !== false) {
                    $codeBlock = !$codeBlock;
                    $listIndent = '';
                } elseif (preg_match('/^(\s*)([0-9]+\.|-|\*|\+) /', $docLine, $matches)) {
                    $listIndent = str_repeat(' ', \strlen($matches[0]));
                    $tag = false;
                    $lines[$i] = $indent . ' * ' . $docLine;
                    continue;
                }
                if ($codeBlock) {
                    $lines[$i] = rtrim($indent . ' * ' . $docLine);
                } else {
                    $lines[$i] = rtrim($indent . ' * ' . (empty($listIndent) && !$tag ? $docLine : ($listIndent . ltrim($docLine))));
                }
            }
        }
    }

    /**
     * @param string $line
     * @return string
     */
    protected function fixParamTypes($line)
    {
        return preg_replace_callback('~@(param|return) ([\w\\|]+)~i', function ($matches) {
            $types = explode('|', $matches[2]);
            foreach ($types as $i => $type) {
                switch ($type) {
                    case 'integer':
                        $types[$i] = 'int';
                        break;
                    case 'boolean':
                        $types[$i] = 'bool';
                        break;
                }
            }

            return '@' . $matches[1] . ' ' . implode('|', $types);
        }, $line);
    }

    /**
     * Fixes line spacing code style for properties and constants.
     * @param string[] $lines
     * @return string[]
     */
    protected function fixLineSpacing($lines)
    {
        $propertiesOnly = false;
        // remove blank lines between properties
        $skip = true;
        $level = 0;
        foreach ($lines as $i => $line) {
            if (strpos($line, 'class ') !== false) {
                $skip = false;
            }
            if ($skip) {
                continue;
            }

            // keep spaces in multi line arrays
            if (strpos($line, '*') === false && strncmp(trim($line), "'SQLSTATE[", 10) !== 0) {
                $level += substr_count($line, '[') - substr_count($line, ']');
            }

            if (trim($line) === '') {
                if ($level == 0) {
                    unset($lines[$i]);
                }
            } elseif (ltrim($line)[0] !== '*' && strpos($line, 'function ') !== false) {
                break;
            } elseif (trim($line) === '}') {
                $propertiesOnly = true;
                break;
            }
        }
        $lines = array_values($lines);

        // add back some
        $endofUse = false;
        $endofConst = false;
        $endofPublic = false;
        $endofProtected = false;
        $endofPrivate = false;
        $skip = true;
        $level = 0; // track array properties
        $property = '';
        foreach ($lines as $i => $line) {
            if (strpos($line, 'class ') !== false) {
                $skip = false;
            }
            if ($skip) {
                continue;
            }

            // check for multi line array
            if ($level > 0) {
                ${'endof' . $property} = $i;
            }

            $line = trim($line);
            if (strncmp($line, 'public $', 8) === 0 || strncmp($line, 'public static $', 15) === 0) {
                $endofPublic = $i;
                $property = 'Public';
                $level = 0;
            } elseif (strncmp($line, 'protected $', 11) === 0 || strncmp($line, 'protected static $', 18) === 0) {
                $endofProtected = $i;
                $property = 'Protected';
                $level = 0;
            } elseif (strncmp($line, 'private $', 9) === 0 || strncmp($line, 'private static $', 16) === 0) {
                $endofPrivate = $i;
                $property = 'Private';
                $level = 0;
            } elseif (strpos($line, 'const ') === 0) {
                $endofConst = $i;
                $property = false;
            } elseif (strpos($line, 'use ') === 0) {
                $endofUse = $i;
                $property = false;
            } elseif (strpos($line, '*') === 0) {
                $property = false;
            } elseif (strpos($line, '*') !== 0 && strpos($line, 'function ') !== false || $line === '}') {
                break;
            }

            // check for multi line array
            if ($property !== false && strncmp($line, "'SQLSTATE[", 10) !== 0) {
                $level += substr_count($line, '[') - substr_count($line, ']');
            }
        }

        $endofAll = false;
        foreach (['Private', 'Protected', 'Public', 'Const', 'Use'] as $var) {
            if (${'endof' . $var} !== false) {
                $endofAll = ${'endof' . $var};
                break;
            }
        }

        //        $this->checkPropertyOrder($lineInfo);
        $result = [];
        foreach ($lines as $i => $line) {
            $result[] = $line;
            if (!($propertiesOnly && $i === $endofAll)) {
                if (
                    $i === $endofUse || $i === $endofConst || $i === $endofPublic ||
                    $i === $endofProtected || $i === $endofPrivate
                ) {
                    $result[] = '';
                }
                if ($i === $endofAll) {
                    $result[] = '';
                }
            }
        }

        return $result;
    }

    protected function checkPropertyOrder($lineInfo)
    {
        // TODO
    }

    protected function match($pattern, $subject, $split = false)
    {
        $sets = [];
        // split subject by double newlines because regex sometimes has problems with matching
        // in the complete set of methods
        // example: yii\di\ServiceLocator setComponents() is not recognized in the whole but in
        // a part of the class.
        $parts = $split ? explode("\n\n", $subject) : [$subject];
        foreach ($parts as $part) {
            preg_match_all($pattern . 'suU', $part, $matches, PREG_SET_ORDER);
            foreach ($matches as &$set) {
                foreach ($set as $i => $match) {
                    if (is_numeric($i) /*&& $i != 0*/) {
                        unset($set[$i]);
                    }
                }

                $sets[] = $set;
            }
        }

        return $sets;
    }

    /**
     * Generate a hash value (message digest)
     * @param string $string message to be hashed.
     * @return string calculated message digest.
     */
    private function hash($string)
    {
        if (!function_exists('hash')) {
            return sha1($string);
        }
        return hash('sha256', $string);
    }
}
