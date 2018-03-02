<?php
namespace asinfotrack\yii2\article\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use asinfotrack\yii2\article\models\MenuItem;
use asinfotrack\yii2\article\Module;

/**
 * Controller to manage root menu items in the backend
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class MenuBackendController extends \asinfotrack\yii2\article\controllers\MenuItemBackendController
{

	public function actionIndex()
	{
		$searchModel = Yii::createObject(Module::getInstance()->classMap['menuItemSearchModel']);
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams, false, true);

		return $this->render(Module::getInstance()->backendMenuItemViews['index'], [
			'searchModel'=>$searchModel,
			'dataProvider'=>$dataProvider,
		]);
	}

	public function actionCreate()
	{
		/* @var $model \creocoder\nestedsets\NestedSetsBehavior|\asinfotrack\yii2\article\models\MenuItem */
		$model = Yii::createObject(Module::getInstance()->classMap['menuItemModel']);
		$model->scenario = MenuItem::SCENARIO_MENU;
		$loaded = $model->load(Yii::$app->request->post());

		if ($loaded && $model->makeRoot()) {
			return $this->redirect(['menu-backend/view', 'id'=>$model->id]);
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

		if ($loaded && $model->save()) {
			return $this->redirect(['menu-backend/view', 'id'=>$model->id]);
		}

		return $this->render(Module::getInstance()->backendMenuItemViews['update'], [
			'model'=>$model,
		]);
	}

	public function actionMoveUp($id)
	{
		throw new NotFoundHttpException();
	}

	public function actionMoveDown($id)
	{
		throw new NotFoundHttpException();
	}

	/**
	 * Tries to find a root menu item model by its id
	 *
	 * @param integer $id the id of the root menu item model
	 * @return \asinfotrack\yii2\article\models\MenuItem|null|\yii\db\ActiveRecord|\creocoder\nestedsets\NestedSetsBehavior the
	 * root menu item model or null
	 * @throws \yii\web\NotFoundHttpException when menu item could not be found
	 */
	protected function findModel($id)
	{
		/* @var $query \asinfotrack\yii2\article\models\query\MenuItemQuery|\creocoder\nestedsets\NestedSetsQueryBehavior */
		$query = call_user_func([Module::getInstance()->classMap['menuItemModel'], 'find'])->roots()->andWhere(['menu_item.id'=>$id]);
		$model = $query->one();

		if ($model === null) {
			$msg = Yii::t('app', 'No menu found with `{value}`', ['value'=>$id]);
			throw new NotFoundHttpException($msg);
		}
		return $model;
	}

	/**
	 * @inheritdoc
	 */
	public function getViewPath()
	{
		return str_replace($this->id, 'menu-item-backend', parent::getViewPath());
	}

}
