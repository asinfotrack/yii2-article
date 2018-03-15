<?php
namespace asinfotrack\yii2\article\components;

use yii\helpers\Json;
use yii\web\UrlRuleInterface;
use asinfotrack\yii2\article\models\MenuItem;

/**
 * Custom url rule to handle article and route menu items
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class MenuItemUrlRule implements UrlRuleInterface
{

	/**
	 * @var string holds the target route which handles article links
	 */
	public $targetArticleRoute;

	/**
	 * @var string the param name of the action which holds the id or the canonical of
	 * the article to render
	 */
	public $targetArticleRouteParam = 'id';

	/**
	 * @inheritdoc
	 */
	public function parseRequest($manager, $request)
	{
		/* @var $menuItem \asinfotrack\yii2\article\models\MenuItem */

		//try to find a menu item with the current path info
		$menuItem = MenuItem::find()
			->types([MenuItem::TYPE_ARTICLE, MenuItem::TYPE_ROUTE])
			->pathInfo($request->pathInfo)
			->one();

		//no match means request is not handled by this rule
		if ($menuItem === null) return false;

		//handle the request with the proper route
		if ($menuItem->type === MenuItem::TYPE_ARTICLE) {
			$route = [$this->targetArticleRoute, [$this->targetArticleRouteParam=>$menuItem->article_id]];
		} else {
			$params = $menuItem->route_params !== null ? Json::decode($menuItem->route_params) : [];
			$route = [$menuItem->route, $params];
		}

		return $route;
	}

	/**
	 * @inheritdoc
	 */
	public function createUrl($manager, $route, $params)
	{
		/* @var $menuItem \asinfotrack\yii2\article\models\MenuItem */

		//try matching an article menu item
		if ($route === $this->targetArticleRoute && isset($params[$this->targetArticleRouteParam])) {
			$menuItem = MenuItem::find()->types(MenuItem::TYPE_ARTICLE)->article($params[$this->targetArticleRouteParam])->one();
			if ($menuItem !== null) return $menuItem->path_info;
		}

		//try matching route items
		$menuItem = MenuItem::find()->types(MenuItem::TYPE_ROUTE)->route($route)->one();
		if ($menuItem !== null) return $menuItem->path_info;

		//could not create url with this rule
		return false;
	}

}
