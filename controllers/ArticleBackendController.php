<?php
namespace asinfotrack\yii2\article\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use asinfotrack\yii2\article\Module;
use asinfotrack\yii2\article\models\ArticleLink;
use asinfotrack\yii2\article\models\search\ArticleLinkSearch;

/**
 * Controller to manage articles in the backend
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class ArticleBackendController extends \yii\web\Controller
{

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		//default filters
		$behaviors = [
			'verbs'=>[
				'class'=>VerbFilter::className(),
				'actions'=>[
					'delete'=>['post'],
				],
			],
		];

		//access control filter if provided by module
		$module = Module::getInstance();
		if (!empty($module->backendArticleAccessControl)) {
			$behaviors['access'] = $module->backendArticleAccessControl;
		}

		return $behaviors;
	}

	public function actionIndex()
	{
		$searchModel = Yii::createObject(Module::getInstance()->classMap['articleSearchModel']);
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);

		return $this->render(Module::getInstance()->backendArticleViews['index'], [
			'searchModel'=>$searchModel,
			'dataProvider'=>$dataProvider,
		]);
	}

	public function actionView($id)
	{
		$model = $this->findModel($id);
		$linkModel = new ArticleLink(['article_id'=>$model->id]);
		$showArticlePreview = Module::getInstance()->enableArticlePreview;

		$linkSearchModel = new ArticleLinkSearch();
		$linkDataProvider = $linkSearchModel->search(Yii::$app->request->queryParams);

		if ($linkModel->load(Yii::$app->request->post())) {
			$linkModel->article_id = $model->id;
			if ($linkModel->save()) return $this->refresh();
		}

		return $this->render(Module::getInstance()->backendArticleViews['view'], [
			'model'=>$model,
			'linkModel'=>$linkModel,
			'linkDataProvider'=>$linkDataProvider,
			'linkSearchModel'=>$linkSearchModel,
			'showArticlePreview'=>$showArticlePreview,
		]);
	}

	public function actionCreate()
	{
		$model = Yii::createObject(Module::getInstance()->classMap['articleModel']);
		$loaded = $model->load(Yii::$app->request->post());

		if ($loaded && $model->save()) {
			return $this->redirect(['article-backend/view', 'id'=>$model->id]);
		}

		return $this->render(Module::getInstance()->backendArticleViews['create'], [
			'model'=>$model,
		]);
	}

	public function actionUpdate($id)
	{
		$model = $this->findModel($id);
		$loaded = $model->load(Yii::$app->request->post());

		if ($loaded && $model->save()) {
			return $this->redirect(['article-backend/view', 'id'=>$model->id]);
		}

		return $this->render(Module::getInstance()->backendArticleViews['update'], [
			'model'=>$model,
		]);
	}

	public function actionDelete($id)
	{
		$model = $this->findModel($id);
		$model->delete();

		return $this->redirect(['article-backend/index']);
	}

	/**
	 * Tries to find an article model by its id or canonical
	 *
	 * @param string|integer $idOrCanonical the id or canonical of the article model
	 * @return \asinfotrack\yii2\article\models\Article|null|\yii\db\ActiveRecord the article model or null
	 * @throws \yii\web\NotFoundHttpException when article could not be found
	 */
	protected function findModel($idOrCanonical)
	{
		$model = call_user_func([Module::getInstance()->classMap['articleModel'], 'findOne'], $idOrCanonical);
		if ($model === null) {
			$msg = Yii::t('app', 'No article found with `{value}`', ['value'=>$idOrCanonical]);
			throw new NotFoundHttpException($msg);
		}
		return $model;
	}

}
