<?php

use asinfotrack\yii2\article\models\MenuItem;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\helpers\VarDumper;
use yii\widgets\DetailView;
use asinfotrack\yii2\article\Module;
use asinfotrack\yii2\toolbox\widgets\Button;

/* @var $this \yii\web\View */
/* @var $model \asinfotrack\yii2\article\models\MenuItem|\creocoder\nestedsets\NestedSetsBehavior */

$typeFilter = call_user_func([Module::getInstance()->classMap['menuItemModel'], 'typeFilter']);

$this->title = Yii::t('app', 'Menu item details');
?>

<?= Button::widget([
	'tagName'=>'a',
	'icon'=>'list',
	'label'=>Yii::t('app', 'All menu items'),
	'options'=>[
		'href'=>Url::to(['menu-item-backend/index']),
		'class'=>'btn btn-primary',
	],
]) ?>
<?= Button::widget([
	'tagName'=>'a',
	'icon'=>'pencil',
	'label'=>Yii::t('app', 'Update menu-item'),
	'options'=>[
		'href'=>Url::to(['menu-item-backend/update', 'id'=>$model->id]),
		'class'=>'btn btn-primary',
	],
]) ?>

<?= DetailView::widget([
	'model'=>$model,
	'attributes'=>[
		[
			'attribute'=>'id',
		],
		'label',
		[
			'attribute'=>'is_new_tab',
			'format'=>'boolean',
			'visible'=>$model->type !== MenuItem::TYPE_MENU,
		],
		[
			'attribute'=>'type',
			'value'=>$typeFilter[$model->type],
			'visible'=>$model->type !== MenuItem::TYPE_MENU,
		],
		[
			'attribute'=>'route',
			'format'=>$model->type === MenuItem::TYPE_URL ? 'url' : null,
			'visible'=>$model->type === MenuItem::TYPE_URL || $model->type === MenuItem::TYPE_ROUTE,
		],
		[
			'attribute'=>'params',
			'format'=>'raw',
			'visible'=>$model->type === MenuItem::TYPE_ROUTE,
			'value'=>empty($model->params) ? null : VarDumper::dumpAsString(Json::decode($model->params), 10, true),
		],
	],
]) ?>
