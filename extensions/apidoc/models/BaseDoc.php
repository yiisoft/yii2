<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\DocBlock\Tag\DeprecatedTag;
use phpDocumentor\Reflection\DocBlock\Tag\SinceTag;
use yii\base\Object;
use yii\helpers\StringHelper;

/**
 * Base class for API documentation information.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class BaseDoc extends Object
{
    /**
     * @var \phpDocumentor\Reflection\DocBlock\Context
     */
    public $phpDocContext;
    public $name;
    public $sourceFile;
    public $startLine;
    public $endLine;
    public $shortDescription;
    public $description;
    public $since;
    public $deprecatedSince;
    public $deprecatedReason;
    /**
     * @var \phpDocumentor\Reflection\DocBlock\Tag[]
     */
    public $tags = [];


    /**
     * Checks if doc has tag of a given name
     * @param string $name tag name
     * @return boolean if doc has tag of a given name
     */
    public function hasTag($name)
    {
        foreach ($this->tags as $tag) {
            if (strtolower($tag->getName()) == $name) {
                return true;
            }
        }
        return false;
    }

    /**
     * Removes tag of a given name
     * @param string $name
     */
    public function removeTag($name)
    {
        foreach ($this->tags as $i => $tag) {
            if (strtolower($tag->getName()) == $name) {
                unset($this->tags[$i]);
            }
        }
    }


    /**
     * @param \phpDocumentor\Reflection\BaseReflector $reflector
     * @param Context $context
     * @param array $config
     */
    public function __construct($reflector = null, $context = null, $config = [])
    {
        parent::__construct($config);

        if ($reflector === null) {
            return;
        }

        // base properties
        $this->name = ltrim($reflector->getName(), '\\');
        $this->startLine = $reflector->getNode()->getAttribute('startLine');
        $this->endLine = $reflector->getNode()->getAttribute('endLine');

        $docblock = $reflector->getDocBlock();
        if ($docblock !== null) {
            $this->shortDescription = ucfirst($docblock->getShortDescription());
            if (empty($this->shortDescription) && !($this instanceof PropertyDoc) && $context !== null && $docblock->getTagsByName('inheritdoc') === null) {
                $context->errors[] = [
                    'line' => $this->startLine,
                    'file' => $this->sourceFile,
                    'message' => "No short description for " . substr(StringHelper::basename(get_class($this)), 0, -3) . " '{$this->name}'",
                ];
            }
            $this->description = $docblock->getLongDescription()->getContents();

            $this->phpDocContext = $docblock->getContext();

            $this->tags = $docblock->getTags();
            foreach ($this->tags as $i => $tag) {
                if ($tag instanceof SinceTag) {
                    $this->since = $tag->getVersion();
                    unset($this->tags[$i]);
                } elseif ($tag instanceof DeprecatedTag) {
                    $this->deprecatedSince = $tag->getVersion();
                    $this->deprecatedReason = $tag->getDescription();
                    unset($this->tags[$i]);
                }
            }
        } elseif ($context !== null) {
            $context->errors[] = [
                'line' => $this->startLine,
                'file' => $this->sourceFile,
                'message' => "No docblock for element '{$this->name}'",
            ];
        }
    }

    // TODO implement
//	public function loadSource($reflection)
//	{
//		$this->sourceFile;
//		$this->startLine;
//		$this->endLine;
//	}
//
//	public function getSourceCode()
//	{
//		$lines = file(YII2_PATH . $this->sourcePath);
//		return implode("", array_slice($lines, $this->startLine - 1, $this->endLine - $this->startLine + 1));
//	}

    /**
     * Extracts first sentence out of text
     * @param string $text
     * @return string
     */
    public static function extractFirstSentence($text)
    {
        if (mb_strlen($text) > 4 && ($pos = mb_strpos($text, '.', 4, 'utf-8')) !== false) {
            $sentence = mb_substr($text, 0, $pos + 1, 'utf-8');
            if (mb_strlen($text) >= $pos + 3) {
                $abbrev = mb_substr($text, $pos - 1, 4);
                if ($abbrev === 'e.g.' || $abbrev === 'i.e.') { // do not break sentence after abbreviation
                    $sentence .= static::extractFirstSentence(mb_substr($text, $pos + 1));
                }
            }
            return $sentence;
        } else {
            return $text;
        }
    }
}
