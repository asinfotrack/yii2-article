<?php
namespace asinfotrack\yii2\article\models;

use Yii;
use yii\base\InvalidCallException;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use creocoder\nestedsets\NestedSetsBehavior;
use asinfotrack\yii2\article\Module;
use asinfotrack\yii2\article\models\query\ArticleCategoryQuery;
use yii\helpers\Inflector;

/**
 * This is the model class for table "article_category"
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 *
 * @property integer $id
 * @property integer $lft
 * @property integer $rgt
 * @property integer $depth
 * @property string $canonical
 * @property string $title_internal
 * @property string $title
 * @property string $title_head
 * @property integer $created
 * @property integer $created_by
 * @property integer $updated
 * @property integer $updated_by
 *
 * @property integer $parentId
 * @property bool $isFirstSibling
 * @property bool $isLastSibling
 * @property string $treeLabel
 *
 * @property \asinfotrack\yii2\article\models\Article[] $articles
 */
class ArticleCategory extends \yii\db\ActiveRecord
{

	/**
	 * @var integer the parent category id during form handling
	 */
	protected $parentId;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%article_category}}';
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'tree'=>[
				'class'=>NestedSetsBehavior::className(),
				'leftAttribute'=>'lft',
				'rightAttribute'=>'rgt',
				'depthAttribute'=>'depth',
			],
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
			'slug'=>[
				'class'=>SluggableBehavior::className(),
				'slugAttribute'=>'canonical',
				'ensureUnique'=>true,
				'immutable'=>true,
				'value'=>function ($event) {
					if (is_callable(Module::getInstance()->slugValueCallback)) {
						return call_user_func(Module::getInstance()->slugValueCallback, $event->sender);
					}
					return Inflector::slug($this->title);
				},
				'uniqueSlugGenerator'=>function ($baseSlug, $iteration, $model) {
					if (is_callable(Module::getInstance()->uniqueSlugGeneratorCallback)) {
						return call_user_func(Module::getInstance()->uniqueSlugGeneratorCallback, $baseSlug, $iteration, $model);
					}
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
			[['title_internal','title','title_head'], 'trim'],
			[['parentId','canonical','title_internal','title','title_head'], 'default'],

			[['canonical','title'], 'required'],

			[['title_head'], 'string', 'max'=>70],
			[['title_internal','title'], 'string', 'max'=>255],

			[['canonical'], 'unique'],

			[['parentId'], 'exist', 'targetClass'=>ArticleCategory::className(), 'targetAttribute'=>'id'],
			[['parentId'], function ($attribute, $params, $validator) {
				if ($this->isNewRecord || empty($this->{$attribute})) return;

				$ownId = (int) $this->id;
				$parentId = (int) $this->parentId;

				if ($ownId === $parentId) {
					$msg = Yii::t('app', 'A category can not be assigned to itself.');
					$this->addError($attribute, $msg);
				}
				if (call_user_func([Module::getInstance()->classMap['articleCategoryModel'], 'findOne'], $parentId)->isChildOf($this)) {
					$msg = Yii::t('app', 'You can not assign a category to one if its child-categories');
					$this->addError($attribute, $msg);
				}
			}],
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
			'title_internal'=>Yii::t('app', 'Internal title'),
			'title'=>Yii::t('app', 'Title'),
			'title_head'=>Yii::t('app', 'Title used in HTML-Head'),
			'created'=>Yii::t('app', 'Created at'),
			'created_by'=>Yii::t('app', 'Created by'),
			'updated'=>Yii::t('app', 'Updated at'),
			'updated_by'=>Yii::t('app', 'Updated by'),

			'parentId'=>Yii::t('app', 'Parent category'),
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeHints()
	{
		return [
			'canonical'=>Yii::t('app', 'Automatically generated string which is used to identify the article category uniquely'),
			'title_internal'=>Yii::t('app', 'Internal title for the CMS. This title is not visible on the website and purely for organisational purposes. Defaults to regular title, if not set.'),
			'title'=>Yii::t('app', 'The title of the article category as shown within the rendered website'),
			'title_head'=>Yii::t('app', 'Optional override to the main title which will be used in the head of the HTML and therefore in the Tab-Header of the browser'),
		];
	}

	/**
	 * @inheritdoc
	 */
	public function transactions()
	{
		//use transactions for all ops as needed by nested set
		return [self::SCENARIO_DEFAULT=>self::OP_ALL];
	}

	/**
	 * Returns an active query for the article category class
	 *
	 * @return \asinfotrack\yii2\article\models\query\ArticleCategoryQuery|\creocoder\nestedsets\NestedSetsQueryBehavior the active query
	 */
	public static function find()
	{
		return new ArticleCategoryQuery(get_called_class());
	}

	/**
	 * Finds an article category model by a condition. If the condition is a non-numerical string,
	 * it will be matched against the article categories canonical automatically.
	 *
	 * @param string|integer|array $condition either the id or the canonical of the article category
	 * or a regular condition
	 * @return \asinfotrack\yii2\article\models\ArticleCategory|null|\yii\db\ActiveRecord|\creocoder\nestedsets\NestedSetsBehavior either
	 * an article or null
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
	public function beforeSave($insert)
	{
		if (!parent::beforeSave($insert)) {
			return false;
		}

		if (empty($this->title_internal))  $this->title_internal = $this->title;
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function beforeDelete()
	{
		if (!parent::beforeDelete()) {
			return false;
		}

		//prevent deletion of the root category
		if ($this->depth === 0) {
			$msg = Yii::t('app', 'You can not delete the root category');
			throw new InvalidCallException($msg);
		}

		return true;
	}

	/**
	 * Magic getter to fetch the id of the parent category
	 *
	 * @return int|null either the parent categories id or null
	 */
	public function getParentId()
	{
		/* @var $this \creocoder\nestedsets\NestedSetsBehavior */
		if ($this->parentId === null && $this->depth > 0) {
			$this->parentId = $this->parents(1)->select('id')->scalar();
		}

		return $this->parentId;
	}

	/**
	 * Sets the parent category id
	 *
	 * @param int|null $parentId the parent id to set
	 */
	public function setParentId($parentId)
	{
		$this->parentId = $parentId;
	}

	/**
	 * Whether or not this node is the first sibling of its level within the tree
	 *
	 * @return bool true if it is the first sibling
	 */
	public function getIsFirstSibling()
	{
		/* @var $this \creocoder\nestedsets\NestedSetsBehavior */
		return !$this->prev()->exists();
	}

	/**
	 * Whether or not this node is the last sibling of its level within the tree
	 *
	 * @return bool true if it is the last sibling
	 */
	public function getIsLastSibling()
	{
		/* @var $this \creocoder\nestedsets\NestedSetsBehavior */
		return !$this->next()->exists();
	}

	/**
	 * Creates a label reflecting the tree depth of the node.
	 *
	 * @return string the title of the category together with the tree level prefix
	 */
	public function getTreeLabel()
	{
		$ret = $this->title;
		if ($this->depth > 1) {
			$prefix = Module::getInstance()->params['treeLevelPrefix'];
			$ret = sprintf('%s %s', str_repeat($prefix, $this->depth - 1), $ret);
		}
		return $ret;
	}

	/**
	 * Relation to the assigned articles
	 *
	 * @return \yii\db\ActiveQuery the active query of the relation
	 */
	public function getArticles()
	{
		return $this
			->hasMany(Article::className(), ['id'=>'article_id'])
			->viaTable('{{%article_article_category}}', ['article_category_id'=>'id']);
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

}
