<?php
namespace asinfotrack\yii2\article\models\query;

use creocoder\nestedsets\NestedSetsQueryBehavior;

/**
 * The query class for article categories providing the most common
 * named scopes.
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class ArticleCategoryQuery extends \yii\db\ActiveQuery
{

	/**
	 * @inheritdoc
	 */
	public function behaviors() {
		return [NestedSetsQueryBehavior::className()];
	}

	/**
	 * @inheritdoc
	 */
	public function prepare($builder)
	{
		//default ordering if none is set
		if (empty($this->orderBy)) $this->orderTree();

		return parent::prepare($builder);
	}

	/**
	 * Named scope for ordering the categories according to the tree structure
	 *
	 * @return \asinfotrack\yii2\article\models\query\ArticleCategoryQuery $this self for chaining
	 */
	public function orderTree()
	{
		$this->addOrderBy(['article_category.lft'=>SORT_ASC]);
		return $this;
	}

	/**
	 * Named scope for ordering the categories by their title
	 *
	 * @return \asinfotrack\yii2\article\models\query\ArticleCategoryQuery $this self for chaining
	 */
	public function orderTitle()
	{
		$this->addOrderBy(['article_category.title'=>SORT_ASC]);
		return $this;
	}

	/**
	 * Named scope to exclude the root category, which is only used for sorting the actual
	 * root categories
	 *
	 * @return \asinfotrack\yii2\article\models\query\ArticleCategoryQuery $this self for chaining
	 */
	public function excludeRoot()
	{
		$this->andWhere(['NOT', ['article_category.id'=>1]]);
		return $this;
	}

}
