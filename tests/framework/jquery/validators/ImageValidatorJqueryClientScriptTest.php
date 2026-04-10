<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\jquery\validators;

use PHPUnit\Framework\Attributes\Group;
use Yii;
use yii\validators\ImageValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * Unit tests for {@see ImageValidator} client validation script.
 */
#[Group('jquery')]
#[Group('validators')]
final class ImageValidatorJqueryClientScriptTest extends TestCase
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

        $modelValidator->attrA = 'test-image.jpg';

        $validator = new ImageValidator(
            [
                'maxHeight' => 600,
                'maxWidth' => 800,
                'minHeight' => 50,
                'minWidth' => 100,
            ],
        );

        self::assertSame(
            <<<JS
            yii.validation.image(attribute, messages, {"message":"File upload failed.","skipOnEmpty":true,"mimeTypes":[],"wrongMimeType":"Only files with these MIME types are allowed: .","extensions":[],"wrongExtension":"Only files with these extensions are allowed: .","maxFiles":1,"tooMany":"You can upload at most 1 file.","notImage":"The file \u0022{file}\u0022 is not an image.","minWidth":100,"underWidth":"The image \u0022{file}\u0022 is too small. The width cannot be smaller than 100 pixels.","maxWidth":800,"overWidth":"The image \u0022{file}\u0022 is too large. The width cannot be larger than 800 pixels.","minHeight":50,"underHeight":"The image \u0022{file}\u0022 is too small. The height cannot be smaller than 50 pixels.","maxHeight":600,"overHeight":"The image \u0022{file}\u0022 is too large. The height cannot be larger than 600 pixels."}, deferred);
            JS,
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->view),
            'Should return correct validation script.',
        );
        self::assertSame(
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
            'Should return correct options array.',
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        self::assertSame(
            'Please upload a file.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithUseJqueryFalse(): void
    {
        Yii::$app->useJquery = false;

        $modelValidator = new FakedValidationModel();

        $modelValidator->attrA = 'test-image.jpg';

        $validator = new ImageValidator(
            [
                'minWidth' => 100,
                'maxWidth' => 800,
                'minHeight' => 50,
                'maxHeight' => 600,
            ],
        );

        self::assertNull(
            $validator->clientScript,
            "Should be 'null' when 'useJquery' is 'false'.",
        );
        self::assertNull(
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->view),
            "Should return 'null' value.",
        );
        self::assertEmpty(
            $validator->getClientOptions($modelValidator, 'attrA'),
            'Should return an empty array.',
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        self::assertSame(
            'Please upload a file.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }
}
