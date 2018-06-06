<?php

use asinfotrack\yii2\toolbox\components\Icon;
use asinfotrack\yii2\toolbox\widgets\grid\BooleanColumn;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use rmrevin\yii\fontawesome\FA;
use asinfotrack\yii2\toolbox\widgets\Button;
use asinfotrack\yii2\toolbox\widgets\grid\AdvancedActionColumn;
use asinfotrack\yii2\toolbox\widgets\grid\AdvancedDataColumn;
use asinfotrack\yii2\article\Module;

/* @var $this \yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel \asinfotrack\yii2\article\models\search\MenuItemSearch */

/* @var $query \asinfotrack\yii2\article\models\query\MenuItemQuery|\creocoder\nestedsets\NestedSetsQueryBehavior */
$query = call_user_func([Module::getInstance()->classMap['menuItemModel'], 'find']);
$menuFilter = ArrayHelper::map($query->roots()->orderBy(['menu_item.label'=>SORT_ASC])->all(), 'tree', 'label');
$typeFilter = call_user_func([Module::getInstance()->classMap['menuItemModel'], 'typeFilter']);
$stateFilter = call_user_func([Module::getInstance()->classMap['menuItemModel'], 'stateFilter']);
$this->title = Yii::t('app', 'Menu items');
?>

<div class="buttons">
	<?= Button::widget([
		'tagName'=>'a',
		'icon'=>'asterisk',
		'label'=>Yii::t('app', 'Create menu item'),
		'options'=>[
			'href'=>Url::to(['menu-item-backend/create']),
			'class'=>'btn btn-primary',
		],
	]) ?>
	<?= Button::widget([
		'tagName'=>'a',
		'icon'=>'asterisk',
		'label'=>Yii::t('app', 'Create menu'),
		'options'=>[
			'href'=>Url::to(['menu-item-backend/create-menu']),
			'class'=>'btn btn-primary',
		],
	]) ?>
</div>

<?= GridView::widget([
	'dataProvider'=>$dataProvider,
	'filterModel'=>$searchModel,
	'columns'=>[
		[
			'attribute'=>'tree',
			'label'=>Yii::t('app', 'Menu'),
			'filter'=>$menuFilter,
			'columnWidth'=>15,
			'enableSorting'=>false,
			'value'=>function ($model, $key, $index, $column) {
				/* @var $model \asinfotrack\yii2\article\models\MenuItem|\creocoder\nestedsets\NestedSetsBehavior */
				return $model->isRoot() ? $model->label : $model->parents()->one()->label;
			},
		],
		[
			'class'=>AdvancedDataColumn::class,
			'attribute'=>'type',
			'filter'=>$typeFilter,
			'columnWidth'=>10,
			'enableSorting'=>false,
			'value'=>function ($model, $key, $index, $column) use ($typeFilter) {
				return $model->type === null ? null : $typeFilter[$model->type];
			},
		],
		[
			'class'=>AdvancedDataColumn::class,
			'attribute'=>'path_info',
			'enableSorting'=>false,
			'format'=>'html',
			'value'=>function ($model, $key, $index, $column) use ($typeFilter) {
				return $model->path_info === null ? null : Html::tag('code', $model->path_info);
			},
		],
		[
			'class'=>AdvancedDataColumn::class,
			'attribute'=>'label',
			'enableSorting'=>false,
			'value'=>function ($model, $key, $index, $column) use ($typeFilter) {
				return $model->treeLabel;
			},
		],
		[
			'class'=>AdvancedDataColumn::class,
			'attribute'=>'state',
			'filter'=>$stateFilter,
			'columnWidth'=>15,
			'enableSorting'=>false,
			'value'=>function ($model, $key, $index, $column) use ($stateFilter) {
				return $model->state === null ? null : $stateFilter[$model->state];
			},
		],
		[
			'class'=>AdvancedActionColumn::class,
			'header'=>Yii::t('app', 'Order'),
			'template'=>function ($model, $key, $index) {
				/* @var $model \asinfotrack\yii2\article\models\ArticleCategory|\creocoder\nestedsets\NestedSetsBehavior */
				if ($model->isRoot()) return '';
				$buttons = [];
				if (!$model->isFirstSibling) $buttons[] = '{up}';
				if (!$model->isLastSibling) $buttons[] = '{down}';
				return implode(' ', $buttons);
			},
			'buttons'=>[
				'up'=>function ($url, $model, $key) {
					return Html::a(Icon::c('arrow-up'), ['menu-item-backend/move-up', 'id'=>$model->id], [
						'title'=>Yii::t('app', 'Move up'),
						'aria-label'=>Yii::t('app', 'Move up'),
						'data-pjax'=>0,
					]);
				},
				'down'=>function ($url, $model, $key) {
					return Html::a(Icon::c('arrow-down'), ['menu-item-backend/move-down', 'id'=>$model->id], [
						'title'=>Yii::t('app', 'Move down'),
						'aria-label'=>Yii::t('app', 'Move down'),
						'data-pjax'=>0,
					]);
				},
			],
		],
		[
			'class'=>AdvancedActionColumn::class,
		],
	],
]); ?>
