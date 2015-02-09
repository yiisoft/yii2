<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use phpDocumentor\Reflection\DocBlock\Type\Collection;
use yii\apidoc\models\MethodDoc;
use yii\apidoc\models\TypeDoc;

/**
 * Class ApiMarkdownTrait
 *
 * @property TypeDoc $renderingContext
 */
trait ApiMarkdownTrait
{
    /**
     * @marker [[
     */
    protected function parseApiLinks($text)
    {
        $context = $this->renderingContext;

        if (preg_match('/^\[\[([\w\d\\\\\(\):$]+)(\|[^\]]*)?\]\]/', $text, $matches)) {

            $offset = strlen($matches[0]);

            $object = $matches[1];
            $title = (empty($matches[2]) || $matches[2] == '|') ? null : substr($matches[2], 1);

            if (($pos = strpos($object, '::')) !== false) {
                $typeName = substr($object, 0, $pos);
                $subjectName = substr($object, $pos + 2);
                if ($context !== null) {
                    // Collection resolves relative types
                    $typeName = (new Collection([$typeName], $context->phpDocContext))->__toString();
                }
                /** @var $type TypeDoc */
                $type = static::$renderer->apiContext->getType($typeName);
                if ($type === null) {
                    static::$renderer->apiContext->errors[] = [
                        'file' => ($context !== null) ? $context->sourceFile : null,
                        'message' => 'broken link to ' . $typeName . '::' . $subjectName . (($context !== null) ? ' in ' . $context->name : ''),
                    ];

                    return [
                        ['brokenApiLink', '<span class="broken-link">' . $typeName . '::' . $subjectName . '</span>'],
                        $offset
                    ];
                } else {
                    if (($subject = $type->findSubject($subjectName)) !== null) {
                        if ($title === null) {
                            $title = $type->name . '::' . $subject->name;
                            if ($subject instanceof MethodDoc) {
                                $title .= '()';
                            }
                        }

                        return [
                            ['apiLink', static::$renderer->createSubjectLink($subject, $title)],
                            $offset
                        ];
                    } else {
                        static::$renderer->apiContext->errors[] = [
                            'file' => ($context !== null) ? $context->sourceFile : null,
                            'message' => 'broken link to ' . $type->name . '::' . $subjectName . (($context !== null) ? ' in ' . $context->name : ''),
                        ];

                        return [
                            ['brokenApiLink', '<span class="broken-link">' . $type->name . '::' . $subjectName . '</span>'],
                            $offset
                        ];
                    }
                }
            } elseif ($context !== null && ($subject = $context->findSubject($object)) !== null) {
                return [
                    ['apiLink', static::$renderer->createSubjectLink($subject, $title)],
                    $offset
                ];
            }

            if ($context !== null) {
                // Collection resolves relative types
                $object = (new Collection([$object], $context->phpDocContext))->__toString();
            }
            if (($type = static::$renderer->apiContext->getType($object)) !== null) {
                return [
                    ['apiLink', static::$renderer->createTypeLink($type, null, $title)],
                    $offset
                ];
            } elseif (strpos($typeLink = static::$renderer->createTypeLink($object, null, $title), '<a href') !== false) {
                return [
                    ['apiLink', $typeLink],
                    $offset
                ];
            }
            static::$renderer->apiContext->errors[] = [
                'file' => ($context !== null) ? $context->sourceFile : null,
                'message' => 'broken link to ' . $object . (($context !== null) ? ' in ' . $context->name : ''),
            ];

            return [
                ['brokenApiLink', '<span class="broken-link">' . $object . '</span>'],
                $offset
            ];
        }

        return [['text', '[['], 2];
    }

    /**
     * Renders API link
     * @param array $block
     * @return string
     */
    protected function renderApiLink($block)
    {
        return $block[1];
    }

    /**
     * Renders API link that is broken i.e. points nowhere
     * @param array $block
     * @return string
     */
    protected function renderBrokenApiLink($block)
    {
        return $block[1];
    }
}
