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
     * TODO adjust implementation
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
                        '<span style="background: #f00;">' . $typeName . '::' . $subjectName . '</span>',
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
                            static::$renderer->createSubjectLink($subject, $title),
                            $offset
                        ];
                    } else {
                        static::$renderer->apiContext->errors[] = [
                            'file' => ($context !== null) ? $context->sourceFile : null,
                            'message' => 'broken link to ' . $type->name . '::' . $subjectName . (($context !== null) ? ' in ' . $context->name : ''),
                        ];

                        return [
                            '<span style="background: #ff0;">' . $type->name . '</span><span style="background: #f00;">::' . $subjectName . '</span>',
                            $offset
                        ];
                    }
                }
            } elseif ($context !== null && ($subject = $context->findSubject($object)) !== null) {
                return [
                    static::$renderer->createSubjectLink($subject, $title),
                    $offset
                ];
            }

            if ($context !== null) {
                // Collection resolves relative types
                $object = (new Collection([$object], $context->phpDocContext))->__toString();
            }
            if (($type = static::$renderer->apiContext->getType($object)) !== null) {
                return [
                    static::$renderer->createTypeLink($type, null, $title),
                    $offset
                ];
            } elseif (strpos($typeLink = static::$renderer->createTypeLink($object, null, $title), '<a href') !== false) {
                return [
                    $typeLink,
                    $offset
                ];
            }
            static::$renderer->apiContext->errors[] = [
                'file' => ($context !== null) ? $context->sourceFile : null,
                'message' => 'broken link to ' . $object . (($context !== null) ? ' in ' . $context->name : ''),
            ];

            return [
                '<span style="background: #f00;">' . $object . '</span>',
                $offset
            ];
        }

        return ['[[', 2];
    }
}
