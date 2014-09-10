Yii Framework 2 elasticsearch extension Change Log
==================================================

2.0.0-rc under development
--------------------------

- Bug #3587: Fixed an issue with storing empty records (cebe)
- Bug #4187: Elasticsearch dynamic scripting is disabled in 1.2.0, so do not use it in query builder (cebe)
- Enh #3520: Added `unlinkAll()`-method to active record to remove all records of a model relation (NmDimas, samdark, cebe)
- Enh #3527: Added `highlight` property to Query and ActiveRecord. (Borales)
- Enh #4048: Added `init` event to `ActiveQuery` classes (qiangxue)
- Enh #4086: changedAttributes of afterSave Event now contain old values (dizews)
- Enh: Make error messages more readable in HTML output (cebe)
- Enh: Added support for query stats (cebe)
- Enh: Added support for query suggesters (cebe)
- Chg #4451: Removed support for facets and replaced them with aggregations (cebe, tadaszelvys)
- Chg: asArray in ActiveQuery is now equal to using the normal Query. This means, that the output structure has changed and `with` is supported anymore. (cebe)
- Chg: Deletion of a record is now also considered successful if the record did not exist. (cebe)
- Chg: Requirement changes: Yii now requires elasticsearch version 1.0 or higher (cebe)


2.0.0-beta April 13, 2014
-------------------------

- Bug #1993: afterFind event in AR is now called after relations have been populated (cebe, creocoder)
- Bug #2324: Fixed QueryBuilder bug when building a query with "query" option (mintao)
- Enh #1313: made index and type available in `ActiveRecord::instantiate()` to allow creating records based on elasticsearch type when doing cross index/type search (cebe)
- Enh #1382: Added a debug toolbar panel for elasticsearch (cebe)
- Enh #1765: Added support for primary key path mapping, pk can now be part of the attributes when mapping is defined (cebe)
- Enh #2002: Added filterWhere() method to yii\elasticsearch\Query to allow easy addition of search filter conditions by ignoring empty search fields (samdark, cebe)
- Enh #2892: ActiveRecord dirty attributes are now reset after call to `afterSave()` so information about changed attributes is available in `afterSave`-event (cebe)
- Chg #1765: Changed handling of ActiveRecord primary keys, removed getId(), use getPrimaryKey() instead (cebe)
- Chg #2281: Renamed `ActiveRecord::create()` to `populateRecord()` and changed signature. This method will not call instantiate() anymore (cebe)
- Chg #2146: Removed `ActiveRelation` class and moved the functionality to `ActiveQuery`.
             All relational queries are now directly served by `ActiveQuery` allowing to use
             custom scopes in relations (cebe)


2.0.0-alpha, December 1, 2013
-----------------------------

- Initial release.

