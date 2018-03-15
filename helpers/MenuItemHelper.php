<?php
namespace asinfotrack\yii2\article\helpers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Json;
use asinfotrack\yii2\article\Module;
use asinfotrack\yii2\article\models\MenuItem;

/**
 * Helper method to work with the menu items and the nav widgets
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class MenuItemHelper
{

	protected $itemModels = [];

	/**
	 * Converts the db models into menu items as used by the yii2-widgets.
	 *
	 * @param \asinfotrack\yii2\article\models\MenuItem|\creocoder\nestedsets\NestedSetsBehavior $menuItem the menu item
	 * @return array the prepared array
	 */
	public static function getMenuItemsForNav(MenuItem $menuItem) : array
	{
		//fetch data
		$allItems = $menuItem->children()->all();

		//perform work
		$targetArr = [];
		$topLevelItems = static::getChildrenForItem($menuItem, $allItems);
		foreach ($topLevelItems as $item) {
			static::prepareItem($item, $targetArr, $allItems);
		}
		return $targetArr;
	}

	/**
	 * Takes a menu item model and creates an array as needed by teh menu widgets from it
	 *
	 * @param \asinfotrack\yii2\article\models\MenuItem $model the menu item model
	 * @param array $targetArr the target array to add it to
	 * @param \asinfotrack\yii2\article\models\MenuItem[] $allItems an array containing all menu item models
	 */
	protected static function prepareItem(MenuItem $model, array &$targetArr, array &$allItems) : void
	{
		//prepare item array
		$itemArr = ['label'=>$model->label, 'icon'=>$model->icon, 'visible'=>true];
		switch ($model->type) {
			case MenuItem::TYPE_ROUTE:
				$finalRoute = ArrayHelper::merge([$model->route], empty($model->route_params) ? [] : Json::decode($model->route_params));
				$itemArr['url'] = Url::to($finalRoute);
				break;
			case MenuItem::TYPE_URL:
				$itemArr['url'] = $model->url;
				break;
			case MenuItem::TYPE_ARTICLE:
				$module = Module::getInstance();
				$itemArr['url'] = Url::to([$module->articleMenuItemRoute, $module->articleMenuItemParam=>$model->article_id]);
				break;
			case MenuItem::TYPE_NO_LINK:
				break;
		}

		//check visibility
		if ($model->visible_item_names !== null || $model->visible_callback_method !== null) {
			$itemArr['visible'] = false;

			if ($model->visible_item_names !== null) {
				$rbacItemNames = explode(',', $model->visible_item_names);
				foreach ($rbacItemNames as $rbacItemName) {
					if (Yii::$app->user->can(trim($rbacItemName))) {
						$itemArr['visible'] = true;
						break;
					}
				}
			}

			if ($model->visible_callback_method !== null && !$itemArr['visible']) {
				$itemArr['visible'] = call_user_func([$model->visible_callback_class, $model->visible_callback_method], $model);
			}
		}

		//render children
		$children = static::getChildrenForItem($model, $allItems);
		if (count($children) > 0) {
			$itemArr['items'] = [];
			foreach ($children as $child) {
				static::prepareItem($child, $itemArr['items'], $allItems);
			}
		}

		$targetArr[] = $itemArr;
	}

	/**
	 * Helper method to extract the children of an item from the flat model list
	 *
	 * @param \asinfotrack\yii2\article\models\MenuItem $parentItem the item to fetch the children for
	 * @param \asinfotrack\yii2\article\models\MenuItem[] $allItems an array containing all menu item models
	 * @return \asinfotrack\yii2\article\models\MenuItem[] the direct descendants of the model
	 */
	protected static function getChildrenForItem(MenuItem &$parentItem, array &$allItems) : array
	{
		return array_filter($allItems, function ($menuItem) use (&$parentItem) {
			/* @var $menuItem \asinfotrack\yii2\article\models\MenuItem|\creocoder\nestedsets\NestedSetsBehavior */

			if ($menuItem->depth != $parentItem->depth + 1) return false;
			if ($menuItem->lft <= $parentItem->lft) return false;
			if ($menuItem->rgt >= $parentItem->rgt) return false;

			return true;
		});
	}

}
