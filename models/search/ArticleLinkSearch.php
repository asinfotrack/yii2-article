<?php
namespace asinfotrack\yii2\article\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use asinfotrack\yii2\article\models\ArticleLink;

/**
 * Search model for article link models
 *
 * @author Tom Lutzenberger, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class ArticleLinkSearch extends \asinfotrack\yii2\article\models\ArticleLink
{

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['id','article_id','order','is_new_tab','created','created_by','updated','updated_by'], 'integer'],
			[['title','description','url'], 'safe'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function scenarios()
	{
		return Model::scenarios();
	}

	/**
	 * Search method preparing the data provider
	 *
	 * @param array $params the params as delivered
	 * @return \yii\data\ActiveDataProvider the prepared data provider
	 */
	public function search($params)
	{
		$query = ArticleLink::find();
		$dataProvider = new ActiveDataProvider([
			'query'=>$query,
			'sort'=>['defaultOrder'=>['order'=>SORT_ASC,'title'=>SORT_ASC]],
		]);

		if ($this->load($params) && !$this->validate()) {
			$query->andFilterWhere([
				'article_attachment.id'=>$this->id,
				'article_attachment.article_id'=>$this->article_id,
				'article_attachment.order'=>$this->order,
				'article_attachment.is_new_tab'=>$this->is_new_tab,
				'article_attachment.created'=>$this->created,
				'article_attachment.created_by'=>$this->created_by,
				'article_attachment.updated'=>$this->updated,
				'article_attachment.updated_by'=>$this->updated_by,
			]);

			$query
				->andFilterWhere(['like', 'article_attachment.title', $this->title])
				->andFilterWhere(['like', 'article_attachment.description', $this->description])
				->andFilterWhere(['like', 'article_attachment.url', $this->url])
				;
		}

		return $dataProvider;
	}

}
