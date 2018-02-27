<?php
namespace asinfotrack\yii2\article\models;

use Yii;
use yii\base\InvalidCallException;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use asinfotrack\yii2\article\Module;
use asinfotrack\yii2\article\models\query\ArticleLinkQuery;

/**
 * This is the model class for table "article"
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 *
 * @property integer $id
 * @property integer $article_id
 * @property integer $order
 * @property boolean $is_new_tab
 * @property string $url
 * @property string $title
 * @property string $description
 * @property integer $created
 * @property integer $created_by
 * @property integer $updated
 * @property integer $updated_by
 *
 * @property \asinfotrack\yii2\article\models\Article $article
 * @property \yii\web\IdentityInterface $createdBy
 * @property \yii\web\IdentityInterface $updatedBy
 */
class ArticleLink extends \yii\db\ActiveRecord
{

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'article_link';
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
		];
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['url','title','description'], 'trim'],
			[['url','title','description'], 'default'],

			[['article_id','url','title'], 'required'],

			[['article_id','order'], 'integer'],
			[['is_new_tab'], 'boolean'],
			[['url','title'], 'string', 'max'=>255],
			[['description'], 'string'],

			[['url'], 'url'],

			[['article_id'], 'exist', 'targetClass'=>Article::className(), 'targetAttribute'=>'id'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id'=>Yii::t('app', 'ID'),
			'article_id'=>Yii::t('app', 'Article'),
			'order'=>Yii::t('app', 'Order'),
			'is_new_tab'=>Yii::t('app', 'New Tab'),
			'url'=>Yii::t('app', 'URL'),
			'title'=>Yii::t('app', 'Title'),
			'description'=>Yii::t('app', 'Description'),
			'created'=>Yii::t('app', 'Created at'),
			'created_by'=>Yii::t('app', 'Created by'),
			'updated'=>Yii::t('app', 'Updated at'),
			'updated_by'=>Yii::t('app', 'Updated by'),
		];
	}

	/**
	 * Returns an active query for the article link class
	 *
	 * @return \asinfotrack\yii2\article\models\query\ArticleLinkQuery the active query
	 */
	public static function find()
	{
		return new ArticleLinkQuery(get_called_class());
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

}
