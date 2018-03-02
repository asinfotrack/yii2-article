<?php

use asinfotrack\yii2\article\models\Article;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use asinfotrack\yii2\article\Module;

/* @var $this \yii\web\View */
/* @var $model \asinfotrack\yii2\article\models\MenuItem|\creocoder\nestedsets\NestedSetsBehavior */

$module = Module::getInstance();

$query = call_user_func([$module->classMap['menuItemModel'], 'find']);
$itemData = ArrayHelper::map($query->all(), 'id', 'treeLabel');

$form = ActiveForm::begin([
	'enableClientValidation'=>$module->backendEnableClientValidation,
	'enableAjaxValidation'=>$module->backendEnableAjaxValidation,
]);

$typeData = call_user_func([Module::getInstance()->classMap['menuItemModel'], 'typeFilter']);
$articleData = ArrayHelper::map(Article::find()->type([Article::TYPE_ARTICLE, Article::TYPE_UNDEFINED])->all(), 'id', 'title');
?>

<?= $form->errorSummary($model); ?>

<fieldset>
	<legend><?= Yii::t('app', 'Menu item') ?></legend>
	<?= $form->field($model, 'label')->textInput(['maxlength'=>true]) ?>
	<?= $form->field($model, 'type')->dropDownList($typeData, ['prompt'=>Yii::t('app', 'Choose a type')]) ?>
	<?= $form->field($model, 'parentId')->dropDownList($itemData, ['prompt'=>Yii::t('app', 'Choose a parent item')]) ?>
</fieldset>

<fieldset>
	<legend><?= Yii::t('app', 'Settings') ?></legend>
	<?= $form->field($model, 'is_new_tab')->checkbox() ?>
</fieldset>

<fieldset>
	<legend><?= Yii::t('app', 'Target') ?></legend>
	<?= $form->field($model, 'article_id')->dropDownList($articleData, ['prompt'=>Yii::t('app', 'Choose an article')]) ?>
	<?= $form->field($model, 'route')->textInput(['maxlength'=>true]) ?>
	<?= $form->field($model, 'params')->textInput(['maxlength'=>true]) ?>
</fieldset>

<div class="form-group">
	<?= Html::submitButton(Yii::t('app', 'Save'), ['class'=>'btn btn-primary']) ?>
</div>

<?php ActiveForm::end() ?>
