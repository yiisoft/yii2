<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\clients;

use yii\authclient\OpenId;

/**
 * GoogleOpenId allows authentication via Google OpenId.
 * Unlike Google OAuth you do not need to register your application anywhere in order to use Google OpenId.
 *
 * Example application configuration:
 *
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'google' => [
 *                 'class' => 'yii\authclient\clients\GoogleOpenId'
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class GoogleOpenId extends OpenId
{
    /**
     * @inheritdoc
     */
    public $authUrl = 'https://www.google.com/accounts/o8/id';
    /**
     * @inheritdoc
     */
    public $requiredAttributes = [
        'namePerson/first',
        'namePerson/last',
        'contact/email',
        'pref/language',
    ];

    /**
     * @inheritdoc
     */
    protected function defaultNormalizeUserAttributeMap()
    {
        return [
            'first_name' => 'namePerson/first',
            'last_name' => 'namePerson/last',
            'email' => 'contact/email',
            'language' => 'pref/language',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function defaultViewOptions()
    {
        return [
            'popupWidth' => 880,
            'popupHeight' => 520,
        ];
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'google';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return 'Google';
    }
}
