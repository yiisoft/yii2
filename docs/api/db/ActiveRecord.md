- `beforeInsert`. Raised before the record is saved.
   By setting [[\yii\base\ModelEvent::isValid]] to be false, the normal [[save()]] will be stopped.
- `afterInsert`. Raised after the record is saved.
- `beforeUpdate`. Raised before the record is saved.
   By setting [[\yii\base\ModelEvent::isValid]] to be false, the normal [[save()]] will be stopped.
- `afterUpdate`. Raised after the record is saved.
- `beforeDelete`. Raised before the record is deleted.
   By setting [[\yii\base\ModelEvent::isValid]] to be false, the normal [[delete()]] process will be stopped.
- `afterDelete`. Raised after the record is deleted.
