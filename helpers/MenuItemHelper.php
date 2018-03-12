<?php
namespace asinfotrack\yii2\article\helpers;

use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Json;
use asinfotrack\yii2\article\models\MenuItem;

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

	protected static function prepareItem(MenuItem $model, array &$targetArr, array &$allItems) : void
	{
		$itemArr = ['label'=>$model->label, 'icon'=>$model->icon];
		switch ($model->type) {
			case MenuItem::TYPE_ROUTE:
				$finalRoute = ArrayHelper::merge([$model->route], empty($model->route_params) ? [] : Json::decode($model->route_params));
				$itemArr['url'] = Url::to($finalRoute);
				break;
			case MenuItem::TYPE_URL:
				$itemArr['url'] = $model->url;
				break;
			case MenuItem::TYPE_ARTICLE:
				$itemArr['url'] = '@web' . preg_replace_callback('/<\w+:\([a-z0-9-]+\|\d+\)>/', function ($matches) {
					$start = strpos($matches[0], '(') + 1;
					$end = strpos($matches[0], '|');
					$canonical = substr($matches[0], $start, $end - $start);
					return $canonical;
				}, $model->url_rule_pattern);
				break;
			case MenuItem::TYPE_NO_LINK:
				$itemArr['url'] = '#';
				break;
		}

		$children = static::getChildrenForItem($model, $allItems);
		if (count($children) > 0) {
			$itemArr['items'] = [];
			foreach ($children as $child) {
				static::prepareItem($child, $itemArr['items'], $allItems);
			}
		}

		$targetArr[] = $itemArr;
	}

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
