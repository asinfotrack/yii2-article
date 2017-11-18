<?php
namespace asinfotrack\yii2\article\models\query;

use yii\db\Query;
use asinfotrack\yii2\article\models\ArticleCategory;

/**
 * The query class for articles providing the most common named scopes.
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class ArticleQuery extends \yii\db\ActiveQuery
{

	/**
	 * @inheritdoc
	 */
	public function prepare($builder)
	{
		//default ordering if none is set
		if (empty($this->orderBy)) $this->orderTitle();

		return parent::prepare($builder);
	}

	/**
	 * Named scope for ordering the article by their title
	 *
	 * @return \asinfotrack\yii2\article\models\query\ArticleQuery $this self for chaining
	 */
	public function orderTitle()
	{
		$this->addOrderBy(['article.title'=>SORT_ASC]);
		return $this;
	}

	/**
	 * Orders the articles according to their published date
	 *
	 * @param bool $newestFirst if true, the newest will be on top, otherwise the oldest ones
	 * @return \asinfotrack\yii2\article\models\query\ArticleQuery $this self for chaining
	 */
	public function orderPublishedAt($newestFirst=true)
	{
		$this->addOrderBy([
			'article.published_at'=>$newestFirst ? SORT_DESC : SORT_ASC,
			'article.id'=>$newestFirst ? SORT_DESC : SORT_ASC,
		]);
		return $this;
	}

	/**
	 * Named scope to filter articles by their assigned categories.
	 * The param can be specified with a single id or category model or a mixed array of the two
	 * types.
	 *
	 * @param integer|integer[]|\asinfotrack\yii2\article\models\ArticleCategory|\asinfotrack\yii2\article\models\ArticleCategory[] $categories the categories
	 * @return \asinfotrack\yii2\article\models\query\ArticleQuery $this self for chaining
	 */
	public function articleCategories($categories)
	{
		//assert array of ids
		if (!is_array($categories)) $categories = [$categories];
		foreach ($categories as &$category) {
			$category = $category instanceof ArticleCategory ? $category->id : $category;
		}

		//create and apply sub query
		$sub = (new Query())
			->select(['article_article_category.article_id'])
			->from('{{%article_article_category}}')
			->where(['article_article_category.article_category_id'=>$categories]);
		$this->andWhere(['article.id'=>$sub]);

		return $this;
	}

	/**
	 * Named scope to filter either published or unpublished articles.
	 *
	 * @param boolean $isPublished if true only published articles, otherwise only unpublished
	 * @return \asinfotrack\yii2\article\models\query\ArticleQuery $this self for chaining
	 */
	public function published($isPublished)
	{
		if ($isPublished) {
			$this->andWhere(['OR', ['article.published_from'=>null], ['<=', 'article.published_from', time()]]);
			$this->andWhere(['OR', ['article.published_to'=>null], ['>=', 'article.published_to', time()]]);
		} else {
			$this->andWhere(['OR', ['article.published_from'=>null], ['>', 'article.published_from', time()]]);
			$this->andWhere(['OR', ['article.published_to'=>null], ['<', 'article.published_to', time()]]);
		}

		return $this;
	}

}
