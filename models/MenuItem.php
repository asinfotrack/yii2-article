<?php
namespace asinfotrack\yii2\article\models;

use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidCallException;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use creocoder\nestedsets\NestedSetsBehavior;
use asinfotrack\yii2\article\Module;
use asinfotrack\yii2\article\models\query\MenuItemQuery;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\validators\UniqueValidator;

/**
 * This is the model class for table "article_category"
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 *
 * @property integer $id
 * @property integer $tree
 * @property integer $lft
 * @property integer $rgt
 * @property integer $depth
 * @property integer $type
 * @property integer $state
 * @property string $label
 * @property string $icon
 * @property bool $is_new_tab
 * @property string $path_info
 * @property integer $article_id
 * @property integer $article_category_id
 * @property string $route
 * @property string $route_params
 * @property string $url
 * @property string $visible_item_names
 * @property string $visible_callback_class
 * @property string $visible_callback_method
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
 * @property \asinfotrack\yii2\article\models\Article $article
 * @property \asinfotrack\yii2\article\models\ArticleCategory $articleCategory
 */
class MenuItem extends \yii\db\ActiveRecord
{

	public const SCENARIO_MENU = 'menu';

	public const TYPE_ARTICLE = 1;
	public const TYPE_ROUTE = 2;
	public const TYPE_URL = 3;
	public const TYPE_ARTICLE_CATEGORY = 4;
	public const TYPE_NO_LINK = 10;

	public static $ALL_TYPES = [self::TYPE_ARTICLE, self::TYPE_ROUTE, self::TYPE_URL, self::TYPE_ARTICLE_CATEGORY, self::TYPE_NO_LINK];

	public const STATE_PUBLISHED = 10;
	public const STATE_PUBLISHED_HIDDEN = 20;
	public const STATE_UNPUBLISHED = 50;

	public static $ALL_STATES = [self::STATE_PUBLISHED, self::STATE_PUBLISHED_HIDDEN, self::STATE_UNPUBLISHED];
	public static $RENDERED_STATES = [self::STATE_PUBLISHED, self::STATE_PUBLISHED_HIDDEN];

	/**
	 * @var integer the parent menu item id during form handling
	 */
	protected $parentId;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%menu_item}}';
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'tree'=>[
				'class'=>NestedSetsBehavior::class,
				'treeAttribute'=>'tree',
				'leftAttribute'=>'lft',
				'rightAttribute'=>'rgt',
				'depthAttribute'=>'depth',
			],
			'timestamp'=>[
				'class'=>TimestampBehavior::class,
				'createdAtAttribute'=>'created',
				'updatedAtAttribute'=>'updated',
			],
			'blameable'=>[
				'class'=>BlameableBehavior::class,
				'createdByAttribute'=>'created_by',
				'updatedByAttribute'=>'updated_by',
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['icon','label','path_info','route','route_params','url','visible_item_names','visible_callback_class','visible_callback_method'], 'trim'],
			[['icon','label','path_info','route','route_params','url','visible_item_names','visible_callback_class','visible_callback_method'], 'default'],
			[['path_info'], 'filter', 'filter'=>function($value) { return trim($value, '/'); }],

			[['label'], 'required'],
			[['parentId','type','state'], 'required', 'on'=>self::SCENARIO_DEFAULT],

			[['icon','label','visible_item_names','visible_callback_class','visible_callback_method'], 'string', 'max'=>255],
			[['type'], 'in', 'range'=>static::$ALL_TYPES],
			[['state'], 'in', 'range'=>static::$ALL_STATES],
			[['is_new_tab'], 'boolean'],
			[['visible_item_names'], 'match', 'pattern'=>'/^[\w -_]+(,[\w -_]+)*$/'],

			[['path_info'], 'required', 'when'=>function ($model) {
				return in_array(intval($model->type), [self::TYPE_ARTICLE, self::TYPE_ROUTE, self::TYPE_ARTICLE_CATEGORY]);
			}],
			[['path_info'], function ($attribute, $params) {
				$tree = MenuItem::findOne($this->parentId)->tree;
				$query = MenuItem::find()->andWhere(['menu_item.tree'=>$tree])->pathInfo($this->{$attribute});
				if (!$this->isNewRecord) $query->andWhere(['NOT', ['menu_item.id'=>$this->id]]);

				if ($query->exists()) {
					$msg = Yii::t('app', 'The path info {pi} is already taken within the menu {tree}', [
						'pi'=>$this->{$attribute},
						'tree'=>$tree,
					]);
					$this->addError($attribute, $msg);
				}
			}, 'when'=>function ($model) {
				return in_array(intval($model->type), [self::TYPE_ARTICLE, self::TYPE_ROUTE]);
			}],
			[['path_info'], function ($attribute, $params) {
				if (empty($this->{$attribute})) return;
				$query = MenuItem::find()->pathInfo($this->{$attribute});
				if (!$this->isNewRecord) $query->andWhere(['NOT', ['menu_item.id'=>$this->id]]);
				foreach ($query->all() as $model) {
					/* @var $model \asinfotrack\yii2\article\models\MenuItem */
					if (!static::haveSameConfig($this, $model)) {
						$msg = Yii::t('app', 'Same path infos across menus are allowed, but they need to be configured the same way');
						$this->addError($attribute, $msg);
					}
				}
			}],

			[['article_id'], 'required', 'when'=>function ($model) { return intval($model->type) === self::TYPE_ARTICLE; }],
			[['article_id'], 'integer'],
			[['article_id'], 'exist', 'targetClass'=>Article::class, 'targetAttribute'=>'id'],

			[['article_category_id'], 'required', 'when'=>function ($model) { return intval($model->type) === self::TYPE_ARTICLE_CATEGORY; }],
			[['article_category_id'], 'integer'],
			[['article_category_id'], 'exist', 'targetClass'=>ArticleCategory::class, 'targetAttribute'=>'id'],

			[['route'], 'required', 'when'=>function ($model) { return intval($model->type) === self::TYPE_ROUTE; }],
			[['route'], 'string', 'max'=>255],
			[['route'], 'match', 'pattern'=>'/^([\w-]+\/?){1,}(\?.*)?$/', 'when'=>function ($model) { return intval($model->type) === +self::TYPE_ROUTE; }],
			[['route_params'], 'string'],
			[['route_params'], function ($attribute, $params) {
				if (empty($this->{$attribute})) return;
				try {
					Json::decode($this->{$attribute});
				} catch (\InvalidArgumentException $e) {
					$this->addError($attribute, Yii::t('app', 'Invalid JSON-data provided for route params'));
				}
			}],

			[['url'], 'required', 'when'=>function ($model) { return intval($model->type) === self::TYPE_URL; }],
			[['url'], 'url'],

			[['parentId'], 'exist', 'targetClass'=>MenuItem::class, 'targetAttribute'=>'id'],
			[['parentId'], function ($attribute, $params, $validator) {
				if ($this->isNewRecord || empty($this->{$attribute})) return;

				$ownId = (int) $this->id;
				$parentId = (int) $this->parentId;

				if ($ownId === $parentId) {
					$msg = Yii::t('app', 'A category can not be assigned to itself.');
					$this->addError($attribute, $msg);
				}

				$parentModel = call_user_func([Module::getInstance()->classMap['menuItemModel'], 'findOne'], $parentId);
				if ($parentModel->isChildOf($this)) {
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
			'type'=>Yii::t('app', 'Typ'),
			'state'=>Yii::t('app', 'State'),
			'icon'=>Yii::t('app', 'Icon'),
			'label'=>Yii::t('app', 'Label'),
			'is_new_tab'=>Yii::t('app', 'New tab'),
			'path_info'=>Yii::t('app', 'Path info'),
			'article_id'=>Yii::t('app', 'Article'),
			'article_category_id'=>Yii::t('app', 'Article category'),
			'route'=>Yii::t('app', 'Route'),
			'route_params'=>Yii::t('app', 'Route-Params'),
			'url'=>Yii::t('app', 'URL'),
			'visible_item_names'=>Yii::t('app', 'Visible to roles'),
			'visible_callback_class'=>Yii::t('app', 'Visibility Callback class'),
			'visible_callback_method'=>Yii::t('app', 'Visibility Callback method'),
			'created'=>Yii::t('app', 'Created at'),
			'created_by'=>Yii::t('app', 'Created by'),
			'updated'=>Yii::t('app', 'Updated at'),
			'updated_by'=>Yii::t('app', 'Updated by'),

			'parentId'=>Yii::t('app', 'Parent menu item'),
			'treeLabel'=>Yii::t('app', 'Label'),
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeHints()
	{
		return [
			'icon'=>Yii::t('app', 'Optional icon for the menu entry. You can choose any icon visible under this URL: {url}', [
				'url'=>Html::a('FontAwesome', 'https://fontawesome.com/v4.7.0/icons/', ['target'=>'_blank']),
			]),
		];
	}

	/**
	 * @inheritdoc
	 */
	public function scenarios()
	{
		return ArrayHelper::merge(parent::scenarios(), [
			self::SCENARIO_MENU=>[],
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function transactions()
	{
		//use transactions for all ops as needed by nested set
		return [
			self::SCENARIO_DEFAULT=>self::OP_ALL,
			self::SCENARIO_MENU=>self::OP_ALL,
		];
	}

	/**
	 * Returns an active query for the menu item class
	 *
	 * @return \asinfotrack\yii2\article\models\query\MenuItemQuery|\creocoder\nestedsets\NestedSetsQueryBehavior the active query
	 */
	public static function find()
	{
		return new MenuItemQuery(get_called_class());
	}

	/**
	 * @inheritdoc
	 */
	public function afterFind()
	{
		if ($this->depth === 0) $this->scenario = self::SCENARIO_MENU;
		parent::afterFind();
	}

	/**
	 * @inheritdoc
	 */
	public function beforeValidate()
	{
		if ($this->scenario === self::SCENARIO_MENU) {
			$this->parentId = null;
			$this->type = null;
			$this->state = self::STATE_PUBLISHED;
		}
		return parent::beforeValidate();
	}

	/**
	 * @inheritdoc
	 */
	public function beforeSave($insert)
	{
		if (!parent::beforeSave($insert)) {
			return false;
		}

		$type = intval($this->type);
		if (!in_array($type, [self::TYPE_ARTICLE, self::TYPE_ROUTE, self::TYPE_ARTICLE_CATEGORY])) $this->path_info = null;
		if ($type !== self::TYPE_ARTICLE) $this->article_id = null;
		if ($type !== self::TYPE_ARTICLE_CATEGORY) $this->article_category_id = null;
		if ($type !== self::TYPE_ROUTE) {
			$this->route = null;
			$this->route_params = null;
		}
		if ($type !== self::TYPE_URL) $this->url = null;

		return true;
	}

	/**
	 * Magic getter to fetch the id of the parent item
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
	 * Sets the parent menu item id
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
	 * @return string the label of the category together with the tree level prefix
	 */
	public function getTreeLabel()
	{
		$ret = $this->label;
		$prefix = Module::getInstance()->params['treeLevelPrefix'];
		$ret = sprintf('%s %s', str_repeat($prefix, $this->depth), $ret);
		return $ret;
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getArticle()
	{
		return $this->hasOne(Article::class, ['id'=>'article_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getArticleCategory()
	{
		return $this->hasOne(ArticleCategory::class, ['id'=>'article_category_id']);
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
	 * Returns a filter for the type field as used by grid views
	 *
	 * @return array array containing the types as keys and the labels as values
	 */
	public static function typeFilter()
	{
		return [
			self::TYPE_ARTICLE=>Yii::t('app', 'Article'),
			self::TYPE_ROUTE=>Yii::t('app', 'Internal route'),
			self::TYPE_URL=>Yii::t('app', 'Fixed url'),
			self::TYPE_ARTICLE_CATEGORY=>Yii::t('app', 'Article category'),
			self::TYPE_NO_LINK=>Yii::t('app', 'No link / Label'),
		];
	}

	/**
	 * Returns a filter for the state field as used by grid views
	 *
	 * @return array array containing the types as keys and the labels as values
	 */
	public static function stateFilter()
	{
		return [
			self::STATE_PUBLISHED=>Yii::t('app', 'Published (visible)'),
			self::STATE_PUBLISHED_HIDDEN=>Yii::t('app', 'Published (invisible)'),
			self::STATE_UNPUBLISHED=>Yii::t('app', 'Unpublished / archived'),
		];
	}

	/**
	 * Compares the menu item configuration of two items and returns if they are the same
	 *
	 * @param \asinfotrack\yii2\article\models\MenuItem $itemA first item
	 * @param \asinfotrack\yii2\article\models\MenuItem $itemB second item
	 * @return bool true if the same
	 */
	protected static function haveSameConfig(MenuItem $itemA, MenuItem $itemB) : bool
	{
		if ($itemA->type !== $itemB->type) {
			return false;
		}
		if ($itemA->article_id !== $itemB->article_id) {
			return false;
		}
		if ($itemA->article_category_id !== $itemB->article_category_id) {
			return false;
		}
		if ($itemA->route !== $itemB->route) {
			return false;
		}
		if ($itemA->route_params !== $itemB->route_params) {
			return false;
		}
		if ($itemA->url !== $itemB->url) {
			return false;
		}

		return true;
	}

}
