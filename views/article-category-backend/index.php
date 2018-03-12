<?php
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use rmrevin\yii\fontawesome\FA;
use asinfotrack\yii2\toolbox\widgets\Button;
use asinfotrack\yii2\toolbox\widgets\grid\AdvancedActionColumn;
use asinfotrack\yii2\toolbox\widgets\grid\AdvancedDataColumn;
use asinfotrack\yii2\toolbox\widgets\grid\IdColumn;
use asinfotrack\yii2\article\Module;

/* @var $this \yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel \asinfotrack\yii2\article\models\search\ArticleSearch */

$this->title = Yii::t('app', 'Articles');
?>

<div class="buttons">
	<?= Button::widget([
		'tagName'=>'a',
		'icon'=>'asterisk',
		'label'=>Yii::t('app', 'Create an article category'),
		'options'=>[
			'href'=>Url::to(['article-category-backend/create']),
			'class'=>'btn btn-primary',
		],
	]) ?>
</div>

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
		[
			'attribute'=>'title',
			'value'=>function ($model, $key, $index, $column) {
				return $model->treeLabel;
			},
		],
		[
			'class'=>IdColumn::className(),
			'label'=>Yii::t('app', 'Articles'),
			'value'=>function ($model, $key, $index, $column) {
				$query = call_user_func([Module::getInstance()->classMap['articleModel'], 'find']);
				return $query->articleCategories($model)->count();
			},
		],

		[
			'class'=>AdvancedActionColumn::className(),
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
					return Html::a(FA::icon('arrow-up'), ['article-category-backend/move-up', 'id'=>$model->id], [
						'title'=>Yii::t('app', 'Move up'),
						'aria-label'=>Yii::t('app', 'Move up'),
						'data-pjax'=>0,
					]);
				},
				'down'=>function ($url, $model, $key) {
					return Html::a(FA::icon('arrow-down'), ['article-category-backend/move-down', 'id'=>$model->id], [
						'title'=>Yii::t('app', 'Move down'),
						'aria-label'=>Yii::t('app', 'Move down'),
						'data-pjax'=>0,
					]);
				},
			],
		],
		[
			'class'=>AdvancedActionColumn::className(),
		],
	],
]); ?>
