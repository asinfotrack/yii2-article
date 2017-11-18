<?php
namespace asinfotrack\yii2\article;

use Yii;
use yii\helpers\ArrayHelper;
use yii\jui\DatePicker;

/**
 * Main class for the article module
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 *
 * @property \asinfotrack\yii2\article\components\ArticleRenderer $renderer
 */
class Module extends \yii\base\Module implements yii\base\BootstrapInterface
{

	/**
	 * @var callable|\Closure an optional callback to create the input field for the intro of
	 * the article. Use this callback to implement an external editor like TinyMCE or similar.
	 *
	 * The callback should have the signature as of the following example and return a string
	 * containing the form code of the input field.
	 *
	 * ```php
	 * function ($form, $model, $attribute, $module, $view) {
	 *     return $form->field($model, $attribute)->textarea(['rows'=>5]);
	 * }
	 * ```
	 *
	 * If not set, a regular text area will be rendered.
	 *
	 * @see \asinfotrack\yii2\article\Module::defaultEditorInput()
	 */
	public $introInputCallback;

	/**
	 * @var callable|\Closure an optional callback to create the input field for the content of
	 * the article. Use this callback to implement an external editor like TinyMCE or similar.
	 *
	 * The callback should have the signature as of the following example and return a string
	 * containing the form code of the input field.
	 *
	 * ```php
	 * function ($form, $model, $attribute, $module, $view) {
	 *     return $form->field($model, $attribute)->textarea(['rows'=>5]);
	 * }
	 * ```
	 *
	 * If not set, a regular text area will be rendered.
	 *
	 * @see \asinfotrack\yii2\article\Module::defaultEditorInput()
	 */
	public $contentInputCallback;

	/**
	 * @var callable|\Closure an optional callback to create the input field for the intro of
	 * the article. Use this callback to implement an external date picker. Remember to reconfigure
	 * the validators of the article model as well!
	 *
	 * The callback should have the signature as of the following example and return a string
	 * containing the form code of the input field.
	 *
	 * ```php
	 * function ($form, $model, $attribute, $module, $view) {
	 *     return $form->field($model, $attribute)->widget(DatePicker::className(), []);
	 * }
	 * ```
	 *
	 * If not set, the datepicker of yii2-jui will be rendered.
	 *
	 * @see \asinfotrack\yii2\article\Module::defaultDateInput()
	 * @see \asinfotrack\yii2\article\models\Article::rules()
	 */
	public $dateInputCallback;

	/**
	 * @var bool whether or not to show a preview of the article content in the detail view of
	 * an article
	 */
	public $enableArticlePreview = true;

	/**
	 * @var callable an optional callback for the user relations as used by the two models
	 * within their blameable behaviors. This callback needs to be set, to use the `createdBy`
	 * and `changedBy` relations of the article and article category models.
	 *
	 * The callback needs to have the signature `function ($model, $attribute)`, where `$model`
	 * is the instance of the article or article category and `$attribute` is the field to build
	 * the relation upon (created_by or updated_by). The function should return an `ActiveQuery`
	 * the same way a regular relation is specified within yii2.
	 *
	 * Example for a callback:
	 *
	 * ```php
	 * function ($model, $attribute) {
	 *     return $model->hasOne(User::className(), ['id'=>$attribute]);
	 * }
	 * ```
	 */
	public $userRelationCallback;

	/**
	 * @var bool whether or not to enable client validation in backend forms
	 */
	public $backendEnableClientValidation = false;

	/**
	 * @var bool whether or not to enable ajax validation in backend forms
	 */
	public $backendEnableAjaxValidation = false;

	/**
	 * @var array configuration for the access control of the article controller.
	 * If set, the config will be added to the behaviors of the controller.
	 */
	public $backendArticleAccessControl = [
		'class'=>'\yii\filters\AccessControl',
		'rules'=>[
			[
				'allow'=>true,
				'roles'=>['@'],
			],
		],
	];

	/**
	 * @var array configuration for the access control of the article category controller.
	 * If set, the config will be added to the behaviors of the controller.
	 */
	public $backendArticleCategoryAccessControl = [
		'class'=>'\yii\filters\AccessControl',
		'rules'=>[
			[
				'allow'=>true,
				'roles'=>['@'],
			],
		],
	];

	/**
	 * @inheritdoc
	 */
	public function __construct($id, $parent=null, $config=[])
	{
		//load the default config for the module
		$localDefaultConfig = require(__DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');
		$config = ArrayHelper::merge($localDefaultConfig, $config);

		parent::__construct($id, $parent, $config);
	}

	/**
	 * @inheritdoc
	 */
	public function bootstrap($app)
	{
		//no action needed during bootstrapping
	}

	/**
	 * This method will be called to create an editor input, when no custom
	 * callback is set in the module config
	 *
	 * @param \yii\bootstrap\ActiveForm $form the form instance
	 * @param \asinfotrack\yii2\article\models\Article $model the article model instance
	 * @param string $attribute name of the attribute
	 * @param \asinfotrack\yii2\article\Module $module the module instance
	 * @param \yii\web\View $view the active view
	 * @return string the resulting form code for the input
	 */
	public static function defaultEditorInput($form, $model, $attribute, $module, $view)
	{
		return $form->field($model, $attribute)->textarea(['rows'=>5]);
	}

	/**
	 * This method will be called to create a date picker input, when no custom
	 * callback is set in the module config
	 *
	 * @param \yii\bootstrap\ActiveForm $form the form instance
	 * @param \asinfotrack\yii2\article\models\Article $model the article model instance
	 * @param string $attribute name of the attribute
	 * @param \asinfotrack\yii2\article\Module $module the module instance
	 * @param \yii\web\View $view the active view
	 * @return string the resulting form code for the input
	 */
	public static function defaultDateInput($form, $model, $attribute, $module, $view)
	{
		return $form->field($model, $attribute)->widget(DatePicker::className(), []);
	}

}
