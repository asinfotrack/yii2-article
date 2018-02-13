<?php
namespace asinfotrack\yii2\article\models;

use Yii;
use yii\base\InvalidCallException;
use asinfotrack\yii2\article\models\query\ArticleAttachmentQuery;
use asinfotrack\yii2\article\Module;

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
 * @property string $title
 * @property string $description
 * @property string $filename
 * @property string $mime_type
 * @property integer $file_size
 * @property integer $created
 * @property integer $created_by
 * @property integer $updated
 * @property integer $updated_by
 *
 * @property \asinfotrack\yii2\article\models\Article $article
 * @property \yii\web\IdentityInterface $createdBy
 * @property \yii\web\IdentityInterface $updatedBy
 */
class ArticleAttachment extends \yii\db\ActiveRecord
{

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'article_attachment';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['title','description'], 'trim'],
			[['title','description'], 'default'],

			[['article_id','title'], 'required'],

			[['article_id','order','file_size'], 'integer'],
			[['title','filename','mime_type'], 'string', 'max'=>255],
			[['description'], 'string'],

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
			'title'=>Yii::t('app', 'Title'),
			'description'=>Yii::t('app', 'Description'),
			'filename'=>Yii::t('app', 'Filename'),
			'mime_type'=>Yii::t('app', 'Mime type'),
			'file_size'=>Yii::t('app', 'File size'),
			'created'=>Yii::t('app', 'Created at'),
			'created_by'=>Yii::t('app', 'Created by'),
			'updated'=>Yii::t('app', 'Updated at'),
			'updated_by'=>Yii::t('app', 'Updated by'),
		];
	}

	/**
	 * Returns an active query for the article link class
	 *
	 * @return \asinfotrack\yii2\article\models\query\ArticleAttachmentQuery the active query
	 */
	public static function find()
	{
		return new ArticleAttachmentQuery(get_called_class());
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
