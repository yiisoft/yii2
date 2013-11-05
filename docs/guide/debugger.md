Debug toolbar and debugger
==========================

Yii2 includes a handy toolbar to aid faster development and debugging as well as debugger. Toolbar displays information
about currently opened page while using debugger you can analyze data collected before.

Installing and configuring
--------------------------

How to use it
-------------

Add these lines to your config file:

```
    'preload' => ['debug'],
    'modules' => [
            'debug' => ['yii\debug\Module']
        ]
```

**Watch out: by default the debug module only works when browsing the website from the localhost. If you want to use it on a remote (staging) server, add the parameter allowedIPs to the config to whitelist your IP.**

Creating your own panels
------------------------

