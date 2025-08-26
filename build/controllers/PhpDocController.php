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
use yii\helpers\FileHelper;
use yii\helpers\Json;

/**
 * PhpDocController is there to help to maintain PHPDoc annotation in class files.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class PhpDocController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public $defaultAction = 'property';
    /**
     * @var bool whether to update class docs directly. Setting this to false will just output docs
     * for copy and paste.
     */
    public $updateFiles = true;
    /**
     * @var bool whether to add copyright header to php files. This should be skipped in application code.
     */
    public $skipFrameworkRequirements = false;


    /**
     * Generates `@property` annotations in class files from getters and setters.
     *
     * Property description will be taken from getter or setter or from an `@property` annotation
     * in the getters docblock if there is one defined.
     *
     * See https://github.com/yiisoft/yii2/wiki/Core-framework-code-style#documentation for details.
     *
     * @param string $root the directory to parse files from. Defaults to YII2_PATH.
     */
    public function actionProperty($root = null)
    {
        $files = $this->findFiles($root);

        $nFilesTotal = 0;
        $nFilesUpdated = 0;
        foreach ($files as $file) {
            $result = $this->generateClassPropertyDocs($file);
            if ($result !== false) {
                list($className, $phpdoc) = $result;
                if ($this->updateFiles) {
                    if ($this->updateClassPropertyDocs($file, $className, $phpdoc)) {
                        $nFilesUpdated++;
                    }
                } elseif (!empty($phpdoc)) {
                    $this->stdout("\n[ " . $file . " ]\n\n", Console::BOLD);
                    $this->stdout($phpdoc);
                }
            }
            $nFilesTotal++;
        }

        $this->stdout("\nParsed $nFilesTotal files.\n");
        $this->stdout("Updated $nFilesUpdated files.\n");
    }

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
        return array_merge(parent::options($actionID), ['updateFiles', 'skipFrameworkRequirements']);
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
            } elseif ($docBlock) {
                $line = ltrim($line);
                if (strpos($line, '*') === 0) {
                    $line = substr($line, 1);
                }
                if (strpos($line, ' ') === 0) {
                    $line = substr($line, 1);
                }
                $docLine = str_replace("\t", '    ', rtrim($line));
                if (empty($docLine)) {
                    $listIndent = '';
                } elseif (strpos($docLine, '@') === 0) {
                    $listIndent = '';
                    $codeBlock = false;
                    $tag = true;
                    $docLine = preg_replace('/\s+/', ' ', $docLine);
                    $docLine = $this->fixParamTypes($docLine);
                } elseif (preg_match('/^(~~~|```)/', $docLine)) {
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
                    case 'integer': $types[$i] = 'int'; break;
                    case 'boolean': $types[$i] = 'bool'; break;
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
                if ($i === $endofUse || $i === $endofConst || $i === $endofPublic ||
                    $i === $endofProtected || $i === $endofPrivate) {
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

    protected function updateClassPropertyDocs($file, $className, $propertyDoc)
    {
        if ($this->shouldSkipClass($className)) {
            $this->stderr("[INFO] Skipping class $className.\n", Console::FG_BLUE, Console::BOLD);
            return false;
        }

        try {
            $ref = new \ReflectionClass($className);
        } catch (\Exception $e) {
            $this->stderr("[ERR] Unable to create ReflectionClass for class '$className': " . $e->getMessage() . "\n", Console::FG_RED);
            return false;
        } catch (\Error $e) {
            $this->stderr("[ERR] Unable to create ReflectionClass for class '$className': " . $e->getMessage() . "\n", Console::FG_RED);
            return false;
        }
        if ($ref->getFileName() != $file) {
            $this->stderr("[ERR] Unable to create ReflectionClass for class: $className loaded class is not from file: $file\n", Console::FG_RED);
            return false;
        }

        if ($this->isBaseObject($className, $ref)) {
            $this->stderr("[INFO] Skipping class $className as it is not a subclass of yii\\base\\BaseObject.\n", Console::FG_BLUE, Console::BOLD);
            return false;
        }

        if ($ref->isSubclassOf('yii\db\BaseActiveRecord')) {
            $this->stderr("[INFO] Skipping class $className as it is an ActiveRecord class, property handling is not supported yet.\n", Console::FG_BLUE, Console::BOLD);
            return false;
        }

        $oldDoc = $ref->getDocComment();
        $newDoc = $this->cleanDocComment($this->updateDocComment($oldDoc, $propertyDoc));

        $seenSince = false;
        $seenAuthor = false;

        // TODO move these checks to different action
        $lines = explode("\n", $newDoc);
        $firstLine = trim($lines[1]);
        if ($firstLine === '*' || strncmp($firstLine, '* @', 3) === 0) {
            $this->stderr("[WARN] Class $className has no short description.\n", Console::FG_YELLOW, Console::BOLD);
        }
        foreach ($lines as $line) {
            $line = trim($line);
            if (strncmp($line, '* @since ', 9) === 0) {
                $seenSince = true;
            } elseif (strncmp($line, '* @author ', 10) === 0) {
                $seenAuthor = true;
            }
        }

        if (!$this->skipFrameworkRequirements) {
            if (!$seenSince) {
                $this->stderr("[ERR] No @since found in class doc in file: $file\n", Console::FG_RED);
            }
            if (!$seenAuthor) {
                $this->stderr("[ERR] No @author found in class doc in file: $file\n", Console::FG_RED);
            }
        }

        if (trim($oldDoc) != trim($newDoc)) {
            $fileContent = explode("\n", file_get_contents($file));
            $start = $ref->getStartLine() - 2;
            $docStart = $start - \count(explode("\n", $oldDoc)) + 1;

            $newFileContent = [];
            $n = \count($fileContent);
            for ($i = 0; $i < $n; $i++) {
                if ($i > $start || $i < $docStart) {
                    $newFileContent[] = $fileContent[$i];
                } else {
                    $newFileContent[] = trim($newDoc);
                    $i = $start;
                }
            }

            file_put_contents($file, implode("\n", $newFileContent));

            return true;
        }

        return false;
    }

    /**
     * remove multi empty lines and trim trailing whitespace.
     *
     * @param $doc
     * @return string
     */
    protected function cleanDocComment($doc)
    {
        $lines = explode("\n", $doc);
        $n = \count($lines);
        for ($i = 0; $i < $n; $i++) {
            $lines[$i] = rtrim($lines[$i]);
            if (trim($lines[$i]) == '*' && trim($lines[$i + 1]) == '*') {
                unset($lines[$i]);
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Replace property annotations in doc comment.
     * @param $doc
     * @param $properties
     * @return string
     */
    protected function updateDocComment($doc, $properties)
    {
        $lines = explode("\n", $doc);
        $propertyPart = false;
        $propertyPosition = false;
        foreach ($lines as $i => $line) {
            $line = trim($line);
            if (strncmp($line, '* @property', 11) === 0) {
                $propertyPart = true;
            } elseif ($propertyPart && $line === '*') {
                $propertyPosition = $i;
                $propertyPart = false;
            }
            if (strncmp($line, '* @author ', 10) === 0 && $propertyPosition === false) {
                $propertyPosition = $i - 1;
                $propertyPart = false;
            }
            if ($propertyPart) {
                unset($lines[$i]);
            }
        }

        // if no properties or other tags were present add properties at the end
        if ($propertyPosition === false) {
            $propertyPosition = \count($lines) - 2;
        }

        $finalDoc = '';
        foreach ($lines as $i => $line) {
            $finalDoc .= $line . "\n";
            if ($i == $propertyPosition) {
                $finalDoc .= $properties;
            }
        }

        return $finalDoc;
    }

    protected function generateClassPropertyDocs($fileName)
    {
        $phpdoc = '';
        $file = str_replace("\r", '', str_replace("\t", ' ', file_get_contents($fileName, true)));
        $ns = $this->match('#\nnamespace (?<name>[\w\\\\]+);\n#', $file);
        $namespace = reset($ns);
        if ($namespace === false) {
            $namespace = '\\';
        } else {
            $namespace = $namespace['name'];
        }
        $classes = $this->match('#\n(?:abstract )?(?:final )?class (?<name>\w+)( extends .+)?( implements .+)?\n\{(?<content>.*)\n\}(\n|$)#', $file);

        if (\count($classes) > 1) {
            $this->stderr("[ERR] There should be only one class in a file: $fileName\n", Console::FG_RED);

            return false;
        }
        if (\count($classes) < 1) {
            $interfaces = $this->match('#\ninterface (?<name>\w+)( extends .+)?\n\{(?<content>.*)\n\}(\n|$)#', $file);
            if (\count($interfaces) == 1) {
                return false;
            }

            if (\count($interfaces) > 1) {
                $this->stderr("[ERR] There should be only one interface in a file: $fileName\n", Console::FG_RED);
            } else {
                $traits = $this->match('#\ntrait (?<name>\w+)\n\{(?<content>.*)\n\}(\n|$)#', $file);
                if (\count($traits) == 1) {
                    return false;
                }

                if (\count($traits) > 1) {
                    $this->stderr("[ERR] There should be only one class/trait/interface in a file: $fileName\n", Console::FG_RED);
                } else {
                    $this->stderr("[ERR] No class in file: $fileName\n", Console::FG_RED);
                }
            }

            return false;
        }

        $className = null;
        foreach ($classes as &$class) {
            $className = $namespace . '\\' . $class['name'];

            $gets = $this->match(
                '#\* @return (?<type>[\w\\|\\\\\\[\\]]+)(?: (?<comment>(?:(?!\*/|\* @).)+?)(?:(?!\*/).)+|[\s\n]*)\*/' .
                '[\s\n]{2,}public function (?<kind>get)(?<name>\w+)\((?:,? ?\$\w+ ?= ?[^,]+)*\)#',
                $class['content'], true);
            $sets = $this->match(
                '#\* @param (?<type>[\w\\|\\\\\\[\\]]+) \$\w+(?: (?<comment>(?:(?!\*/|\* @).)+?)(?:(?!\*/).)+|[\s\n]*)\*/' .
                '[\s\n]{2,}public function (?<kind>set)(?<name>\w+)\(\$\w+(?:, ?\$\w+ ?= ?[^,]+)*\)#',
                $class['content'], true);
            // check for @property annotations in getter and setter
            $properties = $this->match(
                '#\* @(?<kind>property) (?<type>[\w\\|\\\\\\[\\]]+)(?: (?<comment>(?:(?!\*/|\* @).)+?)(?:(?!\*/).)+|[\s\n]*)\*/' .
                '[\s\n]{2,}public function [g|s]et(?<name>\w+)\(((?:,? ?\$\w+ ?= ?[^,]+)*|\$\w+(?:, ?\$\w+ ?= ?[^,]+)*)\)#',
                $class['content']);
            $acrs = array_merge($properties, $gets, $sets);

            $props = [];
            foreach ($acrs as &$acr) {
                $acr['name'] = lcfirst($acr['name']);
                $acr['comment'] = trim(preg_replace('#(^|\n)\s+\*\s?#', '$1 * ', $acr['comment']));
                $props[$acr['name']][$acr['kind']] = [
                    'type' => $acr['type'],
                    'comment' => $this->fixSentence($acr['comment']),
                ];
            }

            if (\count($props) === 0) {
                continue;
            }

            ksort($props);

            $phpdoc .= " *\n";
            foreach ($props as $propName => &$prop) {
                $docLine = ' * @property';
                $note = '';
                if (isset($prop['get'], $prop['set'])) {
                    if ($prop['get']['type'] !== $prop['set']['type']) {
                        $note = ' Note that the type of this property differs in getter and setter.'
                                . ' See [[get' . ucfirst($propName) . '()]]'
                                . ' and [[set' . ucfirst($propName) . '()]] for details.';
                    }
                } elseif (isset($prop['get'])) {
                    if (!$this->hasSetterInParents($className, $propName)) {
                        $docLine .= '-read';
                    }
                } elseif (isset($prop['set'])) {
                    if (!$this->hasGetterInParents($className, $propName)) {
                        $docLine .= '-write';
                    }
                } else {
                    continue;
                }
                $docLine .= ' ' . $this->getPropParam($prop, 'type') . " $$propName ";
                $comment = explode("\n", $this->getPropParam($prop, 'comment') . $note);
                foreach ($comment as &$cline) {
                    $cline = ltrim(rtrim($cline), '* ');
                }
                $docLine = wordwrap($docLine . implode(' ', $comment), 110, "\n * ") . "\n";

                $phpdoc .= $docLine;
            }
            $phpdoc .= " *\n";
        }

        return [$className, $phpdoc];
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

    protected function fixSentence($str)
    {
        // TODO fix word wrap
        if ($str == '') {
            return '';
        }

        return strtoupper(substr($str, 0, 1)) . substr($str, 1) . ($str[\strlen($str) - 1] !== '.' ? '.' : '');
    }

    protected function getPropParam($prop, $param)
    {
        return isset($prop['property']) ? $prop['property'][$param] : (isset($prop['get']) ? $prop['get'][$param] : $prop['set'][$param]);
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

    /**
     * @param string $className
     * @param string $propName
     * @return bool
     */
    protected function hasGetterInParents($className, $propName)
    {
        $class = $className;

        try {
            while ($parent = get_parent_class($class)) {
                if (method_exists($parent, 'get' . ucfirst($propName))) {
                    return true;
                }
                $class = $parent;
            }
        } catch (\Throwable $t) {
            $this->stderr("[ERR] Error when getting parents for $className\n", Console::FG_RED);
            return false;
        }
        return false;
    }

    /**
     * @param string $className
     * @param string $propName
     * @return bool
     */
    protected function hasSetterInParents($className, $propName)
    {
        $class = $className;

        try {
            while ($parent = get_parent_class($class)) {
                if (method_exists($parent, 'set' . ucfirst($propName))) {
                    return true;
                }
                $class = $parent;
            }
        } catch (\Throwable $t) {
            $this->stderr("[ERR] Error when getting parents for $className\n", Console::FG_RED);
            return false;
        }
        return false;
    }

    /**
     * @param string $className
     * @param \ReflectionClass $ref
     * @return bool
     */
    protected function isBaseObject($className, \ReflectionClass $ref)
    {
        $isDeprecatedObject = false;
        if (PHP_VERSION_ID <= 70100) {
            $isDeprecatedObject = $ref->isSubclassOf('yii\base\Object') || $className === 'yii\base\Object';
        }
        return !$isDeprecatedObject && !$ref->isSubclassOf('yii\base\BaseObject') && $className !== 'yii\base\BaseObject';
    }

    private function shouldSkipClass($className)
    {
        if (PHP_VERSION_ID > 70100) {
            return $className === 'yii\base\Object';
        }
        return false;
    }
}
