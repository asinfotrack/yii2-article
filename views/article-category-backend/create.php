<?php
use asinfotrack\yii2\toolbox\widgets\Button;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $model \asinfotrack\yii2\article\models\ArticleCategory */

$this->title = Yii::t('app', 'Create article category');
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

<?= $this->render('partials/_form', ['model'=>$model]) ?>
