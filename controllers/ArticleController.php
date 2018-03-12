<?php
namespace asinfotrack\yii2\article\controllers;

use asinfotrack\yii2\article\components\ArticleAction;

class ArticleController extends \yii\web\Controller
{

	/**
	 * @inheritdoc
	 */
	public function actions()
	{
		return [
			'render'=>[
				'class'=>ArticleAction::className(),
			],
		];
	}

}
