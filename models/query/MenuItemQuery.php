<?php
namespace asinfotrack\yii2\article\models\query;

use asinfotrack\yii2\article\models\Article;
use creocoder\nestedsets\NestedSetsQueryBehavior;

/**
 * The query class for menu items providing the most common named scopes.
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class MenuItemQuery extends \yii\db\ActiveQuery
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
	 * @return \asinfotrack\yii2\article\models\query\MenuItemQuery $this self for chaining
	 */
	public function orderTree()
	{
		$this->addOrderBy(['menu_item.tree'=>SORT_ASC, 'menu_item.lft'=>SORT_ASC]);
		return $this;
	}

	/**
	 * Named scope for ordering the menu items by their label
	 *
	 * @return \asinfotrack\yii2\article\models\query\MenuItemQuery $this self for chaining
	 */
	public function orderLabel()
	{
		$this->addOrderBy(['menu_item.label'=>SORT_ASC]);
		return $this;
	}

	/**
	 * Named scope for filtering menu items with a certain type
	 *
	 * @return \asinfotrack\yii2\article\models\query\MenuItemQuery $this self for chaining
	 */
	public function types($types)
	{
		$this->andWhere(['menu_item.type'=>$types]);
		return $this;
	}

	/**
	 * Named scope for filtering menu items with a certain state
	 *
	 * @return \asinfotrack\yii2\article\models\query\MenuItemQuery $this self for chaining
	 */
	public function states($states)
	{
		$this->andWhere(['menu_item.state'=>$states]);
		return $this;
	}

	/**
	 * Named scope for filtering menu items by their path info
	 *
	 * @return \asinfotrack\yii2\article\models\query\MenuItemQuery $this self for chaining
	 */
	public function pathInfo(string $pathInfo)
	{
		$this->andWhere(['menu_item.path_info'=>$pathInfo]);
		return $this;
	}

	/**
	 * Named scope to filter menu items by their assigned article
	 *
	 * @param int|string|\asinfotrack\yii2\article\models\Article $article the article, its id or canonical
	 * @return \asinfotrack\yii2\article\models\query\MenuItemQuery $this self for chaining
	 */
	public function article($article)
	{
		$id = $article instanceof Article ? $article->id : $article;
		if (!is_numeric($article)) $id = Article::findOne($article)->id;

		$this->andWhere(['menu_item.article_id'=>$id]);
		return $this;
	}

	/**
	 * Named scope for filtering menu items by their target route
	 *
	 * @return \asinfotrack\yii2\article\models\query\MenuItemQuery $this self for chaining
	 */
	public function route($route)
	{
		$this->andWhere(['menu_item.route'=>$route]);
		return $this;
	}

}
