<?php
namespace asinfotrack\yii2\article\models\query;

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

}
