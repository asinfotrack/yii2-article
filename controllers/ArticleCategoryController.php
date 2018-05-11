<?php
namespace asinfotrack\yii2\article\controllers;

use asinfotrack\yii2\article\components\ArticleAction;
use asinfotrack\yii2\article\models\ArticleCategory;
use yii\base\Module;

class ArticleCategoryController extends \yii\web\Controller
{

	public function actionRender($id) {
		$model = ArticleCategory::findOne($id);
		return $this->render('view', ['model'=>$model]);
	}



}
