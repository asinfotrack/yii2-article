<?php
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\helpers\VarDumper;
use yii\widgets\DetailView;
use asinfotrack\yii2\article\Module;
use asinfotrack\yii2\article\models\MenuItem;
use asinfotrack\yii2\toolbox\widgets\Button;

/* @var $this \yii\web\View */
/* @var $model \asinfotrack\yii2\article\models\MenuItem|\creocoder\nestedsets\NestedSetsBehavior */

$typeFilter = call_user_func([Module::getInstance()->classMap['menuItemModel'], 'typeFilter']);

$this->title = Yii::t('app', $model->scenario !== MenuItem::SCENARIO_MENU ? 'Menu item details' : 'Menu details');
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
	'label'=>Yii::t('app', $model->scenario !== MenuItem::SCENARIO_MENU ? 'Update menu-item' : 'Update menu'),
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
			'visible'=>!$model->isRoot(),
		],
		[
			'attribute'=>'type',
			'value'=>$model->isRoot() ? null : $typeFilter[$model->type],
			'visible'=>!$model->isRoot(),
		],
		[
			'attribute'=>'article_id',
			'visible'=>$model->type === MenuItem::TYPE_ARTICLE,
			'format'=>'html',
			'value'=>$model->article_id === null ? null : Html::a($model->article->title, ['article-backend/view', 'id'=>$model->article_id]),
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
