<?php

use yii\helpers\StringHelper;

/**
 * This is the template for generating a CRUD controller class file.
 *
 * @var yii\base\View $this
 * @var yii\gii\generators\crud\Generator $generator
 */

$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
$rules = $generator->generateSearchRules();
$labels = $generator->generateSearchLabels();
$searchAttributes = $generator->getSearchAttributes();
$searchConditions = $generator->generateSearchConditions();

echo "<?php\n";
?>

namespace <?php echo StringHelper::dirname(ltrim($generator->searchModelClass, '\\')); ?>;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use <?php echo ltrim($generator->modelClass, '\\'); ?>;

/**
 * <?php echo $searchModelClass; ?> represents the model behind the search form about <?php echo $modelClass; ?>.
 */
class <?php echo $searchModelClass; ?> extends Model
{
	public $<?php echo implode(";\n\tpublic $", $searchAttributes); ?>;

	public function rules()
	{
		return array(
			<?php echo implode(",\n\t\t\t", $rules); ?>,
		);
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

	public function search($params)
	{
		$query = <?php echo $modelClass; ?>::find();
		$dataProvider = new ActiveDataProvider(array(
			'query' => $query,
		));

		if (!($this->load($params) && $this->validate())) {
			return $dataProvider;
		}

		<?php echo implode("\n\t\t", $searchConditions); ?>

		return $dataProvider;
	}

	protected function addCondition($query, $attribute, $partialMatch = false)
	{
		$value = $this->$attribute;
		if (trim($value) === '') {
			return;
		}
		if ($partialMatch) {
			$value = '%' . strtr($value, array('%'=>'\%', '_'=>'\_', '\\'=>'\\\\')) . '%';
			$query->andWhere(array('like', $attribute, $value));
		} else {
			$query->andWhere(array($attribute => $value));
		}
	}
}
