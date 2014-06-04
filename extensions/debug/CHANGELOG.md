Yii Framework 2 debug extension Change Log
==========================================

2.0.0-rc under development
--------------------------

- Bug #1263: Fixed the issue that Gii and Debug modules might be affected by incompatible asset manager configuration (qiangxue)
- Enh #3088: The debug module will manage their own URL rules now (qiangxue)
- Enh #3103: debugger panel is now not displayed when printing a page (githubjeka)
- Enh #3108: Added `yii\debug\Module::enableDebugLogs` to disable logging debug logs by default (qiangxue)

2.0.0-beta April 13, 2014
-------------------------

- Bug #1783: Using VarDumper::dumpAsString() instead var_export(), because var_export() does not handle circular references. (djagya)
- Bug #1504: Debug toolbar isn't loaded successfully in some environments when xdebug is enabled (qiangxue)
- Bug #1747: Fixed problems with displaying toolbar on small screens (cebe)
- Bug #1827: Debugger toolbar is loaded twice if an action is calling `run()` to execute another action (qiangxue)
- Enh #1667: Added mail panel (Ragazzo, 6pblcb)
- Enh #2006: Added total queries count monitoring (o-rey, Ragazzo)

2.0.0-alpha, December 1, 2013
-----------------------------

- Initial release.
