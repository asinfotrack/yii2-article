<?php
namespace asinfotrack\yii2\article\components;

use asinfotrack\yii2\article\models\Article;
use asinfotrack\yii2\article\models\ArticleCategory;
use yii\helpers\Json;
use yii\jui\Menu;
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
	 * @var string holds the target route which handles article category links
	 */
	public $targetArticleCategoryRoute;

	/**
	 * @var string the param name of the action which holds the id or the canonical of
	 * the article category to render
	 */
	public $targetArticleCategoryRouteParam = 'id';


	/**
	 * @inheritdoc
	 */
	public function parseRequest($manager, $request)
	{
		/* @var $menuItem \asinfotrack\yii2\article\models\MenuItem */

		//try to find a menu item with the current path info
		$menuItem = MenuItem::find()
			->types([MenuItem::TYPE_ARTICLE, MenuItem::TYPE_ROUTE, MenuItem::TYPE_ARTICLE_CATEGORY])
			->pathInfo($request->pathInfo)
			->one();



		if ($menuItem === null) {
			// try find a category
			// split auf slash & verbinden

			$pathInfoParts = explode('/', $request->pathInfo);
			$found = false;
			$indexFound = 0;
			for ($i = 1; $i <= count($pathInfoParts) && $found === false; ++$i){
				$partialPathInfo = implode('/',array_slice($pathInfoParts,0,$i,true));
				$menuItem = MenuItem::find()
					->types([MenuItem::TYPE_ARTICLE_CATEGORY])
					->pathInfo($partialPathInfo)
					->one();
				$found = $menuItem !== null;
				$indexFound = $i;
			}
			// now we know, we're in an article category
			if ($found) {
				$rest = array_slice($pathInfoParts, $indexFound);
				$end = false;
				while(count($rest) > 0 && !$end) {
					$temp = ArticleCategory::findOne(['canonical' => $rest[0]]);
					$end = $temp === null;
					if (!$end) {
						$articleCategory = $temp;
						array_shift($rest);
						$route = [$this->targetArticleCategoryRoute, [$this->targetArticleCategoryRouteParam=>$articleCategory->id]];
					}
				}
				// if there is no article referenced
				if (count($rest) === 0) {
					return $route;
				}

				// after a category, one step deeper can only be one article
				if (count($rest) > 1)
				{
					return false;
				}
				$article = Article::findOne(['canonical'=>$rest[0]]);
				return [$this->targetArticleRoute, [$this->targetArticleRouteParam=>$article->id]];

			}
		}
		//no match means request is not handled by this rule
		if ($menuItem === null) return false;
		//handle the request with the proper route
		if ($menuItem->type === MenuItem::TYPE_ARTICLE) {
			$route = [$this->targetArticleRoute, [$this->targetArticleRouteParam=>$menuItem->article_id]];
		} else if ($menuItem->type === MenuItem::TYPE_ARTICLE_CATEGORY) {
			$route = [$this->targetArticleCategoryRoute, [$this->targetArticleCategoryRouteParam=>$menuItem->article_category_id]];
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

		// try matching article category menu item
		if ($route === $this->targetArticleCategoryRoute && isset($params[$this->targetArticleCategoryRouteParam])) {
			$menuItem = MenuItem::find()->types(MenuItem::TYPE_ARTICLE_CATEGORY)->articleCategory($params[$this->targetArticleCategoryRouteParam])->one();
			if ($menuItem !== null) return $menuItem->path_info;
		}

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
