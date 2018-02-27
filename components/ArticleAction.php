<?php
namespace asinfotrack\yii2\article\components;

use Yii;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;
use asinfotrack\yii2\article\Module;
use asinfotrack\yii2\article\models\Article;
use asinfotrack\yii2\toolbox\exceptions\ExpiredHttpException;

/**
 * Action class used to render a single article with the default view provided
 * by the module. Use the view param to provide your own view as defined by `ViewAction`
 *
 * @see \yii\web\ViewAction
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class ArticleAction extends \yii\web\ViewAction
{

	/**
	 * @var string|integer|\asinfotrack\yii2\article\models\Article either
	 * an article-model, its id or its canonical.
	 */
	public $article;

	/**
	 * @inheritdoc
	 */
	public $defaultView = 'article-action';

	/**
	 * @inheritdoc
	 */
	public $viewPrefix = '@vendor/asinfotrack/yii2-article/views/action-components';

	/**
	 * @var bool whether or not the meta information should be registered to the view. If there are nested
	 * articles, only the root articles tags will be registered
	 */
	public $registerMetaTags = true;

	/**
	 * @var array custom config for the renderer. The specified options will be merged with the default ones
	 */
	public $customRendererConfig = [];

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		//assert proper article setting
		if ($this->article === null) {
			throw new InvalidConfigException($msg = Yii::t('app', 'Article must be set'));
		}
		if (!($this->article instanceof Article)) {
			$article = call_user_func([Module::getInstance()->classMap['articleModel'], 'findOne'], $this->article);
			if ($article === null) $this->throwNotFound($this->article);
			$this->article = $article;
		}

		//check if article is within publication range
		$now = time();
		if ($this->article->published_from !== null && $this->article->published_from > $now) {
			$this->throwNotFound();
		}
		if ($this->article->published_to !== null && $this->article->published_to < $now) {
			$this->throwExpired();
		}

		//ensure module is loaded
		$loaded = false;
		foreach (Yii::$app->modules as $module) {
			if ($module instanceof Module) {
				$loaded = true;
				break;
			}
		}
		if (!$loaded) {
			$msg = Yii::t('app', 'If you want to use ArticleAction, the article module must be loaded. Ensure this by bootstrapping the module.');
			throw new InvalidConfigException($msg);
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function render($viewName)
	{
		return $this->controller->render($viewName, [
			'model'=>$this->article,
			'registerMetaTags'=>$this->registerMetaTags,
			'customRendererConfig'=>$this->customRendererConfig,
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
		$msg = $id === null ? null : Yii::t('app', 'No article found with `{value}`', ['value'=>$id]);
		throw new NotFoundHttpException($msg);
	}

	/**
	 * Throws an expired exception with a corresponding message
	 *
	 * @throws \asinfotrack\yii2\toolbox\exceptions\ExpiredHttpException
	 */
	protected function throwExpired()
	{
		$msg = Yii::t('app', 'This article is no longer available');
		throw new ExpiredHttpException($msg);
	}

}
