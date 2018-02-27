<?php
namespace asinfotrack\yii2\article\models\search;

use asinfotrack\yii2\article\Module;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Search model for article models
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class ArticleSearch extends \asinfotrack\yii2\article\models\Article
{

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['id','type','published_at','published_from','published_to','created','created_by','updated','updated_by'], 'integer'],
			[['canonical','title','title_head','title_menu','meta_keywords','meta_description','intro','content'], 'safe'],
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
		$query = call_user_func([Module::getInstance()->classMap['articleModel'], 'find']);
		$dataProvider = new ActiveDataProvider([
			'query'=>$query,
			'sort'=>['defaultOrder'=>['title'=>SORT_ASC]],
		]);

		if ($this->load($params) && !$this->validate()) {
			$query->andFilterWhere([
				'article.id'=>$this->id,
				'article.type'=>$this->type,
				'article.published_at'=>$this->published_at,
				'article.published_from'=>$this->published_from,
				'article.published_to'=>$this->published_to,
				'article.created'=>$this->created,
				'article.created_by'=>$this->created_by,
				'article.updated'=>$this->updated,
				'article.updated_by'=>$this->updated_by,
			]);

			$query
				->andFilterWhere(['like', 'article.canonical', $this->canonical])
				->andFilterWhere(['like', 'article.title', $this->title])
				->andFilterWhere(['like', 'article.title_head', $this->title_head])
				->andFilterWhere(['like', 'article.title_menu', $this->title_menu])
				->andFilterWhere(['like', 'article.meta_keywords', $this->meta_keywords])
				->andFilterWhere(['like', 'article.meta_description', $this->meta_description])
				->andFilterWhere(['like', 'article.intro', $this->intro])
				->andFilterWhere(['like', 'article.content', $this->content]);
		}

		return $dataProvider;
	}

}
