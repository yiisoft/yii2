<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\data;

use Yii;
use yii\base\Component;
use yii\base\Model;
use yii\helpers\StringHelper;

/**
 * ModelSerializer converts a model or a list of models into an array representation with selected fields.
 *
 * Used together with [[\yii\web\ResponseFormatter]], ModelSerializer can be used to serve model data
 * in JSON or XML format for REST APIs.
 *
 * ModelSerializer provides two methods [[export()]] and [[exportAll()]] to convert model(s) into array(s).
 * The former works for a single model, while the latter for an array of models.
 * During conversion, it will check which fields are requested and only provide valid fields (as declared
 * in [[fields()]] and [[expand()]]) in the array result.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ModelSerializer extends Component
{
	/**
	 * @var string the model class that this API is serving. If not set, it will be initialized
	 * as the class of the model(s) being exported by [[export()]] or [[exportAll()]].
	 */
	public $modelClass;
	/**
	 * @var mixed the context information. If not set, it will be initialized as the "user" application component.
	 * You can use the context information to conditionally control which fields can be returned for a model.
	 */
	public $context;
	/**
	 * @var array|string an array or a string of comma separated field names representing
	 * which fields should be returned. Only fields declared in [[fields()]] will be respected.
	 * If this property is empty, all fields declared in [[fields()]] will be returned.
	 */
	public $fields;
	/**
	 * @var array|string an array or a string of comma separated field names representing
	 * which fields should be returned in addition to those declared in [[fields()]].
	 * Only fields declared in [[expand()]] will be respected.
	 */
	public $expand;
	/**
	 * @var integer the error code to be used in the result of [[exportErrors()]].
	 */
	public $validationErrorCode = 1024;
	/**
	 * @var string the error message to be used in the result of [[exportErrors()]].
	 */
	public $validationErrorMessage = 'Validation Failed';
	/**
	 * @var array a list of serializer classes indexed by their corresponding model classes.
	 * This property is used by [[createSerializer()]] to serialize embedded objects.
	 * @see createSerializer()
	 */
	public $serializers = [];
	/**
	 * @var array a list of paths or path aliases specifying how to look for a serializer class
	 * given a model class. If the base name of a model class is `Xyz`, the corresponding
	 * serializer class being looked for would be `XyzSerializer` under each of the paths listed here.
	 */
	public $serializerPaths = ['@app/serializers'];
	/**
	 * @var array the loaded serializer objects indexed by the model class names
	 */
	private $_serializers = [];


	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		if ($this->context === null && Yii::$app) {
			$this->context = Yii::$app->user;
		}
	}

	/**
	 * Exports a model object by converting it into an array based on the specified fields.
	 * @param Model $model the model being exported
	 * @return array the exported data
	 */
	public function export($model)
	{
		if ($this->modelClass === null) {
			$this->modelClass = get_class($model);
		}

		$fields = $this->resolveFields($this->fields, $this->expand);
		return $this->exportObject($model, $fields);
	}

	/**
	 * Exports an array of model objects by converting it into an array based on the specified fields.
	 * @param Model[] $models the models being exported
	 * @return array the exported data
	 */
	public function exportAll(array $models)
	{
		if (empty($models)) {
			return [];
		}

		if ($this->modelClass === null) {
			$this->modelClass = get_class(reset($models));
		}

		$fields = $this->resolveFields($this->fields, $this->expand);
		$result = [];
		foreach ($models as $model) {
			$result[] = $this->exportObject($model, $fields);
		}
		return $result;
	}

	/**
	 * Exports the model validation errors.
	 * @param Model $model
	 * @return array
	 */
	public function exportErrors($model)
	{
		$result = [
			'code' => $this->validationErrorCode,
			'message' => $this->validationErrorMessage,
			'errors' => [],
		];
		foreach ($model->getFirstErrors() as $name => $message) {
			$result['errors'][] = [
				'field' => $name,
				'message' => $message,
			];
		}
		return $result;
	}

	/**
	 * Returns a list of fields that can be returned to end users.
	 *
	 * These are the fields that should be returned by default when a user does not explicitly specify which
	 * fields to return for a model. If the user explicitly which fields to return, only the fields declared
	 * in this method can be returned. All other fields will be ignored.
	 *
	 * By default, this method returns [[Model::attributes()]], which are the attributes defined by a model.
	 *
	 * You may override this method to select which fields can be returned or define new fields based
	 * on model attributes.
	 *
	 * The value returned by this method should be an array of field definitions. The array keys
	 * are the field names, and the array values are the corresponding attribute names or callbacks
	 * returning field values. If a field name is the same as the corresponding attribute name,
	 * you can use the field name without a key.
	 *
	 * @return array field name => attribute name or definition
	 */
	protected function fields()
	{
		if (is_subclass_of($this->modelClass, Model::className())) {
			/** @var Model $model */
			$model = new $this->modelClass;
			return $model->attributes();
		} else {
			return array_keys(get_class_vars($this->modelClass));
		}
	}

	/**
	 * Returns a list of additional fields that can be returned to end users.
	 *
	 * The default implementation returns an empty array. You may override this method to return
	 * a list of additional fields that can be returned to end users. Please refer to [[fields()]]
	 * on the format of the return value.
	 *
	 * You usually override this method by returning a list of relation names.
	 *
	 * @return array field name => attribute name or definition
	 */
	protected function expand()
	{
		return [];
	}

	/**
	 * Filters the data to be exported to end user.
	 * The default implementation does nothing. You may override this method to remove
	 * certain fields from the data being exported based on the [[context]] information.
	 * You may also use this method to add some common fields, such as class name, to the data.
	 * @param array $data the data being exported
	 * @return array the filtered data
	 */
	protected function filter($data)
	{
		return $data;
	}

	/**
	 * Returns the serializer for the specified model class.
	 * @param string $modelClass fully qualified model class name
	 * @return static the serializer
	 */
	protected function getSerializer($modelClass)
	{
		if (!isset($this->_serializers[$modelClass])) {
			$this->_serializers[$modelClass] = $this->createSerializer($modelClass);
		}
		return $this->_serializers[$modelClass];
	}

	/**
	 * Creates a serializer object for the specified model class.
	 *
	 * This method tries to create an appropriate serializer using the following algorithm:
	 *
	 * - Check if [[serializers]] specifies the serializer class for the model class and
	 *   create an instance of it if available;
	 * - Search for a class named `XyzSerializer` under the paths specified by [[serializerPaths]],
	 *   where `Xyz` stands for the model class.
	 * - If both of the above two strategies fail, simply return an instance of `ModelSerializer`.
	 *
	 * @param string $modelClass the model class
	 * @return ModelSerializer the new model serializer
	 */
	protected function createSerializer($modelClass)
	{
		if (isset($this->serializers[$modelClass])) {
			$config = $this->serializers[$modelClass];
			if (!is_array($config)) {
				$config = ['class' => $config];
			}
		} else {
			$className = StringHelper::basename($modelClass) . 'Serializer';
			foreach ($this->serializerPaths as $path) {
				$path = Yii::getAlias($path);
				if (is_file($path . "/$className.php")) {
					$config = ['class' => $className];
					break;
				}
			}
		}

		if (!isset($config)) {
			$config = ['class' => __CLASS__];
		}
		$config['modelClass'] = $modelClass;
		$config['context'] = $this->context;

		return Yii::createObject($config);
	}

	/**
	 * Returns the fields of the model that need to be returned to end user
	 * @param string|array $fields an array or a string of comma separated field names representing
	 * which fields should be returned.
	 * @param string|array $expand an array or a string of comma separated field names representing
	 * which additional fields should be returned.
	 * @return array field name => field definition (attribute name or callback)
	 */
	protected function resolveFields($fields, $expand)
	{
		if (!is_array($fields)) {
			$fields = preg_split('/\s*,\s*/', $fields, -1, PREG_SPLIT_NO_EMPTY);
		}
		if (!is_array($expand)) {
			$expand = preg_split('/\s*,\s*/', $expand, -1, PREG_SPLIT_NO_EMPTY);
		}

		$result = [];

		foreach ($this->fields() as $field => $definition) {
			if (is_integer($field)) {
				$field = $definition;
			}
			if (empty($fields) || in_array($field, $fields, true)) {
				$result[$field] = $definition;
			}
		}

		if (empty($expand)) {
			return $result;
		}

		foreach ($this->expand() as $field => $definition) {
			if (is_integer($field)) {
				$field = $definition;
			}
			if (in_array($field, $expand, true)) {
				$result[$field] = $definition;
			}
		}

		return $result;
	}

	/**
	 * Exports an object by converting it into an array based on the given field definitions.
	 * @param object $model the model being exported
	 * @param array $fields field definitions (field name => field definition)
	 * @return array the exported model data
	 */
	protected function exportObject($model, $fields)
	{
		$data = [];
		foreach ($fields as $field => $attribute) {
			if (is_string($attribute)) {
				$value = $model->$attribute;
			} else {
				$value = call_user_func($attribute, $model, $field);
			}
			if (is_object($value)) {
				$value = $this->getSerializer(get_class($value))->export($value);
			} elseif (is_array($value)) {
				foreach ($value as $i => $v) {
					if (is_object($v)) {
						$value[$i] = $this->getSerializer(get_class($v))->export($v);
					}
					// todo: array of array
				}
			}
			$data[$field] = $value;
		}
		return $this->filter($data);
	}
}
