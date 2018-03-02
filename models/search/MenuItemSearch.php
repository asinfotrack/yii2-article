<?php
namespace asinfotrack\yii2\article\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use asinfotrack\yii2\article\Module;

/**
 * Search model for menu item models
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class MenuItemSearch extends \asinfotrack\yii2\article\models\MenuItem
{

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['id','tree','lft','rgt','depth','type','is_new_tab','article_id','created','created_by','updated','updated_by'], 'integer'],
			[['label','route','params'], 'safe'],
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
	 * @param bool $showItems whether or not to show actual menu items (non-roots)
	 * @param bool $showRoots whether or not to show menus (roots)
	 * @return \yii\data\ActiveDataProvider the prepared data provider
	 */
	public function search($params, $showItems=true, $showRoots=false)
	{
		/* @var $query \asinfotrack\yii2\article\models\query\MenuItemQuery|\creocoder\nestedsets\NestedSetsQueryBehavior */
		$query = call_user_func([Module::getInstance()->classMap['menuItemSearchModel'], 'find']);
		if (!$showRoots) $query->andWhere(['!=', 'menu_item.depth', 0]);
		if (!$showItems) $query->andWhere(['menu_item.depth'=>0]);

		$dataProvider = new ActiveDataProvider([
			'query'=>$query,
			'sort'=>[
				'defaultOrder'=>[
					'tree'=>SORT_ASC,
					'lft'=>SORT_ASC,
					'label'=>SORT_ASC,
				],
			],
		]);

		$this->detachBehavior('slug');

		if ($this->load($params) && $this->validate()) {
			$query->andFilterWhere([
				'menu_item.id'=>$this->id,
				'menu_item.tree'=>$this->tree,
				'menu_item.lft'=>$this->lft,
				'menu_item.rgt'=>$this->rgt,
				'menu_item.depth'=>$this->depth,
				'menu_item.type'=>$this->type,
				'menu_item.is_new_tab'=>$this->is_new_tab,
				'menu_item.created'=>$this->created,
				'menu_item.created_by'=>$this->created_by,
				'menu_item.updated'=>$this->updated,
				'menu_item.updated_by'=>$this->updated_by,
			]);

			$query
				->andFilterWhere(['like', 'menu_item.label', $this->label])
				->andFilterWhere(['like', 'menu_item.route', $this->route])
				->andFilterWhere(['like', 'menu_item.params', $this->params]);
		}

		return $dataProvider;
	}

}
