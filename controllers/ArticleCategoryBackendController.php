<?php
namespace asinfotrack\yii2\article\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use asinfotrack\yii2\article\Module;
use asinfotrack\yii2\article\models\ArticleCategory;
use asinfotrack\yii2\article\models\search\ArticleCategorySearch;

/**
 * Controller to manage article categories in the backend
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class ArticleCategoryBackendController extends \yii\web\Controller
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
		if (!empty($module->backendArticleCategoryAccessControl)) {
			$behaviors['access'] = $module->backendArticleCategoryAccessControl;
		}

		return $behaviors;
	}

	public function actionIndex()
	{
		$searchModel = new ArticleCategorySearch();
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);

		return $this->render(Module::getInstance()->backendArticleViews['index'], [
			'searchModel'=>$searchModel,
			'dataProvider'=>$dataProvider,
		]);
	}

	public function actionView($id)
	{
		$model = $this->findModel($id);

		return $this->render(Module::getInstance()->backendArticleViews['view'], [
			'model'=>$model,
		]);
	}

	public function actionCreate()
	{
		/* @var $model \creocoder\nestedsets\NestedSetsBehavior */
		$model = new ArticleCategory();
		$loaded = $model->load(Yii::$app->request->post());

		if ($loaded && $model->validate()) {
			$parentCategory = ArticleCategory::findOne( $model->parentId !== null ? $model->parentId : 1);

			if ($model->appendTo($parentCategory)) {
				return $this->redirect(['article-category-backend/view', 'id'=>$model->id]);
			}
		}

		return $this->render(Module::getInstance()->backendArticleViews['create'], [
			'model'=>$model,
		]);
	}

	public function actionUpdate($id)
	{
		/* @var $model \creocoder\nestedsets\NestedSetsBehavior */
		$model = $this->findModel($id);
		$loaded = $model->load(Yii::$app->request->post());

		if ($loaded && $model->validate()) {
			$oldParentId = (int) $model->parents(1)->one()->id;
			$newParentId = (int) $model->parentId;

			if ($oldParentId !== $newParentId) {
				$res = $model->appendTo(ArticleCategory::findOne($newParentId), false);
			} else {
				$res = $model->save(false);
			}

			if ($res) {
				return $this->redirect(['article-category-backend/view', 'id'=>$model->id]);
			}
		}

		return $this->render(Module::getInstance()->backendArticleViews['update'], [
			'model'=>$model,
		]);
	}

	public function actionDelete($id)
	{
		$model = $this->findModel($id);
		$model->delete();

		return $this->redirect(['article-category-backend/index']);
	}

	public function actionMoveUp($id)
	{
		$model = $this->findModel($id);
		$previous = $model->prev()->one();

		if ($previous !== null) $model->insertBefore($previous);

		return $this->redirect(Yii::$app->request->referrer);
	}

	public function actionMoveDown($id)
	{
		$model = $this->findModel($id);
		$next = $model->next()->one();

		if ($next !== null) $model->insertAfter($next);

		return $this->redirect(Yii::$app->request->referrer);
	}

	/**
	 * Tries to find an article category model by its id or canonical
	 *
	 * @param string|integer $idOrCanonical the id or canonical of the article model
	 * @return \asinfotrack\yii2\article\models\ArticleCategory|null|\yii\db\ActiveRecord|\creocoder\nestedsets\NestedSetsBehavior the
	 * article category model or null
	 * @throws \yii\web\NotFoundHttpException when article could not be found
	 */
	protected function findModel($idOrCanonical)
	{
		$model = ArticleCategory::findOne($idOrCanonical);
		if ($model === null) {
			$msg = Yii::t('app', 'No article found with `{value}`', ['value'=>$idOrCanonical]);
			throw new NotFoundHttpException($msg);
		}
		return $model;
	}

}
