<?php

namespace yiiunit\extensions\authclient\oauth\signature;

use yii\authclient\signature\RsaSha1;
use yiiunit\extensions\authclient\TestCase;

class RsaSha1Test extends TestCase
{
	/**
	 * Returns test public certificate string.
	 * @return string public certificate string.
	 */
	protected function getTestPublicCertificate()
	{
		return '-----BEGIN CERTIFICATE-----
MIIDJDCCAo2gAwIBAgIJALCFAl3nj1ibMA0GCSqGSIb3DQEBBQUAMIGqMQswCQYD
VQQGEwJOTDESMBAGA1UECAwJQW1zdGVyZGFtMRIwEAYDVQQHDAlBbXN0ZXJkYW0x
DzANBgNVBAoMBlBpbVRpbTEPMA0GA1UECwwGUGltVGltMSswKQYDVQQDDCJkZXY1
My5xdWFydHNvZnQuY29tL3BrbGltb3YvcGltdGltMSQwIgYJKoZIhvcNAQkBFhVw
a2xpbW92QHF1YXJ0c29mdC5jb20wHhcNMTIxMTA2MTQxNjUzWhcNMTMxMTA2MTQx
NjUzWjCBqjELMAkGA1UEBhMCTkwxEjAQBgNVBAgMCUFtc3RlcmRhbTESMBAGA1UE
BwwJQW1zdGVyZGFtMQ8wDQYDVQQKDAZQaW1UaW0xDzANBgNVBAsMBlBpbVRpbTEr
MCkGA1UEAwwiZGV2NTMucXVhcnRzb2Z0LmNvbS9wa2xpbW92L3BpbXRpbTEkMCIG
CSqGSIb3DQEJARYVcGtsaW1vdkBxdWFydHNvZnQuY29tMIGfMA0GCSqGSIb3DQEB
AQUAA4GNADCBiQKBgQDE0d63YwpBLxzxQAW887JALcGruAHkHu7Ui1oc7bCIMy+u
d6rPgNmbFLw3GoGzQ8xhMmksZHsS07IfWRTDeisPHAqfgcApOZbyMyZUAL6+1ko4
xAIPnQSia7l8M4nWgtgqifDCbFKAoPXuWSrYDOFtgSkBLH5xYyFPRc04nnHpoQID
AQABo1AwTjAdBgNVHQ4EFgQUE2oxXYDFRNtgvn8tyXldepRFWzYwHwYDVR0jBBgw
FoAUE2oxXYDFRNtgvn8tyXldepRFWzYwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0B
AQUFAAOBgQB1/S46dWBECaOs4byCysFhzXw8qx8znJkSZcIdDilmg1kkfusXKi2S
DiiFw5gDrc6Qp6WtPmVhxHUWl6O5bOG8lG0Dcppeed9454CGvBShmYdwC6vk0s7/
gVdK2V4fYsUeT6u49ONshvJ/8xhHz2gGXeLWaqHwtK3Dl3S6TIDuoQ==
-----END CERTIFICATE-----';
	}

	/**
	 * Returns test private certificate string.
	 * @return string private certificate string.
	 */
	protected function getTestPrivateCertificate()
	{
		return '-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQDE0d63YwpBLxzxQAW887JALcGruAHkHu7Ui1oc7bCIMy+ud6rP
gNmbFLw3GoGzQ8xhMmksZHsS07IfWRTDeisPHAqfgcApOZbyMyZUAL6+1ko4xAIP
nQSia7l8M4nWgtgqifDCbFKAoPXuWSrYDOFtgSkBLH5xYyFPRc04nnHpoQIDAQAB
AoGAPm1e2gYE86Xw5ShsaYFWcXrR6hiEKQoSsMG+hFxz2M97eTglqolw+/p4tHWo
2+ZORioKJ/V6//67iavkpRfz3dloUlNE9ZzlvqvPjHePt3BI22GI8D84dcqnxWW5
4okEAfDfXk2B4UNOpVNU5FZjg4XvBEbbhRVrsBWAPMduDX0CQQDtFgLLLWr50F3z
lGuFy68Y1d01sZsyf7xUPaLcDWbrnVMIjZIs60BbLg9PZ6sYcwV2RwL/WaJU0Ap/
KKjHW51zAkEA1IWBicQtt6yGaUqydq+ifX8/odFjIrlZklwckLl65cImyxqDYMnA
m+QdbZznSH96BHjduhJAEAtfYx5CVMrXmwJAHKiWedzpm3z2fmUoginW5pejf8QS
UI5kQ4KX1yW/lSeVS+lhDBD73Im6zAxqADCXLm7zC87X8oybWDef/0kxxQJAebRX
AalKMSRo+QVg/F0Kpenoa+f4aNtSc2GyriK6QbeU9b0iPZxsZBoXzD0NqlPucX8y
IyvuagHJR379p4dePwJBAMCkYSATGdhYbeDfySWUro5K0QAvBNj8FuNJQ4rqUxz8
8b+OXIyd5WlmuDRTDGJBTxAYeaioTuMCFWaZm4jG0I4=
-----END RSA PRIVATE KEY-----';
	}

	// Tests :

	public function testGenerateSignature()
	{
		$signatureMethod = new RsaSha1();
		$signatureMethod->setPrivateCertificate($this->getTestPrivateCertificate());
		$signatureMethod->setPublicCertificate($this->getTestPublicCertificate());

		$baseString = 'test_base_string';
		$key = 'test_key';

		$signature = $signatureMethod->generateSignature($baseString, $key);
		$this->assertNotEmpty($signature, 'Unable to generate signature!');
	}

	/**
	 * @depends testGenerateSignature
	 */
	public function testVerify()
	{
		$signatureMethod = new RsaSha1();
		$signatureMethod->setPrivateCertificate($this->getTestPrivateCertificate());
		$signatureMethod->setPublicCertificate($this->getTestPublicCertificate());

		$baseString = 'test_base_string';
		$key = 'test_key';
		$signature = 'unsigned';
		$this->assertFalse($signatureMethod->verify($signature, $baseString, $key), 'Unsigned signature is valid!');

		$generatedSignature = $signatureMethod->generateSignature($baseString, $key);
		$this->assertTrue($signatureMethod->verify($generatedSignature, $baseString, $key), 'Generated signature is invalid!');
	}

	public function testInitPrivateCertificate()
	{
		$signatureMethod = new RsaSha1();

		$certificateFileName = __FILE__;
		$signatureMethod->privateCertificateFile = $certificateFileName;
		$this->assertEquals(file_get_contents($certificateFileName), $signatureMethod->getPrivateCertificate(), 'Unable to fetch private certificate from file!');
	}

	public function testInitPublicCertificate()
	{
		$signatureMethod = new RsaSha1();

		$certificateFileName = __FILE__;
		$signatureMethod->publicCertificateFile = $certificateFileName;
		$this->assertEquals(file_get_contents($certificateFileName), $signatureMethod->getPublicCertificate(), 'Unable to fetch public certificate from file!');
	}
}
