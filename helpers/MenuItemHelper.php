<?php
namespace asinfotrack\yii2\article\helpers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
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

	protected static $ITEM_CACHE;

	/**
	 * Converts the db models into menu items as used by the yii2-widgets.
	 *
	 * @param \asinfotrack\yii2\article\models\MenuItem|\creocoder\nestedsets\NestedSetsBehavior $menuItem the menu item
	 * @return array the prepared array
	 */
	public static function getMenuItemsForNav(MenuItem $menuItem) : array
	{
		//assert items are cached
		static::cacheMenuItems();

		//perform work
		$targetArr = [];
		$items = static::getChildrenForItem($menuItem);
		foreach ($items as $item) {
			static::prepareItem($item, $targetArr,$items);
		}
		return $targetArr;
	}

	/**
	 * Performs the activation of menu items according to a callback.
	 * The callback should have the signature `function ($item, $parentItems)` and return a boolean value
	 * whether or not the current item is active. The structure of `$item` is the same as the one used in the nav widget.
	 *
	 * @see \yii\widgets\Menu::$items
	 *
	 * @param array $items the item array to activate
	 * @param callable $isActiveCallback the callback to determine if an item is active
	 * @param bool $activateParents whether or not to activate parent items
	 * @param array $parentItems the list of parent items of the current item (index 0 is the outmost, while the
	 * highest index is the actual parent of the current item)
	 */
	public static function activateMenuItems(array &$items, callable $isActiveCallback, bool $activateParents, array $parentItems=[]) : void
	{
		foreach ($items as &$item) {
			$item['active'] = call_user_func($isActiveCallback, $item, $parentItems);

			if ($item['active'] && $activateParents && !empty($parentItems)) {
				foreach ($parentItems as &$parentItem) {
					$parentItem['active'] = true;
				}
			}

			if (isset($item['items']) && !empty($item['items'])) {
				static::activateMenuItems($item['items'], $isActiveCallback, $activateParents, array_merge($parentItems, [&$item]));
			}
		}
	}

	/**
	 * Extracts the active paths from a prepared menu items array as used in the yii menus
	 *
	 * @param array $items the items to process
	 * @return array an array of active paths within the items processed
	 */
	public static function activePaths(array &$items) : array
	{
		$activePaths = [];

		//call method on active root elements
		foreach ($items as &$item) {
			if (isset($item['active']) && $item['active']) {
				static::getActivePathsRecursive($item,$activePaths);
			}
		}

		return $activePaths;
	}

	/**
	 * This method is called for each found active item in a recursive way. It will be called again for each
	 * active child found.
	 *
	 * @param array $item the active (!) item to get the path for
	 * @param array $activePaths the target array containing the active paths
	 * @param array $currentPath path of the parent items down to the one currently processed (without current item, only parents)
	 */
	protected static function getActivePathsRecursive(array &$item, array &$activePaths, array $currentPath=[]) : void
	{
		//iterate over children
		$hasActiveChild = false;
		if (isset($item['items'])) {
			foreach ($item['items'] as &$childItem) {
				if (isset($childItem['active']) && $childItem['active']) {
					$hasActiveChild = true;
					static::getActivePathsRecursive($childItem, $activePaths, array_merge($currentPath, [$item]));
				}
			}
		}

		//if last active leave add the path
		if (!$hasActiveChild) {
			$activePaths[] = array_merge($currentPath, [$item]);
		}
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
		$options = ['data'=>['menu-item-id'=>$model->id, 'state'=>$model->state]];
		if ($model->state === MenuItem::STATE_PUBLISHED_HIDDEN) {
			Html::addCssClass($options, 'hidden');
		}
		$itemArr = ['label'=>$model->label, 'icon'=>$model->icon, 'visible'=>true, 'options'=>$options];
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
					$rbacItemName = trim($rbacItemName);
					if ($rbacItemName === '@' && !Yii::$app->user->isGuest) {
						$itemArr['visible'] = true;
						break;
					}
					if ($rbacItemName === '?' && Yii::$app->user->isGuest) {
						$itemArr['visible'] = true;
						break;
					}
					if (Yii::$app->user->can($rbacItemName)) {
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
		$children = static::getChildrenForItem($model);
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
	 * @return \asinfotrack\yii2\article\models\MenuItem[] the direct descendants of the model
	 */
	protected static function getChildrenForItem(MenuItem &$parentItem) : array
	{
		return array_filter(static::$ITEM_CACHE, function ($menuItem) use (&$parentItem) {
			/* @var $menuItem \asinfotrack\yii2\article\models\MenuItem|\creocoder\nestedsets\NestedSetsBehavior */

			if ($menuItem->tree != $parentItem->tree) return false;
			if ($menuItem->depth != $parentItem->depth + 1) return false;
			if ($menuItem->lft <= $parentItem->lft) return false;
			if ($menuItem->rgt >= $parentItem->rgt) return false;

			return true;
		});
	}

	/**
	 * Method which caches the menu items
	 */
	protected static function cacheMenuItems()
	{
		if (static::$ITEM_CACHE !== null) return;
		static::$ITEM_CACHE = MenuItem::find()->orderTree()->all();
	}

}
