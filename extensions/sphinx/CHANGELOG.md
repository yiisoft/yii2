Yii Framework 2 sphinx extension Change Log
===========================================

2.0.1 under development
-----------------------

- Bug #5601: Simple conditions in Query::where() and ActiveQuery::where() did not allow `yii\db\Expression` to be used as the value (cebe, stevekr)
- Enh #5223: Query builder now supports selecting sub-queries as columns (qiangxue)


2.0.0 October 12, 2014
----------------------

- Enh #5211: `yii\sphinx\Query` now supports 'HAVING' (klimov-paul)


2.0.0-rc September 27, 2014
---------------------------

- Bug #3668: Escaping of the special characters at 'MATCH' statement added (klimov-paul)
- Bug #4018: AR relation eager loading does not work with db models (klimov-paul)
- Bug #4375: Distributed indexes support provided (klimov-paul)
- Bug #4830: `ActiveQuery` instance reusage ability granted (klimov-paul)
- Enh #3520: Added `unlinkAll()`-method to active record to remove all records of a model relation (NmDimas, samdark, cebe)
- Enh #4048: Added `init` event to `ActiveQuery` classes (qiangxue)
- Enh #4086: changedAttributes of afterSave Event now contain old values (dizews)
- Enh: Added support for using sub-queries when building a DB query with `IN` condition (qiangxue)
- Chg #2287: Split `yii\sphinx\ColumnSchema::typecast()` into two methods `phpTypecast()` and `dbTypecast()` to allow specifying PDO type explicitly (cebe)


2.0.0-beta April 13, 2014
-------------------------

- Bug #1993: afterFind event in AR is now called after relations have been populated (cebe, creocoder)
- Bug #2160: SphinxQL does not support `OFFSET` (qiangxue, romeo7)
- Enh #1398: Refactor ActiveRecord to use BaseActiveRecord class of the framework (klimov-paul)
- Enh #2002: Added filterWhere() method to yii\spinx\Query to allow easy addition of search filter conditions by ignoring empty search fields (samdark, cebe)
- Enh #2892: ActiveRecord dirty attributes are now reset after call to `afterSave()` so information about changed attributes is available in `afterSave`-event (cebe)
- Enh #3964: Gii generator for Active Record model added (klimov-paul)
- Chg #2281: Renamed `ActiveRecord::create()` to `populateRecord()` and changed signature. This method will not call instantiate() anymore (cebe)
- Chg #2146: Removed `ActiveRelation` class and moved the functionality to `ActiveQuery`.
             All relational queries are now directly served by `ActiveQuery` allowing to use
             custom scopes in relations (cebe)


2.0.0-alpha, December 1, 2013
-----------------------------

- Initial release.
