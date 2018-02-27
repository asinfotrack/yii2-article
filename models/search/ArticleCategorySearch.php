<?php
namespace asinfotrack\yii2\article\models\search;

use asinfotrack\yii2\article\Module;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use asinfotrack\yii2\article\models\ArticleCategory;

/**
 * Search model for article category models
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class ArticleCategorySearch extends \asinfotrack\yii2\article\models\ArticleCategory
{

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['id','created','created_by','updated','updated_by'], 'integer'],
			[['canonical','title','title_head','title_menu'], 'safe'],
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
		$query = call_user_func([Module::getInstance()->classMap['articleCategoryModel'], 'find'])->excludeRoot();
		$dataProvider = new ActiveDataProvider([
			'query'=>$query,
			'sort'=>[
				'defaultOrder'=>[
					'lft'=>SORT_ASC,
					'title'=>SORT_ASC,
				],
			],
		]);

		if ($this->load($params) && !$this->validate()) {
			$query->andFilterWhere([
				'article_category.id'=>$this->id,
				'article_category.created'=>$this->created,
				'article_category.created_by'=>$this->created_by,
				'article_category.updated'=>$this->updated,
				'article_category.updated_by'=>$this->updated_by,
			]);

			$query
				->andFilterWhere(['like', 'article_category.canonical', $this->canonical])
				->andFilterWhere(['like', 'article_category.title', $this->title])
				->andFilterWhere(['like', 'article_category.title_head', $this->title_head])
				->andFilterWhere(['like', 'article_category.title_menu', $this->title_menu]);
		}

		return $dataProvider;
	}

}
