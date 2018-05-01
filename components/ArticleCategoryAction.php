<?php
namespace asinfotrack\yii2\article\components;

use Yii;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;
use asinfotrack\yii2\article\Module;
use asinfotrack\yii2\article\models\ArticleCategory;

/**
 * Action class used to render one or several article categories with the default view
 * provided by the module. Use the view param to provide your own view as defined by `ViewAction`
 *
 * @see \yii\web\ViewAction
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class ArticleCategoryAction extends \yii\web\ViewAction
{

	/**
	 * @var string|string[]|integer|integer[]|\asinfotrack\yii2\article\models\ArticleCategory|\asinfotrack\yii2\article\models\ArticleCategory[] either
	 * an article category model, its id, its canonical or an array of the former three.
	 */
	public $articleCategories;

	/**
	 * @var string title of the view
	 */
	public $title = 'NO TITLE';

	/**
	 * @var string meta title of the view
	 */
	public $metaTitle = 'NO TITLE';

	/**
	 * @var array the config to use for the articles widget. The `items` property
	 * of this config will be overwritten by this view action.
	 *
	 * @see \asinfotrack\yii2\article\widgets\Articles
	 */
	public $widgetConfig = [];

	/**
	 * @inheritdoc
	 */
	public $defaultView = 'article-category-action';

	/**
	 * @inheritdoc
	 */
	public $viewPrefix = '@vendor/asinfotrack/yii2-article/views/action-components';

	/**
	 * @var bool whether or not to only list published articles
	 */
	public $publishedOnly = true;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		//assert categories are set
		if (empty($this->articleCategories)) {
			throw new InvalidConfigException($msg = Yii::t('app', 'Article category must be set'));
		}

		//convert categories into proper format
		if (!is_array($this->articleCategories)) $this->articleCategories = [$this->articleCategories];
		foreach ($this->articleCategories as &$entry) {
			if ($entry instanceof ArticleCategory) {
				$entry = $entry->id;
			} else {
				$articleCategory = call_user_func([Module::getInstance()->classMap['articleCategoryModel'], 'findOne'], $entry);
				if ($articleCategory === null) $this->throwNotFound($entry);
				$entry = $articleCategory->id;
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function render($viewName)
	{
		//TODO: finish widget config with overwriting items property
		$query = call_user_func([Module::getInstance()->classMap['articleModel'], 'find']);
		$query = $query->articleCategories($this->articleCategories)->orderPublishedAt();
		if ($this->publishedOnly) $query->published(true);
		$this->widgetConfig['items'] = $query;

		return $this->controller->render($viewName, [
			'title'=>$this->title,
			'metaTitle'=>$this->metaTitle,
			'query'=>$query,
			'widgetConfig'=>$this->widgetConfig,
		]);
	}

	/**
	 * Throws a not found exception with a corresponding message
	 *
	 * @param string|integer $id either id or canonical of the article
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function throwNotFound($id=null)
	{
		$msg = $id === null ? null : Yii::t('app', 'No article category found with `{value}`', ['value'=>$id]);
		throw new NotFoundHttpException($msg);
	}

}
