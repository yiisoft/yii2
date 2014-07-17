<?php
/**
 * This is the template for generating the model class of a specified Sphinx index.
 */

/* @var $this yii\web\View */
/* @var $generator yii\sphinx\gii\model\Generator */
/* @var $indexName string full table name */
/* @var $className string class name */
/* @var $indexSchema yii\sphinx\IndexSchema */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */

echo "<?php\n";
?>

namespace <?= $generator->ns ?>;

use Yii;

/**
 * This is the model class for index "<?= $indexName ?>".
 *
<?php foreach ($indexSchema->columns as $column): ?>
 * @property <?= $column->isMva ? 'array' : $column->phpType ?> <?= "\${$column->name}\n" ?>
<?php endforeach; ?>
 */
class <?= $className ?> extends <?= '\\' . ltrim($generator->baseClass, '\\') . "\n" ?>
{
    /**
     * @inheritdoc
     */
    public static function indexName()
    {
        return '<?= $generator->generateIndexName($indexName) ?>';
    }
<?php if ($generator->db !== 'sphinx'): ?>

    /**
     * @return \yii\sphinx\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('<?= $generator->db ?>');
    }
<?php endif; ?>

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
