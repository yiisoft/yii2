Yii Framework 2 elasticsearch extension Change Log
==================================================

2.0.0 beta under development
----------------------------

- Bug #1993: afterFind event in AR is now called after relations have been populated (cebe, creocoder)
- Bug #2324: Fixed QueryBuilder bug when building a query with "query" option (mintao)
- Enh #1313: made index and type available in `ActiveRecord::instantiate()` to allow creating records based on elasticsearch type when doing cross index/type search (cebe)
- Enh #1382: Added a debug toolbar panel for elasticsearch (cebe)
- Enh #1765: Added support for primary key path mapping, pk can now be part of the attributes when mapping is defined (cebe)
- Chg #1765: Changed handling of ActiveRecord primary keys, removed getId(), use getPrimaryKey() instead (cebe)
- Chg #2281: Renamed `ActiveRecord::create()` to `populateRecord()` and changed signature. This method will not call instantiate() anymore (cebe)

2.0.0 alpha, December 1, 2013
-----------------------------

- Initial release.
