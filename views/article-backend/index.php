<?php

use asinfotrack\yii2\toolbox\widgets\grid\AdvancedActionColumn;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use asinfotrack\yii2\article\models\Article;
use asinfotrack\yii2\toolbox\widgets\Button;
use asinfotrack\yii2\toolbox\widgets\grid\AdvancedDataColumn;
use asinfotrack\yii2\toolbox\widgets\grid\IdColumn;

/* @var $this \yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel \asinfotrack\yii2\article\models\search\ArticleSearch */

$this->title = Yii::t('app', 'Articles');
?>

<?= Button::widget([
	'tagName'=>'a',
	'icon'=>'asterisk',
	'label'=>Yii::t('app', 'Create an article'),
	'options'=>[
		'href'=>Url::to(['article-backend/create']),
		'class'=>'btn btn-primary',
	],
]) ?>

<?= GridView::widget([
	'dataProvider'=>$dataProvider,
	'filterModel'=>$searchModel,
	'columns'=>[
		[
			'class'=>IdColumn::className(),
			'attribute'=>'id',
		],
		[
			'class'=>AdvancedDataColumn::className(),
			'attribute'=>'canonical',
			'format'=>'html',
			'columnWidth'=>20,
			'value'=>function ($model, $key, $index, $column) {
				return Html::tag('code', $model->canonical);
			},
		],
		'title',
		[
			'class'=>AdvancedDataColumn::className(),
			'attribute'=>'type',
			'columnWidth'=>10,
			'filter'=>Article::typeFilter(),
			'value'=>function ($model, $key, $index, $column) {
				return Article::typeFilter()[$model->type];
			},
		],
		[
			'class'=>AdvancedActionColumn::className(),
		],
	],
]); ?>
