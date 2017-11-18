<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use asinfotrack\yii2\article\Module;
use asinfotrack\yii2\article\models\ArticleCategory;

/* @var $this \yii\web\View */
/* @var $model \asinfotrack\yii2\article\models\ArticleCategory|\creocoder\nestedsets\NestedSetsBehavior */

$catData = [];
$catDataOptions = [];
$childIds = $model->children()->select('id')->column();
foreach (ArticleCategory::find()->excludeRoot()->all() as $category) {
	$catData[$category->id] = $category->treeLabel;
	$catDataOptions[$category->id] = [
		'disabled'=>$model->id === $category->id || in_array($category->id, $childIds),
	];
}

$module = Module::getInstance();
$form = ActiveForm::begin([
	'enableClientValidation'=>$module->backendEnableClientValidation,
	'enableAjaxValidation'=>$module->backendEnableAjaxValidation,
]);
?>

<?= $form->errorSummary($model); ?>

<fieldset>
	<legend><?= Yii::t('app', 'Titles') ?></legend>
	<?= $form->field($model, 'title')->textInput(['maxlength'=>true]) ?>
	<?= $form->field($model, 'title_head')->textInput(['maxlength'=>true]) ?>
	<?= $form->field($model, 'title_menu')->textInput(['maxlength'=>true]) ?>
</fieldset>

<fieldset>
	<legend><?= Yii::t('app', 'Settings') ?></legend>
	<?= $form->field($model, 'parentId')->dropDownList($catData, [
		'size'=>10,
		'prompt'=>['options'=>['value'=>1], 'text'=>Yii::t('app', 'None / new root cetegory')],
		'options'=>$catDataOptions,
	]) ?>
</fieldset>

<div class="form-group">
	<?= Html::submitButton(Yii::t('app', 'Save'), ['class'=>'btn btn-primary']) ?>
</div>

<?php ActiveForm::end() ?>
