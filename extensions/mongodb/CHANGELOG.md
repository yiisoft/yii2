Yii Framework 2 mongodb extension Change Log
============================================

2.0.1 under development
-----------------------

- Enh #3855: Added debug toolbar panel for MongoDB (klimov-paul)


2.0.0 October 12, 2014
----------------------

- Bug #5303: Fixed `yii\mongodb\Collection` unable to fetch default database name from DSN with parameters (klimov-paul)
- Bug #5411: Fixed `yii\mongodb\ActiveRecord` unable to fetch 'hasMany' referred by array of `\MongoId` (klimov-paul)


2.0.0-rc September 27, 2014
---------------------------

- Bug #2337: `yii\mongodb\Collection::buildLikeCondition()` fixed to escape regular expression (klimov-paul)
- Bug #3385: Fixed "The 'connected' property is deprecated" (samdark)
- Bug #4879: Fixed `yii\mongodb\Collection::buildInCondition()` handles non-sequent key arrays (klimov-paul)
- Enh #3520: Added `unlinkAll()`-method to active record to remove all records of a model relation (NmDimas, samdark, cebe)
- Enh #3778: Gii generator for Active Record model added (klimov-paul)
- Enh #3947: Migration support added (klimov-paul)
- Enh #4048: Added `init` event to `ActiveQuery` classes (qiangxue)
- Enh #4086: changedAttributes of afterSave Event now contain old values (dizews)
- Enh #4335: `yii\mongodb\log\MongoDbTarget` log target added (klimov-paul)


2.0.0-beta April 13, 2014
-------------------------

- Initial release.
