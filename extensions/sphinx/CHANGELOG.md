Yii Framework 2 sphinx extension Change Log
===========================================

2.0.0-rc under development
--------------------------

- no changes in this release.


2.0.0-beta April 13, 2014
-------------------------

- Bug #1993: afterFind event in AR is now called after relations have been populated (cebe, creocoder)
- Bug #2160: SphinxQL does not support `OFFSET` (qiangxue, romeo7)
- Enh #1398: Refactor ActiveRecord to use BaseActiveRecord class of the framework (klimov-paul)
- Enh #2002: Added filterWhere() method to yii\spinx\Query to allow easy addition of search filter conditions by ignoring empty search fields (samdark, cebe)
- Enh #2892: ActiveRecord dirty attributes are now reset after call to `afterSave()` so information about changed attributes is available in `afterSave`-event (cebe)
- Chg #2281: Renamed `ActiveRecord::create()` to `populateRecord()` and changed signature. This method will not call instantiate() anymore (cebe)
- Chg #2146: Removed `ActiveRelation` class and moved the functionality to `ActiveQuery`.
             All relational queries are now directly served by `ActiveQuery` allowing to use
             custom scopes in relations (cebe)

2.0.0-alpha, December 1, 2013
-----------------------------

- Initial release.
