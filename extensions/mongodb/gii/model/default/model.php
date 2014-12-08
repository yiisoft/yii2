<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator yii\mongodb\gii\model\Generator */
/* @var $collectionName string full table name */
/* @var $attributes array list of attribute names */
/* @var $className string class name */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */

echo "<?php\n";
?>

namespace <?= $generator->ns ?>;

use Yii;

/**
 * This is the model class for collection "<?= $collectionName ?>".
 *
<?php foreach ($attributes as $attribute): ?>
 * @property <?= $attribute == '_id' ? '\MongoId|string' : 'mixed' ?> <?= "\${$attribute}\n" ?>
<?php endforeach; ?>
 */
class <?= $className ?> extends <?= '\\' . ltrim($generator->baseClass, '\\') . "\n" ?>
{
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
<?php if (empty($generator->databaseName)): ?>
        return '<?= $collectionName ?>';
<?php else: ?>
        return ['<?= $generator->databaseName ?>', '<?= $collectionName ?>'];
<?php endif; ?>
    }
<?php if ($generator->db !== 'mongodb'): ?>

    /**
     * @return \yii\mongodb\Connection the MongoDB connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('<?= $generator->db ?>');
    }
<?php endif; ?>

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
<?php foreach ($attributes as $attribute): ?>
            <?= "'$attribute',\n" ?>
<?php endforeach; ?>
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [<?= "\n            " . implode(",\n            ", $rules) . "\n        " ?>];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
<?php foreach ($labels as $name => $label): ?>
            <?= "'$name' => " . $generator->generateString($label) . ",\n" ?>
<?php endforeach; ?>
        ];
    }
}
