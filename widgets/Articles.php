<?php
namespace asinfotrack\yii2\article\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\Html;
use asinfotrack\yii2\article\Module;
use asinfotrack\yii2\article\models\Article;

/**
 * This widget is responsible for rendering an article. If a custom article view is
 * set, it will be rendered with the article model provided. If not set, the widget will
 * use the renderer of the article module to create the output automatically.
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class Articles extends \yii\widgets\ListView
{

	/**
	 * @var callable|\asinfotrack\yii2\article\models\query\ArticleQuery|mixed|array define the content to render
	 * via this attribute.
	 *
	 * 1) If a closure/callable with the signature `function ($widget)` is provided, its return value will be
	 * used first to repopulate this attribute. The return value will then be evaluated according to
	 * options 2) and 3) below.
	 *
	 * 2) If an ArticleQuery is provided, it will be passed to the active data provider of the list view 1:1.
	 *
	 * 3) Alternatively you can specify this value with a single item or an array of the following types. If an array is
	 * provided, the values within it can be a mix of the following:
	 *
	 * - asinfotrack\yii2\article\models\Article (actual article models)
	 * - string (will be matched against the canonical column of the article table)
	 * - integer (will be matched against the id column of the article table)
	 *
	 * @see \asinfotrack\yii2\article\models\query\ArticleQuery
	 * @see \asinfotrack\yii2\article\models\Article
	 * @see \Closure
	 */
	public $items;

	/**
	 * @var array optional custom config of article helper. The relevant attributes will be overwritten,
	 * while the original renderer is left untouched. If you do not provide custom config attributes here,
	 * the original renderer instance of the module is used. Otherwise the renderer will be cloned and
	 * the copies attributes changed.
	 */
	public $customArticleRendererConfig = [];

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		//assert article data is set
		if (empty($this->items)) {
			$msg = Yii::t('app', 'Article(s) must be set');
			throw new InvalidConfigException($msg);
		}

		//create the data provider
		$this->dataProvider = $this->createDataProvider();

		//use article helper for rendering if no config for list views `itemView` is provided
		if ($this->itemView === null) {
			$renderer = Module::getInstance()->renderer;
			if (!empty($this->articleRendererConfig)) {
				$renderer = clone $renderer;
				Yii::configure($renderer, $this->customArticleRendererConfig);
			}

			$this->itemView = function ($model, $key, $index, $widget) use ($renderer) {
				/* @var $model \asinfotrack\yii2\article\models\Article */
				return $renderer->render($model);
			};
		}

		//prepare options
		$this->options['id'] = $this->id;
		Html::addCssClass($this->options, 'widget-articles');

		//call list views init function
		parent::init();
	}

	/**
	 * Cares about proper loading of article data
	 */
	protected function createDataProvider()
	{
		//resolve closure if there is one
		if (is_callable($this->items)) {
			$this->items = call_user_func($this->items, $this);
		}

		//if active query is provided, create an active data provider and return
		if ($this->items instanceof ActiveQuery) {
			return new ActiveDataProvider(['query'=>$this->items]);
		}

		//assert array
		if (!is_array($this->items)) {
			$this->items = [$this->items];
		}

		//perform the actual loading of the articles
		$arr = [];
		$articleIds = [];
		foreach ($this->items as $item) {
			if ($item instanceof Article) {
				$this->addArticleAccordingToConfig($arr, $articleIds, $item);
			} else {
				$this->addArticleAccordingToConfig($arr, $articleIds, static::findArticle($item, true));
			}
		}
		$this->items = $arr;

		return new ArrayDataProvider(['allModels'=>$this->items]);
	}

	/**
	 * Adds an article to the target array only if configured criteria is met
	 *
	 * @param array $targetArr array to add the article models to
	 * @param array $loadedIds array of already loaded article ids
	 * @param \asinfotrack\yii2\article\models\Article $model the article model
	 */
	protected function addArticleAccordingToConfig(&$targetArr, &$loadedIds, $model)
	{
		if (!in_array($model->id, $loadedIds)) {
			$targetArr[] = $model;
		}
	}

	/**
	 * Finds an article by its id or canonical
	 *
	 * @param integer|string $article either the id or the canonical of an article
	 * @param bool $throwException whether or not to throw an exception when article wasn't found
	 * @return \asinfotrack\yii2\article\models\Article|null the article or null if not found and no exception desired
	 * @throws \yii\base\InvalidConfigException when exception desired and no article found
	 */
	protected static function findArticle($article, $throwException=true)
	{
		//lookup article model
		$model = Article::findOne($article);

		//throw exception if desired an no article found
		if ($model === null && $throwException) {
			$msg = Yii::t('app', 'No article found with entry `{key}`', ['key'=>$article]);
			throw new InvalidConfigException($msg);
		}

		//return model or null
		return $model;
	}

}
