<?php
namespace asinfotrack\yii2\article\models;

use asinfotrack\yii2\article\models\query\MenuItemQuery;
use asinfotrack\yii2\article\Module;
use Yii;
use yii\base\InvalidCallException;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use creocoder\nestedsets\NestedSetsBehavior;
use yii\helpers\ArrayHelper;

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
 * @property string $label
 * @property bool $is_new_tab
 * @property integer $article_id
 * @property string $route
 * @property string $params
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
 */
class MenuItem extends \yii\db\ActiveRecord
{

	public const SCENARIO_MENU = 'menu';

	public const TYPE_MENU = 1;
	public const TYPE_ARTICLE = 2;
	public const TYPE_ROUTE = 3;
	public const TYPE_URL = 4;

	public static $ALL_TYPES = [self::TYPE_MENU, self::TYPE_ARTICLE, self::TYPE_ROUTE, self::TYPE_URL];

	/**
	 * @var integer the parent menu item id during form handling
	 */
	protected $parentId;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'menu_item';
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'tree'=>[
				'class'=>NestedSetsBehavior::className(),
				'treeAttribute'=>'tree',
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
		];
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['label'], 'trim'],
			[['label'], 'default'],
			[['type'], 'default', 'value'=>self::TYPE_MENU, 'on'=>self::SCENARIO_MENU],

			[['type','label'], 'required'],
			[['parentId'], 'required', 'on'=>self::SCENARIO_DEFAULT],
			[['route'], 'required', 'when'=>function ($model) { return $model->type === self::TYPE_ROUTE; }],
			[['article_id'], 'required', 'when'=>function ($model) { return $model->type === self::TYPE_ARTICLE; }],

			[['type'], 'in', 'range'=>static::$ALL_TYPES],
			[['label'], 'string', 'max'=>255],
			[['is_new_tab'], 'boolean'],
			[['article_id'], 'integer'],

			[['route'], 'url', 'when'=>function ($model) { return $model->type === self::TYPE_URL; }],
			[['route'], 'match', 'pattern'=>'/^\/?([\w-]+\/?){1,}(\?.*)?$/', 'when'=>function ($model) { return $model->type = self::TYPE_ROUTE; }],
			[['article_id'], 'exist', 'targetClass'=>Article::className(), 'targetAttribute'=>'id'],

			[['parentId'], 'exist', 'targetClass'=>MenuItem::className(), 'targetAttribute'=>'id'],
			[['parentId'], function ($attribute, $params, $validator) {
				if ($this->isNewRecord || empty($this->{$attribute})) return;

				$ownId = (int) $this->id;
				$parentId = (int) $this->parentId;

				if ($ownId === $parentId) {
					$msg = Yii::t('app', 'A category can not be assigned to itself.');
					$this->addError($attribute, $msg);
				}
				if (call_user_func([Module::getInstance()->classMap['articleModel'], 'findOne'], $parentId)->isChildOf($this)) {
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
			'label'=>Yii::t('app', 'Label'),
			'is_new_tab'=>Yii::t('app', 'New tab'),
			'article_id'=>Yii::t('app', 'Article'),
			'route'=>Yii::t('app', 'Route'),
			'params'=>Yii::t('app', 'Route-Params'),
			'created'=>Yii::t('app', 'Created at'),
			'created_by'=>Yii::t('app', 'Created by'),
			'updated'=>Yii::t('app', 'Updated at'),
			'updated_by'=>Yii::t('app', 'Updated by'),

			'parentId'=>Yii::t('app', 'Parent menu item'),
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
		if ($this->scenario === self::SCENARIO_MENU) $this->parentId = null;
		return parent::beforeValidate();
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
		if ($this->depth > 1) {
			$prefix = Module::getInstance()->params['treeLevelPrefix'];
			$ret = sprintf('%s %s', str_repeat($prefix, $this->depth - 1), $ret);
		}
		return $ret;
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getArticle()
	{
		return $this->hasOne(Article::className(), ['id'=>'article_id']);
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
			self::TYPE_MENU=>Yii::t('app', 'Menu'),
			self::TYPE_ARTICLE=>Yii::t('app', 'Article'),
			self::TYPE_ROUTE=>Yii::t('app', 'Internal route'),
			self::TYPE_URL=>Yii::t('app', 'Fixed url'),
		];
	}

}
