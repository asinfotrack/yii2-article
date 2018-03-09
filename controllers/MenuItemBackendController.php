<?php
namespace asinfotrack\yii2\article\controllers;

use asinfotrack\yii2\article\models\MenuItem;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use asinfotrack\yii2\article\Module;

/**
 * Controller to manage menu items in the backend
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class MenuItemBackendController extends \yii\web\Controller
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
		if (!empty($module->backendMenuItemsAccessControl)) {
			$behaviors['access'] = $module->backendMenuItemsAccessControl;
		}

		return $behaviors;
	}

	public function actionIndex()
	{
		$searchModel = Yii::createObject(Module::getInstance()->classMap['menuItemSearchModel']);
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams, true, true);

		return $this->render(Module::getInstance()->backendMenuItemViews['index'], [
			'searchModel'=>$searchModel,
			'dataProvider'=>$dataProvider,
		]);
	}

	public function actionView($id)
	{
		$model = $this->findModel($id);

		return $this->render(Module::getInstance()->backendMenuItemViews['view'], [
			'model'=>$model,
		]);
	}

	public function actionCreate()
	{
		/* @var $model \creocoder\nestedsets\NestedSetsBehavior|\asinfotrack\yii2\article\models\MenuItem */
		$model = Yii::createObject(Module::getInstance()->classMap['menuItemModel']);
		$loaded = $model->load(Yii::$app->request->post());

		if ($loaded && $model->validate()) {
			$parentItem = $this->findModel($model->parentId);

			if ($model->appendTo($parentItem)) {
				return $this->redirect(['menu-item-backend/view', 'id'=>$model->id]);
			}
		}

		return $this->render(Module::getInstance()->backendMenuItemViews['create'], [
			'model'=>$model,
		]);
	}

	public function actionCreateMenu()
	{
		/* @var $model \creocoder\nestedsets\NestedSetsBehavior|\asinfotrack\yii2\article\models\MenuItem */
		$model = Yii::createObject(Module::getInstance()->classMap['menuItemModel']);
		$loaded = $model->load(Yii::$app->request->post());
		$model->scenario = MenuItem::SCENARIO_MENU;
		$model->parentId = null;

		if ($loaded && $model->makeRoot()) {
			return $this->redirect(['menu-item-backend/view', 'id'=>$model->id]);
		}

		return $this->render(Module::getInstance()->backendMenuItemViews['create'], [
			'model'=>$model,
		]);
	}

	public function actionUpdate($id)
	{
		/* @var $model \creocoder\nestedsets\NestedSetsBehavior|\asinfotrack\yii2\article\models\MenuItem */
		$model = $this->findModel($id);
		$loaded = $model->load(Yii::$app->request->post());

		if ($loaded && $model->validate()) {
			$oldParentId = (int) $model->parents(1)->one()->id;
			$newParentId = (int) $model->parentId;

			if ($oldParentId !== $newParentId) {
				$res = $model->appendTo($this->findModel($newParentId), false);
			} else {
				$res = $model->save(false);
			}

			if ($res) {
				return $this->redirect(['menu-item-backend/view', 'id'=>$model->id]);
			}
		}

		return $this->render(Module::getInstance()->backendMenuItemViews['update'], [
			'model'=>$model,
		]);
	}

	public function actionDelete($id)
	{
		$model = $this->findModel($id);
		$model->delete();

		return $this->redirect(['menu-item-backend/index']);
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
	 * Tries to find a menu item model by its id
	 *
	 * @param integer $id the id of the menu item model
	 * @return \asinfotrack\yii2\article\models\MenuItem|null|\yii\db\ActiveRecord|\creocoder\nestedsets\NestedSetsBehavior the
	 * menu item model or null
	 * @throws \yii\web\NotFoundHttpException when menu item could not be found
	 */
	protected function findModel($id)
	{
		$model = call_user_func([Module::getInstance()->classMap['menuItemModel'], 'findOne'], $id);
		if ($model === null) {
			$msg = Yii::t('app', 'No menu item found with `{value}`', ['value'=>$id]);
			throw new NotFoundHttpException($msg);
		}
		return $model;
	}

}
