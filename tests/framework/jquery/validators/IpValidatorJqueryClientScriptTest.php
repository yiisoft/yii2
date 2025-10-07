<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\jquery\validators;

use Yii;
use yii\validators\IpValidator;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;

/**
 * @group jquery
 * @group validators
 */
final class IpValidatorJqueryClientScriptTest extends \yiiunit\TestCase
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
        $validator = new IpValidator();

        $modelValidator->attrA = '192.168.1.1';

        $ipParsePattern = $validator->getIpParsePattern();

        $this->assertSame(
            'yii.validation.ip(value, messages, {"ipv4Pattern":' . $validator->ipv4Pattern . ',"ipv6Pattern":' .
            $validator->ipv6Pattern . ',"messages":{"ipv6NotAllowed":"attrA must not be an IPv6 address.",' .
            '"ipv4NotAllowed":"attrA must not be an IPv4 address.","message":"attrA must be a valid IP address.",' .
            '"noSubnet":"attrA must be an IP address with specified subnet.",' .
            '"hasSubnet":"attrA must not be a subnet."},"ipv4":true,"ipv6":true,"ipParsePattern":' . $ipParsePattern .
            ',"negation":false,"subnet":false,"skipOnEmpty":1});',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );

        $clientOptions = $validator->getClientOptions($modelValidator, 'attrA');

        $clientOptions['ipv4Pattern'] = (string) ($clientOptions['ipv4Pattern'] ?? '');
        $clientOptions['ipv6Pattern'] = (string) ($clientOptions['ipv6Pattern'] ?? '');
        $clientOptions['ipParsePattern'] = (string) ($clientOptions['ipParsePattern'] ?? '');

        $this->assertSame(
            [
                'ipv4Pattern' => $validator->ipv4Pattern,
                'ipv6Pattern' => $validator->ipv6Pattern,
                'messages' => [
                    'ipv6NotAllowed' => 'attrA must not be an IPv6 address.',
                    'ipv4NotAllowed' => 'attrA must not be an IPv4 address.',
                    'message' => 'attrA must be a valid IP address.',
                    'noSubnet' => 'attrA must be an IP address with specified subnet.',
                    'hasSubnet' => 'attrA must not be a subnet.',
                ],
                'ipv4' => true,
                'ipv6' => true,
                'ipParsePattern' => $ipParsePattern,
                'negation' => false,
                'subnet' => false,
                'skipOnEmpty' => 1,
            ],
            $clientOptions,
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate('invalid-ip', $errorMessage);

        $this->assertSame(
            'the input value must be a valid IP address.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithIpv4Only(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new IpValidator(['ipv6' => false]);

        $ipParsePattern = $validator->getIpParsePattern();

        $this->assertSame(
            'yii.validation.ip(value, messages, {"ipv4Pattern":' . $validator->ipv4Pattern . ',"ipv6Pattern":' .
            $validator->ipv6Pattern . ',"messages":{"ipv6NotAllowed":"attrA must not be an IPv6 address.",' .
            '"ipv4NotAllowed":"attrA must not be an IPv4 address.","message":"attrA must be a valid IP address.",' .
            '"noSubnet":"attrA must be an IP address with specified subnet.",' .
            '"hasSubnet":"attrA must not be a subnet."},"ipv4":true,"ipv6":false,"ipParsePattern":' . $ipParsePattern .
            ',"negation":false,"subnet":false,"skipOnEmpty":1});',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );

        $clientOptions = $validator->getClientOptions($modelValidator, 'attrA');

        $clientOptions['ipv4Pattern'] = (string) ($clientOptions['ipv4Pattern'] ?? '');
        $clientOptions['ipv6Pattern'] = (string) ($clientOptions['ipv6Pattern'] ?? '');
        $clientOptions['ipParsePattern'] = (string) ($clientOptions['ipParsePattern'] ?? '');

        $this->assertSame(
            [
                'ipv4Pattern' => $validator->ipv4Pattern,
                'ipv6Pattern' => $validator->ipv6Pattern,
                'messages' => [
                    'ipv6NotAllowed' => 'attrA must not be an IPv6 address.',
                    'ipv4NotAllowed' => 'attrA must not be an IPv4 address.',
                    'message' => 'attrA must be a valid IP address.',
                    'noSubnet' => 'attrA must be an IP address with specified subnet.',
                    'hasSubnet' => 'attrA must not be a subnet.',
                ],
                'ipv4' => true,
                'ipv6' => false,
                'ipParsePattern' => $ipParsePattern,
                'negation' => false,
                'subnet' => false,
                'skipOnEmpty' => 1,
            ],
            $clientOptions,
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate('invalid-ip', $errorMessage);

        $this->assertSame(
            'the input value must be a valid IP address.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithIpv6Only(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new IpValidator(['ipv4' => false]);

        $ipParsePattern = $validator->getIpParsePattern();

        $this->assertSame(
            'yii.validation.ip(value, messages, {"ipv4Pattern":' . $validator->ipv4Pattern . ',"ipv6Pattern":' .
            $validator->ipv6Pattern . ',"messages":{"ipv6NotAllowed":"attrA must not be an IPv6 address.",' .
            '"ipv4NotAllowed":"attrA must not be an IPv4 address.","message":"attrA must be a valid IP address.",' .
            '"noSubnet":"attrA must be an IP address with specified subnet.",' .
            '"hasSubnet":"attrA must not be a subnet."},"ipv4":false,"ipv6":true,"ipParsePattern":' . $ipParsePattern .
            ',"negation":false,"subnet":false,"skipOnEmpty":1});',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );

        $clientOptions = $validator->getClientOptions($modelValidator, 'attrA');

        $clientOptions['ipv4Pattern'] = (string) ($clientOptions['ipv4Pattern'] ?? '');
        $clientOptions['ipv6Pattern'] = (string) ($clientOptions['ipv6Pattern'] ?? '');
        $clientOptions['ipParsePattern'] = (string) ($clientOptions['ipParsePattern'] ?? '');

        $this->assertSame(
            [
                'ipv4Pattern' => $validator->ipv4Pattern,
                'ipv6Pattern' => $validator->ipv6Pattern,
                'messages' => [
                    'ipv6NotAllowed' => 'attrA must not be an IPv6 address.',
                    'ipv4NotAllowed' => 'attrA must not be an IPv4 address.',
                    'message' => 'attrA must be a valid IP address.',
                    'noSubnet' => 'attrA must be an IP address with specified subnet.',
                    'hasSubnet' => 'attrA must not be a subnet.',
                ],
                'ipv4' => false,
                'ipv6' => true,
                'ipParsePattern' => $ipParsePattern,
                'negation' => false,
                'subnet' => false,
                'skipOnEmpty' => 1,
            ],
            $clientOptions,
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate('invalid-ip', $errorMessage);

        $this->assertSame(
            'the input value must be a valid IP address.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithSubnetRequired(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new IpValidator(['subnet' => true]);

        $ipParsePattern = $validator->getIpParsePattern();

        $this->assertSame(
            'yii.validation.ip(value, messages, {"ipv4Pattern":' . $validator->ipv4Pattern . ',"ipv6Pattern":' .
            $validator->ipv6Pattern . ',"messages":{"ipv6NotAllowed":"attrA must not be an IPv6 address.",' .
            '"ipv4NotAllowed":"attrA must not be an IPv4 address.","message":"attrA must be a valid IP address.",' .
            '"noSubnet":"attrA must be an IP address with specified subnet.",' .
            '"hasSubnet":"attrA must not be a subnet."},"ipv4":true,"ipv6":true,"ipParsePattern":' . $ipParsePattern .
            ',"negation":false,"subnet":true,"skipOnEmpty":1});',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );

        $clientOptions = $validator->getClientOptions($modelValidator, 'attrA');

        $clientOptions['ipv4Pattern'] = (string) ($clientOptions['ipv4Pattern'] ?? '');
        $clientOptions['ipv6Pattern'] = (string) ($clientOptions['ipv6Pattern'] ?? '');
        $clientOptions['ipParsePattern'] = (string) ($clientOptions['ipParsePattern'] ?? '');

        $this->assertSame(
            [
                'ipv4Pattern' => $validator->ipv4Pattern,
                'ipv6Pattern' => $validator->ipv6Pattern,
                'messages' => [
                    'ipv6NotAllowed' => 'attrA must not be an IPv6 address.',
                    'ipv4NotAllowed' => 'attrA must not be an IPv4 address.',
                    'message' => 'attrA must be a valid IP address.',
                    'noSubnet' => 'attrA must be an IP address with specified subnet.',
                    'hasSubnet' => 'attrA must not be a subnet.',
                ],
                'ipv4' => true,
                'ipv6' => true,
                'ipParsePattern' => $ipParsePattern,
                'negation' => false,
                'subnet' => true,
                'skipOnEmpty' => 1,
            ],
            $clientOptions,
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate('invalid-ip', $errorMessage);

        $this->assertSame(
            'the input value must be an IP address with specified subnet.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithUseJqueryFalse(): void
    {
        Yii::$app->useJquery = false;

        $modelValidator = new FakedValidationModel();
        $validator = new IpValidator();

        $modelValidator->attrA = '192.168.1.1';

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

        $validator->validate('invalid-ip', $errorMessage);

        $this->assertSame(
            'the input value must be a valid IP address.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }
}
