<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use asinfotrack\yii2\toolbox\widgets\Button;

/* @var $this \yii\web\View */
/* @var $model \asinfotrack\yii2\article\models\ArticleCategory|\creocoder\nestedsets\NestedSetsBehavior */

$this->title = Yii::t('app', 'Article category details');

//parent category
$parent = $model->parents(1)->one();
$valParent = $parent === null || $parent->id === 1 ? null : Html::a($parent->title, ['article-category-backend/view', 'id'=>$parent->id]);

//child categories
$children = $model->children(1)->all();
$valChildren = null;
if (count($children) > 0) {
	$valChildren = Html::beginTag('ul', ['class'=>'list-unstyled']);
	foreach ($children as $child) {
		$valChildren .= Html::tag('li', Html::a($child->title, ['article-category-backend/view', 'id'=>$child->id]));
	}
	$valChildren .= Html::endTag('ul');
}

//articles
$valArticles = null;
if (count($model->articles) > 0) {
	$valArticles = Html::beginTag('ul', ['class'=>'list-unstyled']);
	foreach ($model->articles as $article) {
		$valArticles .= Html::tag('li', Html::a($article->title, ['article-backend/view', 'id'=>$article->id]));
	}
	$valArticles .= Html::endTag('ul');
}
?>

<?= Button::widget([
	'tagName'=>'a',
	'icon'=>'list',
	'label'=>Yii::t('app', 'All article categories'),
	'options'=>[
		'href'=>Url::to(['article-category-backend/index']),
		'class'=>'btn btn-primary',
	],
]) ?>
<?= Button::widget([
	'tagName'=>'a',
	'icon'=>'pencil',
	'label'=>Yii::t('app', 'Update article category'),
	'options'=>[
		'href'=>Url::to(['article-category-backend/update', 'id'=>$model->id]),
		'class'=>'btn btn-primary',
	],
]) ?>

<?= DetailView::widget([
	'model'=>$model,
	'attributes'=>[
		[
			'attribute'=>'id',
		],
		[
			'attribute'=>'canonical',
			'format'=>'html',
			'value'=>Html::tag('code', $model->canonical),
		],
		'title',
		'title_head',
		'title_menu',
		[
			'label'=>Yii::t('app', 'Parent category'),
			'format'=>'html',
			'value'=>$valParent,
		],
		[
			'label'=>Yii::t('app', 'Child categories'),
			'format'=>'html',
			'value'=>$valChildren,
		],
		[
			'attribute'=>'articles',
			'format'=>'html',
			'value'=>$valArticles,
		],
	],
]) ?>
