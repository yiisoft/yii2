<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\bootstrap;

use yii\apidoc\models\TypeDoc;

/**
 * Common methods for renderers
 */
trait RendererTrait
{
    /**
     * @var array official Yii extensions
     */
    public $extensions = [
        'apidoc',
        'authclient',
        'bootstrap',
        'codeception',
        'composer',
        'debug',
        'elasticsearch',
        'faker',
        'gii',
        'imagine',
        'jui',
        'mongodb',
        'redis',
        'smarty',
        'sphinx',
        'swiftmailer',
        'twig',
    ];

    /**
     * Returns nav TypeDocs
     * @param TypeDoc $type typedoc to take category from
     * @param TypeDoc[] $types TypeDocs to filter
     * @return array
     */
    public function getNavTypes($type, $types)
    {
        if ($type === null) {
            return $types;
        }

        return $this->filterTypes($types, $this->getTypeCategory($type));
    }

    /**
     * Returns category of TypeDoc
     * @param TypeDoc $type
     * @return string
     */
    protected function getTypeCategory($type)
    {
        $extensions = $this->extensions;
        $navClasses = 'app';
        if (isset($type)) {
            if ($type->name == 'Yii') {
                $navClasses = 'yii';
            } elseif (strncmp($type->name, 'yii\\', 4) == 0) {
                $navClasses = 'yii';
                $subName = substr($type->name, 4);
                if (($pos = strpos($subName, '\\')) !== false) {
                    $subNamespace = substr($subName, 0, $pos);
                    if (in_array($subNamespace, $extensions)) {
                        $navClasses = $subNamespace;
                    }
                }
            }
        }

        return $navClasses;
    }

    /**
     * Returns types of a given class
     *
     * @param TypeDoc[] $types
     * @param string $navClasses
     * @return array
     */
    protected function filterTypes($types, $navClasses)
    {
        switch ($navClasses) {
            case 'app':
                $types = array_filter($types, function ($val) {
                    return strncmp($val->name, 'yii\\', 4) !== 0;
                });
                break;
            case 'yii':
                $self = $this;
                $types = array_filter($types, function ($val) use ($self) {
                    if ($val->name == 'Yii') {
                        return true;
                    }
                    if (strlen($val->name) < 5) {
                        return false;
                    }
                    $subName = substr($val->name, 4, strpos($val->name, '\\', 5) - 4);

                    return strncmp($val->name, 'yii\\', 4) === 0 && !in_array($subName, $self->extensions);
                });
                break;
            default:
                $types = array_filter($types, function ($val) use ($navClasses) {
                    return strncmp($val->name, "yii\\$navClasses\\", strlen("yii\\$navClasses\\")) === 0;
                });
        }

        return $types;
    }
}
