<?php
use yii\helpers\Url;
use asinfotrack\yii2\toolbox\widgets\Button;

/* @var $this \yii\web\View */
/* @var $model \asinfotrack\yii2\article\models\ArticleCategory */

$this->title = Yii::t('app', 'Update article category');
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
	'icon'=>'eye',
	'label'=>Yii::t('app', 'Article category details'),
	'options'=>[
		'href'=>Url::to(['article-category-backend/view', 'id'=>$model->id]),
		'class'=>'btn btn-primary',
	],
]) ?>

<?= $this->render('partials/_form', ['model'=>$model]) ?>
