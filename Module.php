<?php
namespace asinfotrack\yii2\article;

use asinfotrack\yii2\article\components\MenuItemUrlRule;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\jui\DatePicker;
use asinfotrack\yii2\article\models\MenuItem;

/**
 * Main class for the article module
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 *
 * @property \asinfotrack\yii2\article\components\ArticleRenderer $renderer
 */
class Module extends \yii\base\Module implements \yii\base\BootstrapInterface
{

	/**
	 * @var array the route for article rendering when a menu item has an article id
	 * as its target.
	 */
	public $articleMenuItemRoute = 'article/article/render';

	/**
	 * @var string the name of the action param when rendering articles
	 */
	public $articleMenuItemParam = 'id';

	/**
	 * @var array array containing the classes to use for the individual model components.
	 */
	public $classMap = [
		'articleModel'=>'asinfotrack\yii2\article\models\Article',
		'articleSearchModel'=>'asinfotrack\yii2\article\models\search\ArticleSearch',
		'articleCategoryModel'=>'asinfotrack\yii2\article\models\ArticleCategory',
		'articleCategorySearchModel'=>'asinfotrack\yii2\article\models\search\ArticleCategorySearch',
		'menuItemModel'=>'asinfotrack\yii2\article\models\MenuItem',
		'menuItemSearchModel'=>'asinfotrack\yii2\article\models\search\MenuItemSearch',
	];

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
	 * @var bool whether or not to use article types to handle full articles different
	 * from text blocks etc. At the moment this is only used for searching and filtering articles.
	 */
	public $useArticleTypes = true;

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
		'class'=>'yii\filters\AccessControl',
		'rules'=>[
			[
				'allow'=>true,
				'roles'=>['@'],
			],
		],
	];

	/**
	 * @var array array holding the views which will be used for the article backend. The
	 * array is indexed by the action name and the values will be used to get the views. By
	 * default the views of the module will be used.
	 *
	 * To use a local view, use the corresponding view syntax. Usually two slashes are used
	 * to reference your root view path (eg `//my-folder/my-view`).
	 *
	 * See the article backend controller for the variables passed to the corresponding views.
	 * @see \asinfotrack\yii2\article\controllers\ArticleBackendController
	 */
	public $backendArticleViews = [
		'index'=>'index',
		'view'=>'view',
		'create'=>'create',
		'update'=>'update',
	];

	/**
	 * @var array configuration for the access control of the article category controller.
	 * If set, the config will be added to the behaviors of the controller.
	 */
	public $backendArticleCategoryAccessControl = [
		'class'=>'yii\filters\AccessControl',
		'rules'=>[
			[
				'allow'=>true,
				'roles'=>['@'],
			],
		],
	];

	/**
	 * @var array array holding the views which will be used for the article category backend.
	 * The array is indexed by the action name and the values will be used to get the views. By
	 * default the views of the module will be used.
	 *
	 * To use a local view, use the corresponding view syntax. Usually two slashes are used
	 * to reference your root view path (eg `//my-folder/my-view`).
	 *
	 * See the article category backend controller for the variables passed to the corresponding views.
	 * @see \asinfotrack\yii2\article\controllers\ArticleBackendController
	 */
	public $backendArticleCategoryViews = [
		'index'=>'index',
		'view'=>'view',
		'create'=>'create',
		'update'=>'update',
	];

	/**
	 * @var array configuration for the access control of the menu item controller.
	 * If set, the config will be added to the behaviors of the controller.
	 */
	public $backendMenuItemsAccessControl = [
		'class'=>'yii\filters\AccessControl',
		'rules'=>[
			[
				'allow'=>true,
				'roles'=>['@'],
			],
		],
	];

	/**
	 * @var array array holding the views which will be used for the menu item backend.
	 * The array is indexed by the action name and the values will be used to get the views. By
	 * default the views of the module will be used.
	 *
	 * To use a local view, use the corresponding view syntax. Usually two slashes are used
	 * to reference your root view path (eg `//my-folder/my-view`).
	 *
	 * See the menu item backend controller for the variables passed to the corresponding views.
	 * @see \asinfotrack\yii2\article\controllers\MenuItemBackendController
	 */
	public $backendMenuItemViews = [
		'index'=>'index',
		'view'=>'view',
		'create'=>'create',
		'update'=>'update',
	];

	/**
	 * @var callable optional callable to replace the default behavior of the slug generation
	 * for articles and article categories. If set, the callable should have the signature
	 * `function ($sender)` and return the slugged string.
	 *
	 * @see \yii\behaviors\SluggableBehavior::$value
	 */
	public $slugValueCallback;

	/**
	 * @var callable optional callable to replace the default behavior of the unique slug behavior
	 * for articles and article categories. By default the static method of the model class will be used.
	 *
	 * If defined, the callable should have the signature `function ($baseSlug, $iteration, $model)` and
	 * return the unique slug.
	 *
	 * @see \yii\behaviors\SluggableBehavior::$uniqueSlugGenerator
	 */
	public $uniqueSlugGeneratorCallback;

	/**
	 * @var string the alias which defines the path where the attachments of the articles will be saved.
	 * If the folder does not exist, it will be created.
	 */
	public $attachmentAlias = '@runtime/article_attachments';

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
		//register menu item rules referring to an article, if this is a web application
		if (Yii::$app instanceof \yii\web\Application) {
			$this->registerUrlRules();
		}
	}

	/**
	 * Registers the url rules as needed by the article menu entries
	 */
	protected function registerUrlRules()
	{
		$rule = new MenuItemUrlRule();
		$rule->targetArticleRoute = $this->articleMenuItemRoute;
		$rule->targetArticleRouteParam = $this->articleMenuItemParam;

		Yii::$app->urlManager->addRules([$rule], false);
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
