<?php
/**
 * This is the template for generating the model class of a specified table.
 *
 * @var yii\base\View $this
 * @var yii\gii\generators\model\Generator $generator
 * @var string $tableName full table name
 * @var string $className class name
 * @var yii\db\TableSchema $tableSchema
 * @var string[] $labels list of attribute labels (name=>label)
 * @var string[] $rules list of validation rules
 * @var array $relations list of relations (name=>relation declaration)
 */

echo "<?php\n";
?>

namespace <?php echo $generator->ns; ?>;

/**
 * This is the model class for table "<?php echo $tableName; ?>".
 *
<?php foreach ($tableSchema->columns as $column): ?>
 * @property <?php echo "{$column->phpType} \${$column->name}\n"; ?>
<?php endforeach; ?>
<?php if (!empty($relations)): ?>
 *
<?php foreach ($relations as $name => $relation): ?>
 * @property <?php echo $relation[1] . ($relation[2] ? '[]' : '') . ' $' . lcfirst($name) . "\n"; ?>
<?php endforeach; ?>
<?php endif; ?>
 */
class <?php echo $className; ?> extends <?php echo '\\' . ltrim($generator->baseClass, '\\') . "\n"; ?>
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
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
<?php foreach ($relations as $name => $relation): ?>

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function get<?php echo $name; ?>()
	{
		<?php echo $relation[0] . "\n"; ?>
	}
<?php endforeach; ?>
}
