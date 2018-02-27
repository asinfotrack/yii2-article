<?php

use yii\bootstrap\Modal;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use asinfotrack\yii2\article\Module;
use asinfotrack\yii2\article\models\Article;
use asinfotrack\yii2\article\widgets\LinkList;
use asinfotrack\yii2\article\widgets\LinkSave;
use asinfotrack\yii2\attachments\widgets\AttachmentList;
use asinfotrack\yii2\attachments\widgets\AttachmentUpload;
use asinfotrack\yii2\toolbox\widgets\Button;
use asinfotrack\yii2\toolbox\widgets\grid\AdvancedActionColumn;
use asinfotrack\yii2\toolbox\widgets\grid\AdvancedDataColumn;
use asinfotrack\yii2\toolbox\widgets\grid\BooleanColumn;
use asinfotrack\yii2\toolbox\widgets\grid\IdColumn;
use rmrevin\yii\fontawesome\FA;

/* @var $this \yii\web\View */
/* @var $model \asinfotrack\yii2\article\models\Article */
/* @var $showArticlePreview bool */
/* @var $attachmentModel \asinfotrack\yii2\attachments\models\Attachment */
/* @var $linkModel \asinfotrack\yii2\article\models\ArticleLink */
/* @var $linkDataProvider \yii\data\ActiveDataProvider */
/* @var $linkSearchModel \asinfotrack\yii2\article\models\search\ArticleLinkSearch */

$this->title = Yii::t('app', $model->title);

$valCategories = null;
if (count($model->articleCategories) > 0) {
	$valCategories = Html::beginTag('ul', ['class'=>'list-unstyled']);
	foreach ($model->articleCategories as $category) {
		$valCategories .= Html::tag('li', Html::a($category->title, ['article-category-backend/view', 'id'=>$category->id]));
	}
	$valCategories .= Html::endTag('ul');
}
?>

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
			'value'=>Article::typeFilter()[$model->type],
			'visible'=>Module::getInstance()->useArticleTypes,
		],
		'title',
		'title_head',
		'title_menu',
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

<h3><?= Yii::t('app', 'Attachments & Links') ?></h3>
<?php
$widgetUpload = AttachmentUpload::begin([
	'model'=>$attachmentModel,
	'subject'=>$model,
	'showModalImmediately'=>$attachmentModel->hasErrors(),
]);

AttachmentUpload::end();
echo Html::tag('strong', Yii::t('app', 'Attachments'));
echo AttachmentList::widget(['subject'=>$model]) . $widgetUpload->generateShowModalButton();
?>

<br/>

<?= Html::tag('strong', Yii::t('app', 'Links')) ?>

<?= GridView::widget([
		'dataProvider' => $linkDataProvider,
		'filterModel' => $linkSearchModel,
		'columns' => [
			[
				'class' => IdColumn::className(),
				'attribute' => 'id',
			],
			[
				'class' => AdvancedDataColumn::className(),
				'attribute' => 'url',
				'columnWidth' => 20,
			],
			[
				'class' => AdvancedDataColumn::className(),
				'attribute' => 'title',
				'columnWidth' => 20,
			],
			[
				'class' => BooleanColumn::className(),
				'attribute' => 'is_new_tab',
			],
			[
				'class' => AdvancedDataColumn::className(),
				'attribute' => 'createdBy',
				'columnWidth' => 20,
				'value'=>function($model, $key, $index) {
					return $model->createdBy->fullName;
				}
			],
			[
				'class' => AdvancedActionColumn::className(),
				'header' => Yii::t('app', 'Open'),
				//'template' => '{link}',
				'buttons' => [
					'view' => function ($url, $model, $key) {
						return Html::a(FA::icon('external-link'), $model->url, ['target'=>'_blank']);
					},
				],
			],
			//'',
		]
	]);
?>
<?php
$modalLinkTitle = FA::icon('link').' '.Yii::t('app', 'Add link');
$modalLinkOptions = !$linkModel->hasErrors() ? [] : ['data'=>['show-immediately'=>true]];
$modalLink = Modal::begin([
	'id'=>'modal-link',
	'options'=>$modalLinkOptions,
	'header'=>Html::tag('h4', $modalLinkTitle),
	'toggleButton'=>[
		'tag'=>'a',
		'label'=>$modalLinkTitle,
		'class'=>'btn-primary btn',
	],
]);
?>
<?= $this->render('partials/_view_form_link', [
	'model'=>$linkModel,
	'articleModel'=>$model,
]) ?>
<?php Modal::end(); ?>


<?php if ($showArticlePreview): ?>

<hr/>

<h2><?= Yii::t('app', 'Article preview') ?></h2>
<div class="well article-preview">
	<?= $model->render() ?>
</div>

<?php endif; ?>
