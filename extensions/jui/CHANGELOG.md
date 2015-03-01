Yii Framework 2 jui extension Change Log
========================================

2.0.3 March 01, 2015
--------------------

- Enh #7127: `name` or `model` and `attribute` are no longer required properties of `yii\jui\InputWidget` (nirvana-msu, cebe)


2.0.2 January 11, 2015
----------------------

- Enh #6570: Datepicker now uses fallback to find language files, e.g. application language is `de-DE` and the translation files does not exists, it will use `de` instead (cebe)
- Enh #6471: Datepicker will now show an empty field when value is an empty string (cebe)


2.0.1 December 07, 2014
-----------------------

- no changes in this release.


2.0.0 October 12, 2014
----------------------

- no changes in this release.


2.0.0-rc September 27, 2014
---------------------------

- Chg #1551: Jui datepicker has a new property `$dateFormat` which is used to set the clientOption `dateFormat`.
   The new property does not use the datepicker formatting syntax anymore but uses the same as the `yii\i18n\Formatter`
   class which is the ICU syntax for date formatting, you have to adjust all your DatePicker widgets to use
   the new property instead of setting the dateFormat in the clientOptions (cebe)


2.0.0-beta April 13, 2014
-------------------------

- Bug #1550: fixed the issue that JUI input widgets did not property input IDs. (qiangxue)
- Bug #2514: Jui sortable clientEvents were not working because of wrong naming assumptions. (cebe)
- Enh #2573: Jui datepicker now uses the current application language by default. (andy5)

2.0.0-alpha, December 1, 2013
-----------------------------

- Initial release.
