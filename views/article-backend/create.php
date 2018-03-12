<?php
use yii\helpers\Url;
use asinfotrack\yii2\toolbox\widgets\Button;

/* @var $this \yii\web\View */
/* @var $model \asinfotrack\yii2\article\models\Article */

$this->title = Yii::t('app', 'Create article');
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
</div>

<?= $this->render('partials/_form', ['model'=>$model]) ?>
