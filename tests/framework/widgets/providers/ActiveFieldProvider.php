<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

/**
 * Data provider for {@see \yiiunit\framework\widgets\ActiveFieldTest} test cases.
 *
 * Provides representative input/output pairs for label, hint, radio, and checkbox attributes.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */

namespace yiiunit\framework\widgets\providers;

final class ActiveFieldProvider
{
    public static function hint(): array
    {
        return [
            'false' => [
                false,
                '',
            ],
            'null' => [
                null,
                <<<HTML
                <div class="hint-block">Hint for attributeName attribute</div>
                HTML,
            ],
            'string' => [
                'Hint Content',
                <<<HTML
                <div class="hint-block">Hint Content</div>
                HTML,
            ],
        ];
    }

    public static function labelWithTagCustom(): array
    {
        return [
            'h3' => [
                'h3',
                'Custom Label',
                <<<HTML
                <h3 class="control-label">Custom Label</h3>
                HTML,
            ],
            'span' => [
                'span',
                null,
                <<<HTML
                <span class="control-label">Attribute Name</span>
                HTML,
            ],
        ];
    }

    public static function labelWithTagFalse(): array
    {
        return [
            'null' => [
                null,
                'Attribute Name',
            ],
            'label' => [
                'Custom Label',
                'Custom Label',
            ],
        ];
    }

    public static function radioEnclosedByLabelFalse(): array
    {
        return [
            'label with false' => [
                [
                    'label' => false,
                    'labelOptions' => [
                        'class' => 'custom-label',
                    ],
                ],
                '',
            ],
            'labelOptions' => [
                [
                    'label' => 'Radio Label',
                    'labelOptions' => [
                        'class' => 'custom-label',
                        'data-type' => 'radio',
                    ],
                ],
                <<<HTML
                <label class="custom-label" data-type="radio" for="activefieldtestmodel-attributename">Radio Label</label>
                HTML,
            ],
            'labelOptions with custom tag' => [
                [
                    'label' => 'Radio Label',
                    'labelOptions' => [
                        'tag' => 'span',
                        'class' => 'custom-label',
                    ],
                ],
                <<<HTML
                <span class="custom-label">Radio Label</span>
                HTML,
            ],
            'labelOptions with tag false' => [
                [
                    'label' => 'Radio Label',
                    'labelOptions' => [
                        'tag' => false,
                    ],
                ],
                'Radio Label',
            ],
            'without labelOptions' => [
                [
                    'label' => 'Radio Label',
                ],
                'Radio Label',
            ],
        ];
    }

    public static function checkboxEnclosedByLabelFalse(): array
    {
        return [
            'label with false' => [
                [
                    'label' => false,
                    'labelOptions' => ['class' => 'custom-label'],
                ],
                '',
            ],
            'labelOptions' => [
                [
                    'label' => 'Checkbox Label',
                    'labelOptions' => [
                        'class' => 'custom-label',
                        'data-type' => 'checkbox',
                    ],
                ],
                <<<HTML
                <label class="custom-label" data-type="checkbox" for="activefieldtestmodel-attributename">Checkbox Label</label>
                HTML,
            ],
            'labelOptions with custom tag' => [
                [
                    'label' => 'Checkbox Label',
                    'labelOptions' => [
                        'tag' => 'span',
                        'class' => 'custom-label',
                    ],
                ],
                <<<HTML
                <span class="custom-label">Checkbox Label</span>
                HTML,
            ],
            'labelOptions with tag false' => [
                [
                    'label' => 'Checkbox Label',
                    'labelOptions' => ['tag' => false],
                ],
                'Checkbox Label',
            ],
            'without labelOptions' => [
                [
                    'label' => 'Checkbox Label',
                ],
                'Checkbox Label',
            ],
        ];
    }
}
