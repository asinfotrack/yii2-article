<?php
namespace asinfotrack\yii2\article\models;

use Yii;
use yii\base\InvalidCallException;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\validators\ExistValidator;
use asinfotrack\yii2\article\Module;
use asinfotrack\yii2\article\components\ArticleRenderer;
use asinfotrack\yii2\article\models\query\ArticleQuery;

/**
 * This is the model class for table "article"
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 *
 * @property integer $id
 * @property string $canonical
 * @property integer $type
 * @property string $title
 * @property string $title_head
 * @property string $title_menu
 * @property integer $published_at;
 * @property integer $published_from;
 * @property integer $published_to;
 * @property string $meta_keywords
 * @property string $meta_description
 * @property string $intro
 * @property string $content
 * @property integer $created
 * @property integer $created_by
 * @property integer $updated
 * @property integer $updated_by
 *
 * @property boolean $isPublished
 * @property integer[] $categoryIds
 *
 * @property \asinfotrack\yii2\article\models\ArticleCategory[] $articleCategories
 * @property \yii\web\IdentityInterface $createdBy
 * @property \yii\web\IdentityInterface $updatedBy
 */
class Article extends \yii\db\ActiveRecord
{

	/**
	 * Constant to mark an article with an undefined type
	 */
	const TYPE_UNDEFINED = 1;
	/**
	 * Constant to mark an article as a full page article
	 */
	const TYPE_ARTICLE = 10;
	/**
	 * Constant to mark an article as a text block which is intended for usage in other
	 * articles
	 */
	const TYPE_BLOCK = 20;

	/**
	 * @var integer[] holds all valid types of an article
	 */
	protected static $ALL_TYPES = [ self::TYPE_UNDEFINED, self::TYPE_ARTICLE, self::TYPE_BLOCK ];

	/**
	 * @var integer[] holds the category for assignment and reassignment
	 */
	protected $categoryIds = null;


	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'article';
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'timestamp'=>[
				'class'=>TimestampBehavior::className(),
				'createdAtAttribute'=>'created',
				'updatedAtAttribute'=>'updated',
			],
			'blameable'=>[
				'class'=>BlameableBehavior::className(),
				'createdByAttribute'=>'created_by',
				'updatedByAttribute'=>'updated_by',
			],
			'attachments'=>[
				'class'=>AttachmentBehavior::className(),
			],
			'slug'=>[
				'class'=>SluggableBehavior::className(),
				'slugAttribute'=>'canonical',
				'ensureUnique'=>true,
				'value'=>function ($event) {
					return sprintf('%s', Inflector::slug($this->title));
				},
				'uniqueSlugGenerator'=>function ($baseSlug, $iteration, $model) {
					return sprintf('%s_%d', $baseSlug, $iteration+1);
				},
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['title','title_head','title_menu','meta_keywords','meta_description'], 'trim'],
			[['canonical','title','title_head','title_menu','meta_keywords','meta_description'], 'default'],

			[['canonical','title'], 'required'],

			[['type'], 'in', 'range'=>static::$ALL_TYPES],
			[['title_head'], 'string', 'max'=>70],
			[['title_menu', 'meta_description'], 'string', 'max'=>160],
			[['meta_keywords'], 'string', 'max'=>255],
			[['meta_keywords'], function ($attribute, $params, $validator) {
				if (empty($this->{$attribute})) return;
				$this->{$attribute} = preg_replace('/\s*,\s*/', ',', $this->{$attribute});
				if (count(explode(',', $this->{$attribute})) > 10) {
					$this->addError($attribute, Yii::t('app', 'Do not use more than 10 keywords'));
				}
			}],
			[['intro','content'], 'string'],

			[['published_at'], 'date', 'timestampAttribute'=>'published_at',
				'when'=>function($model) { return !empty($model->published_at) && !is_numeric($model->published_at); },
			],
			[['published_from'], 'date', 'timestampAttribute'=>'published_from',
				'when'=>function($model) { return !empty($model->published_from) && !is_numeric($model->published_from); },
			],
			[['published_to'], 'date', 'timestampAttribute'=>'published_to',
				'when'=>function($model) { return !empty($model->published_to) && !is_numeric($model->published_to); },
			],

			[['categoryIds'], function ($attribute, $params, $validator) {
				$existValidator = new ExistValidator(['targetClass'=>ArticleCategory::className(), 'targetAttribute'=>'id']);
				foreach ($this->{$attribute} as $id) {
					$id = is_int($id) ? $id : intval($id);
					if ($id === 1) {
						$this->addError($attribute, Yii::t('app', 'You can not assign an article to the root-category'));
						return;
					}

					$err = null;
					if (!$existValidator->validate($id, $err)) {
						$this->addError($attribute, $err);
						return;
					}
				}
			}],

			[['canonical'], 'unique'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id'=>Yii::t('app', 'ID'),
			'canonical'=>Yii::t('app', 'Canonical'),
			'type'=>Yii::t('app', 'Type of article'),
			'title'=>Yii::t('app', 'Title'),
			'title_head'=>Yii::t('app', 'Title used in HTML-Head'),
			'title_menu'=>Yii::t('app', 'Title used in Menus'),
			'published_at'=>Yii::t('app', 'Shown published date'),
			'published_from'=>Yii::t('app', 'Published from'),
			'published_to'=>Yii::t('app', 'Published to'),
			'meta_keywords'=>Yii::t('app', 'Meta keywords'),
			'meta_description'=>Yii::t('app', 'Meta description'),
			'intro'=>Yii::t('app', 'Intro'),
			'content'=>Yii::t('app', 'Article content'),
			'created'=>Yii::t('app', 'Created at'),
			'created_by'=>Yii::t('app', 'Created by'),
			'updated'=>Yii::t('app', 'Updated at'),
			'updated_by'=>Yii::t('app', 'Updated by'),

			'isPublished'=>Yii::t('app', 'Published now'),
			'categoryIds'=>Yii::t('app', 'Categories'),
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeHints()
	{
		return [
			'canonical'=>Yii::t('app', 'Automatically generated string which is used to identify the article uniquely'),
			'title'=>Yii::t('app', 'The title of the article as shown within the rendered article'),
			'title_head'=>Yii::t('app', 'Optional override to the main title which will be used in the head of the HTML and therefore in the Tab-Header of the browser'),
			'title_menu'=>Yii::t('app', 'Optional override to the main title which can be used in menus'),
			'published_at'=>Yii::t('app', 'Publishing date which will be shown in the article'),
			'published_from'=>Yii::t('app', 'The article will be shown no earlier than this date if specified'),
			'published_to'=>Yii::t('app', 'The article will be shown no later than this date if specified'),
			'meta_keywords'=>Yii::t('app', 'The meta keywords which can be rendered in the HTML head'),
			'meta_description'=>Yii::t('app', 'The meta description which can be rendered in the HTML head'),
			'intro'=>Yii::t('app', 'Optional intro text which will be rendered differently'),
		];
	}

	/**
	 * Returns an active query for the article class
	 *
	 * @return \asinfotrack\yii2\article\models\query\ArticleQuery the active query
	 */
	public static function find()
	{
		return new ArticleQuery(get_called_class());
	}

	/**
	 * Finds an article model by a condition. If the condition is a non-numerical string,
	 * it will be matched against the article categories canonical automatically.
	 *
	 * @param string|integer|array $condition either the id or the canonical of the article or a
	 * regular condition
	 * @return \asinfotrack\yii2\article\models\Article|null|\yii\db\ActiveRecord either an article
	 * or null
	 */
	public static function findOne($condition)
	{
		//if no array is specified and value is not numeric, check canonical
		if (!is_array($condition) && !is_numeric($condition)) {
			$condition = [static::tableName() . '.canonical'=>$condition];
		}

		return parent::findOne($condition);
	}

	/**
	 * @inheritdoc
	 */
	public function save($runValidation=true, $attributeNames=null)
	{
		//call the actual save implementation
		if (!parent::save($runValidation, $attributeNames)) {
			return false;
		}

		//remove old relations to categories
		$oldCats = $this->articleCategories;
		$oldCatIds = ArrayHelper::getColumn($oldCats, 'id');
		foreach ($oldCats as $cat) {
			if (!in_array($cat->id, $this->categoryIds)) {
				$this->unlink('courseCategories', $cat);
			}
		}

		//add missing relations
		$query = call_user_func([Module::getInstance()->classMap['articleCategoryModel'], 'find']);
		$newCats = $query->where(['article_category.id'=>$this->categoryIds])->all();
		foreach ($newCats as $cat) {
			if (in_array($cat->id, $oldCatIds)) continue;
			$this->link('articleCategories', $cat);
		}

		return true;
	}

	/**
	 * Shorthand method to render the article using a new instance of the article
	 * helper with the config provided. If no config is provided, the default is used.
	 *
	 * @param array $customRendererConfig an optional custom config for the renderer. If
	 * provided, the default renderer will be cloned and its config updated with the custom
	 * values provided
	 * @return string the rendered content
	 */
	public function render($customRendererConfig=null)
	{
		//get rendering component and assert its validity
		$renderer = Instance::ensure(Module::getInstance()->renderer, ArticleRenderer::className());

		//apply custom config if necessary
		if ($customRendererConfig !== null) {
			$renderer = clone $renderer;
			Yii::configure($renderer, $customRendererConfig);
		}

		//perform the rendering and return the result
		return $renderer->render($this);
	}

	/**
	 * Magic getter to determine if an article is published
	 *
	 * @return bool true if currently published
	 */
	public function getIsPublished()
	{
		$now = time();
		if ($this->published_from !== null && $this->published_from > $now) return false;
		if ($this->published_to !== null && $this->published_to < $now) return false;
		return true;
	}

	/**
	 * Getter for the assigned category ids used in forms
	 *
	 * @return integer[] array containing category ids
	 */
	public function getCategoryIds()
	{
		if ($this->categoryIds === null) {
			$this->categoryIds = ArrayHelper::getColumn($this->articleCategories, 'id');
		}

		return $this->categoryIds;
	}

	/**
	 * Sets the category ids which will assigned during saving
	 *
	 * @param integer[] $categoryIds array containing category ids
	 */
	public function setCategoryIds($categoryIds)
	{
		$this->categoryIds = $categoryIds;
	}

	/**
	 * Relation to the assigned article categories
	 *
	 * @return \yii\db\ActiveQuery the active query of the relation
	 */
	public function getArticleCategories()
	{
		return $this
			->hasMany(ArticleCategory::className(), ['id'=>'article_category_id'])
			->viaTable('{{%article_article_category}}', ['article_id'=>'id']);
	}

	/**
	 * Returns the user who created the instance. This relation only works when
	 * `userRelationCallback` is properly configured within the module config.
	 *
	 * @return \yii\db\ActiveQuery the active query of the relation
	 * @throws \yii\base\InvalidCallException when `userRelationCallback is not properly configured
	 */
	public function getCreatedBy()
	{
		$callback = Module::getInstance()->userRelationCallback;
		if (!is_callable($callback)) {
			$msg = Yii::t('app', 'No or invalid `userRelationCallback` specified in Module config');
			throw new InvalidCallException($msg);
		}

		return call_user_func($callback, $this, 'created_by');
	}

	/**
	 * Returns the user who updated the instance. This relation only works when
	 * `userRelationCallback` is properly configured within the module config.
	 *
	 * @return \yii\db\ActiveQuery the active query of the relation
	 * @throws \yii\base\InvalidCallException when `userRelationCallback is not properly configured
	 */
	public function getUpdatedBy()
	{
		$callback = Module::getInstance()->userRelationCallback;
		if (!is_callable($callback)) {
			$msg = Yii::t('app', 'No or invalid `userRelationCallback` specified in Module config');
			throw new InvalidCallException($msg);
		}

		return call_user_func($callback, $this, 'updated_by');
	}

	/**
	 * Returns the possible types to use in dropdown filters or other selections
	 *
	 * @return array array indexed by type with values representing the labels of each type
	 */
	public static function typeFilter()
	{
		return [
			self::TYPE_UNDEFINED=>Yii::t('app', 'Undefined'),
			self::TYPE_ARTICLE=>Yii::t('app', 'Article'),
			self::TYPE_BLOCK=>Yii::t('app', 'Block'),
		];
	}

}
