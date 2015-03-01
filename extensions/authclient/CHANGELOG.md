Yii Framework 2 authclient extension Change Log
===============================================

2.0.3 March 01, 2015
--------------------

- Enh #6892: Default value of `yii\authclient\clients\Twitter::$authUrl` changed to 'authenticate', allowing usage of previous logged user without request an access (kotchuprik)


2.0.2 January 11, 2015
----------------------

- Bug #6502: Fixed `\yii\authclient\OAuth2::refreshAccessToken()` does not save fetched token (sebathi)
- Bug #6510: Fixed infinite redirect loop using default `\yii\authclient\AuthAction::cancelUrl` (klimov-paul)


2.0.1 December 07, 2014
-----------------------

- Bug #6000: Fixed CCS for `yii\authclient\widgets\AuthChoice` does not loaded if `popupMode` disabled (klimov-paul)


2.0.0 October 12, 2014
----------------------

- Enh #5135: Added ability to operate nested and complex attributes via `yii\authclient\BaseClient::normalizeUserAttributeMap` (zinzinday, klimov-paul)


2.0.0-rc September 27, 2014
---------------------------

- Bug #3633: OpenId return URL comparison advanced to prevent url encode problem (klimov-paul)
- Bug #4490: `yii\authclient\widgets\AuthChoice` does not preserve initial settings while opening popup (klimov-paul)
- Bug #5011: OAuth API Response with 20x status were not considered success (ychongsaytc)
- Enh #3416: VKontakte OAuth support added (klimov-paul)
- Enh #4076: Request HTTP headers argument added to `yii\authclient\BaseOAuth::api()` method (klimov-paul)
- Enh #4134: `yii\authclient\InvalidResponseException` added for tracking invalid remote server response (klimov-paul)
- Enh #4139: User attributes requesting at GoogleOAuth switched to Google+ API (klimov-paul)


2.0.0-beta April 13, 2014
-------------------------

- Initial release.