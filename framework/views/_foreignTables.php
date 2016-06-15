<?php

/**
 * Creates a call for the method `yii\db\Migration::createTable()`
 */
/* @var $foreignKeys array the foreign keys */

if (!empty($foreignKeys)):?>
 * Has foreign keys to the tables:
 *
<?php foreach ($foreignKeys as $fkData): ?>
 * - `<?= $fkData['relatedTable'] ?>`
<?php endforeach;
endif;
