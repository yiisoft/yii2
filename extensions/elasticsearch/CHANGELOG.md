Yii Framework 2 elasticsearch extension Change Log
==================================================

2.0.2 under development
-----------------------

- no changes in this release.


2.0.1 December 07, 2014
-----------------------

- Bug [#5662](https://github.com/yiisoft/yii2/issues/5662): Elasticsearch AR updateCounters() now uses explicitly `groovy` script for updating making it compatible with ES >1.3.0 (cebe)
- Bug [#6065](https://github.com/yiisoft/yii2/issues/6065): `ActiveRecord::unlink()` was failing in some situations when working with relations via array valued attributes (cebe)
- Enh [#5758](https://github.com/yiisoft/yii2/issues/5758): Allow passing custom options to `ActiveRecord::update()` and `::delete()` including support for routing needed for updating records with parent relation (cebe)
- Enh: Add support for optimistic locking (cebe)


2.0.0 October 12, 2014
----------------------

- Enh [#3381](https://github.com/yiisoft/yii2/issues/3381): Added ActiveRecord::arrayAttributes() to define attributes that should be treated as array when retrieved via `fields` (cebe)


2.0.0-rc September 27, 2014
---------------------------

- Bug [#3587](https://github.com/yiisoft/yii2/issues/3587): Fixed an issue with storing empty records (cebe)
- Bug [#4187](https://github.com/yiisoft/yii2/issues/4187): Elasticsearch dynamic scripting is disabled in 1.2.0, so do not use it in query builder (cebe)
- Enh [#3527](https://github.com/yiisoft/yii2/issues/3527): Added `highlight` property to Query and ActiveRecord. (Borales)
- Enh [#4048](https://github.com/yiisoft/yii2/issues/4048): Added `init` event to `ActiveQuery` classes (qiangxue)
- Enh [#4086](https://github.com/yiisoft/yii2/issues/4086): changedAttributes of afterSave Event now contain old values (dizews)
- Enh: Make error messages more readable in HTML output (cebe)
- Enh: Added support for query stats (cebe)
- Enh: Added support for query suggesters (cebe, tvdavid)
- Enh: Added support for delete by query (cebe, tvdavid)
- Chg [#4451](https://github.com/yiisoft/yii2/issues/4451): Removed support for facets and replaced them with aggregations (cebe, tadaszelvys)
- Chg: asArray in ActiveQuery is now equal to using the normal Query. This means, that the output structure has changed and `with` is supported anymore. (cebe)
- Chg: Deletion of a record is now also considered successful if the record did not exist. (cebe)
- Chg: Requirement changes: Yii now requires elasticsearch version 1.0 or higher (cebe)


2.0.0-beta April 13, 2014
-------------------------

- Bug [#1993](https://github.com/yiisoft/yii2/issues/1993): afterFind event in AR is now called after relations have been populated (cebe, creocoder)
- Bug [#2324](https://github.com/yiisoft/yii2/issues/2324): Fixed QueryBuilder bug when building a query with "query" option (mintao)
- Enh [#1313](https://github.com/yiisoft/yii2/issues/1313): made index and type available in `ActiveRecord::instantiate()` to allow creating records based on elasticsearch type when doing cross index/type search (cebe)
- Enh [#1382](https://github.com/yiisoft/yii2/issues/1382): Added a debug toolbar panel for elasticsearch (cebe)
- Enh [#1765](https://github.com/yiisoft/yii2/issues/1765): Added support for primary key path mapping, pk can now be part of the attributes when mapping is defined (cebe)
- Enh [#2002](https://github.com/yiisoft/yii2/issues/2002): Added filterWhere() method to yii\elasticsearch\Query to allow easy addition of search filter conditions by ignoring empty search fields (samdark, cebe)
- Enh [#2892](https://github.com/yiisoft/yii2/issues/2892): ActiveRecord dirty attributes are now reset after call to `afterSave()` so information about changed attributes is available in `afterSave`-event (cebe)
- Chg [#1765](https://github.com/yiisoft/yii2/issues/1765): Changed handling of ActiveRecord primary keys, removed getId(), use getPrimaryKey() instead (cebe)
- Chg [#2281](https://github.com/yiisoft/yii2/issues/2281): Renamed `ActiveRecord::create()` to `populateRecord()` and changed signature. This method will not call instantiate() anymore (cebe)
- Chg [#2146](https://github.com/yiisoft/yii2/issues/2146): Removed `ActiveRelation` class and moved the functionality to `ActiveQuery`.
             All relational queries are now directly served by `ActiveQuery` allowing to use
             custom scopes in relations (cebe)


2.0.0-alpha, December 1, 2013
-----------------------------

- Initial release.

