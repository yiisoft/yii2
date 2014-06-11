<?php
/**
 * This is the template for generating the model class of a specified table.
 *
 * @var yii\web\View $this
 * @var yii\mongodb\gii\model\Generator $generator
 * @var string $collectionName full table name
 * @var array $attributes list of attribute names
 * @var string $className class name
 * @var string[] $labels list of attribute labels (name => label)
 * @var string[] $rules list of validation rules
 */

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
