<?php
use yii\helpers\Url;
use asinfotrack\yii2\article\models\MenuItem;
use asinfotrack\yii2\toolbox\widgets\Button;

/* @var $this \yii\web\View */
/* @var $model \asinfotrack\yii2\article\models\MenuItem */

$this->title = Yii::t('app', $model->scenario !== MenuItem::SCENARIO_MENU ? 'Update menu item' : 'Update menu');
?>

<div class="buttons">
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
		'icon'=>'eye',
		'label'=>Yii::t('app', 'Menu item details'),
		'options'=>[
			'href'=>Url::to(['menu-item-backend/view', 'id'=>$model->id]),
			'class'=>'btn btn-primary',
		],
	]) ?>
</div>

<?= $this->render('partials/_form', ['model'=>$model]) ?>
