<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\signature;

use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;

/**
 * RsaSha1 represents 'RSA-SHA1' signature method.
 *
 * Note: This class require PHP "OpenSSL" extension({@link http://php.net/manual/en/book.openssl.php}).
 *
 * @property string $privateCertificate Private key certificate content.
 * @property string $publicCertificate Public key certificate content.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class RsaSha1 extends BaseMethod
{
    /**
     * @var string OpenSSL private key certificate content.
     * This value can be fetched from file specified by {@link privateCertificateFile}.
     */
    protected $_privateCertificate;
    /**
     * @var string OpenSSL public key certificate content.
     * This value can be fetched from file specified by {@link publicCertificateFile}.
     */
    protected $_publicCertificate;
    /**
     * @var string path to the file, which holds private key certificate.
     */
    public $privateCertificateFile = '';
    /**
     * @var string path to the file, which holds public key certificate.
     */
    public $publicCertificateFile = '';

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!function_exists('openssl_sign')) {
            throw new NotSupportedException('PHP "OpenSSL" extension is required.');
        }
    }

    /**
     * @param string $publicCertificate public key certificate content.
     */
    public function setPublicCertificate($publicCertificate)
    {
        $this->_publicCertificate = $publicCertificate;
    }

    /**
     * @return string public key certificate content.
     */
    public function getPublicCertificate()
    {
        if ($this->_publicCertificate === null) {
            $this->_publicCertificate = $this->initPublicCertificate();
        }

        return $this->_publicCertificate;
    }

    /**
     * @param string $privateCertificate private key certificate content.
     */
    public function setPrivateCertificate($privateCertificate)
    {
        $this->_privateCertificate = $privateCertificate;
    }

    /**
     * @return string private key certificate content.
     */
    public function getPrivateCertificate()
    {
        if ($this->_privateCertificate === null) {
            $this->_privateCertificate = $this->initPrivateCertificate();
        }

        return $this->_privateCertificate;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'RSA-SHA1';
    }

    /**
     * Creates initial value for {@link publicCertificate}.
     * This method will attempt to fetch the certificate value from {@link publicCertificateFile} file.
     * @throws InvalidConfigException on failure.
     * @return string                 public certificate content.
     */
    protected function initPublicCertificate()
    {
        if (!empty($this->publicCertificateFile)) {
            if (!file_exists($this->publicCertificateFile)) {
                throw new InvalidConfigException("Public certificate file '{$this->publicCertificateFile}' does not exist!");
            }

            return file_get_contents($this->publicCertificateFile);
        } else {
            return '';
        }
    }

    /**
     * Creates initial value for {@link privateCertificate}.
     * This method will attempt to fetch the certificate value from {@link privateCertificateFile} file.
     * @throws InvalidConfigException on failure.
     * @return string                 private certificate content.
     */
    protected function initPrivateCertificate()
    {
        if (!empty($this->privateCertificateFile)) {
            if (!file_exists($this->privateCertificateFile)) {
                throw new InvalidConfigException("Private certificate file '{$this->privateCertificateFile}' does not exist!");
            }

            return file_get_contents($this->privateCertificateFile);
        } else {
            return '';
        }
    }

    /**
     * @inheritdoc
     */
    public function generateSignature($baseString, $key)
    {
        $privateCertificateContent = $this->getPrivateCertificate();
        // Pull the private key ID from the certificate
        $privateKeyId = openssl_pkey_get_private($privateCertificateContent);
        // Sign using the key
        openssl_sign($baseString, $signature, $privateKeyId);
        // Release the key resource
        openssl_free_key($privateKeyId);

        return base64_encode($signature);
    }

    /**
     * @inheritdoc
     */
    public function verify($signature, $baseString, $key)
    {
        $decodedSignature = base64_decode($signature);
        // Fetch the public key cert based on the request
        $publicCertificate = $this->getPublicCertificate();
        // Pull the public key ID from the certificate
        $publicKeyId = openssl_pkey_get_public($publicCertificate);
        // Check the computed signature against the one passed in the query
        $verificationResult = openssl_verify($baseString, $decodedSignature, $publicKeyId);
        // Release the key resource
        openssl_free_key($publicKeyId);

        return ($verificationResult == 1);
    }
}
