<?php
/**
 * This is the template for generating the model class of a specified table.
 *
 * @var yii\base\View $this
 * @var yii\gii\generators\model\Generator $generator
 * @var string $tableName
 * @var string $className
 * @var yii\db\TableSchema $tableSchema
 * @var string[] $labels
 * @var string[] $rules
 *
 * - $tableName: the table name for this class (prefix is already removed if necessary)
 * - $modelClass: the model class name
 * - $tableSchema: list of table columns (name=>CDbColumnSchema)
 * - $labels: list of attribute labels (name=>label)
 * - $rules: list of validation rules
 * - $relations: list of relations (name=>relation declaration)
 */

echo "<?php\n";
?>

namespace <?php echo $generator->ns; ?>;

/**
 * This is the model class for table "<?php echo $tableName; ?>".
 *
 * Attributes:
 *
<?php foreach ($tableSchema->columns as $column): ?>
 * @property <?php echo "{$column->phpType} \${$column->name}\n"; ?>
<?php endforeach; ?>
 */
class <?php echo $className; ?> extends <?php echo '\\' . ltrim($generator->baseClass, '\\') . "\n"; ?>
{
	/**
	 * @inheritdoc
	 */
	public function tableName()
	{
		return '<?php echo $tableName; ?>';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return array(<?php echo "\n\t\t\t" . implode(",\n\t\t\t", $rules) . "\n\t\t"; ?>);
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return array(
<?php foreach ($labels as $name => $label): ?>
			<?php echo "'$name' => '" . addslashes($label) . "',\n"; ?>
<?php endforeach; ?>
		);
	}
}
