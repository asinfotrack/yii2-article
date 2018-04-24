<?php
namespace asinfotrack\yii2\article\models\query;

/**
 * The query class for articles providing the most common named scopes.
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class StateQuery extends \yii\db\ActiveQuery
{

	/**
	 * Named scope for ordering the state by their title
	 *
	 * @return \asinfotrack\yii2\article\models\query\StateQuery $this self for chaining
	 */
	public function orderName()
	{
		$this->addOrderBy(['state.name'=>SORT_ASC]);
		return $this;
	}

}
