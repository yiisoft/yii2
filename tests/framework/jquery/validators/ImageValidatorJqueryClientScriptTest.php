<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\jquery\validators;

use Yii;
use yii\validators\ImageValidator;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;

/**
 * @group jquery
 * @group validators
 */
final class ImageValidatorJqueryClientScriptTest extends \yiiunit\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockWebApplication();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->destroyApplication();
    }

    public function testClientValidateAttribute(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new ImageValidator(
            [
                'maxHeight' => 600,
                'maxWidth' => 800,
                'minHeight' => 50,
                'minWidth' => 100,
            ],
        );

        $modelValidator->attrA = 'test-image.jpg';

        $this->assertSame(
            'yii.validation.image(attribute, messages, {"message":"File upload failed.","skipOnEmpty":true,' .
            '"mimeTypes":[],"wrongMimeType":"Only files with these MIME types are allowed: .","extensions":[],' .
            '"wrongExtension":"Only files with these extensions are allowed: .","maxFiles":1,' .
            '"tooMany":"You can upload at most 1 file.","notImage":"The file \u0022{file}\u0022 is not an image.",' .
            '"minWidth":100,"underWidth":"The image \u0022{file}\u0022 is too small. The width cannot be smaller than 100 pixels.",' .
            '"maxWidth":800,"overWidth":"The image \u0022{file}\u0022 is too large. The width cannot be larger than 800 pixels.",' .
            '"minHeight":50,"underHeight":"The image \u0022{file}\u0022 is too small. The height cannot be smaller than 50 pixels.",' .
            '"maxHeight":600,"overHeight":"The image \u0022{file}\u0022 is too large. The height cannot be larger than 600 pixels."}, ' .
            'deferred);',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );
        $this->assertSame(
            [
                'message' => 'File upload failed.',
                'skipOnEmpty' => true,
                'mimeTypes' => [],
                'wrongMimeType' => 'Only files with these MIME types are allowed: .',
                'extensions' => [],
                'wrongExtension' => 'Only files with these extensions are allowed: .',
                'maxFiles' => 1,
                'tooMany' => 'You can upload at most 1 file.',
                'notImage' => 'The file "{file}" is not an image.',
                'minWidth' => 100,
                'underWidth' => 'The image "{file}" is too small. The width cannot be smaller than 100 pixels.',
                'maxWidth' => 800,
                'overWidth' => 'The image "{file}" is too large. The width cannot be larger than 800 pixels.',
                'minHeight' => 50,
                'underHeight' => 'The image "{file}" is too small. The height cannot be smaller than 50 pixels.',
                'maxHeight' => 600,
                'overHeight' => 'The image "{file}" is too large. The height cannot be larger than 600 pixels.',
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertSame(
            'Please upload a file.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithUseJqueryFalse(): void
    {
        Yii::$app->useJquery = false;

        $modelValidator = new FakedValidationModel();
        $validator = new ImageValidator(
            [
                'minWidth' => 100,
                'maxWidth' => 800,
                'minHeight' => 50,
                'maxHeight' => 600,
            ],
        );

        $modelValidator->attrA = 'test-image.jpg';

        $this->assertNull(
            $validator->clientScript,
            "'ClientScript' property should be 'null' when 'useJquery' is 'false'.",
        );
        $this->assertNull(
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return 'null' value.",
        );
        $this->assertEmpty(
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return an empty array.",
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertSame(
            'Please upload a file.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }
}
