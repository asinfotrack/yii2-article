<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use asinfotrack\yii2\article\Module;
use asinfotrack\yii2\toolbox\widgets\Button;

/* @var $this \yii\web\View */
/* @var $model \asinfotrack\yii2\article\models\Article */
/* @var $showArticlePreview bool */

$this->title = Yii::t('app', 'Article details');

$valCategories = null;
if (count($model->articleCategories) > 0) {
	$valCategories = Html::beginTag('ul', ['class'=>'list-unstyled']);
	foreach ($model->articleCategories as $category) {
		$valCategories .= Html::tag('li', Html::a($category->title, ['article-category-backend/view', 'id'=>$category->id]));
	}
	$valCategories .= Html::endTag('ul');
}
?>

<div class="buttons">
	<?= Button::widget([
		'tagName'=>'a',
		'icon'=>'list',
		'label'=>Yii::t('app', 'All articles'),
		'options'=>[
			'href'=>Url::to(['article-backend/index']),
			'class'=>'btn btn-primary',
		],
	]) ?>
	<?= Button::widget([
		'tagName'=>'a',
		'icon'=>'pencil',
		'label'=>Yii::t('app', 'Update article'),
		'options'=>[
			'href'=>Url::to(['article-backend/update', 'id'=>$model->id]),
			'class'=>'btn btn-primary',
		],
	]) ?>
</div>

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
		[
			'attribute'=>'type',
			'value'=>$model::typeFilter()[$model->type],
			'visible'=>Module::getInstance()->useArticleTypes,
		],
		'title_internal',
		'title',
		'title_head',
		'subtitle',
		'published_at:date',
		'published_from:date',
		'published_to:date',
		'isPublished:boolean',
		[
			'label'=>Yii::t('app', 'Article categories'),
			'format'=>'html',
			'value'=>$valCategories,
		],
		'meta_keywords',
		'meta_description',
	],
]) ?>

<?php if ($showArticlePreview): ?>
<hr/>
<h2><?= Yii::t('app', 'Article preview') ?></h2>
<div class="well article-preview">
	<?= $model->render() ?>
</div>
<?php endif; ?>
