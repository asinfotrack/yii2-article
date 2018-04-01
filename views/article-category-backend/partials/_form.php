<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use asinfotrack\yii2\article\Module;

/* @var $this \yii\web\View */
/* @var $model \asinfotrack\yii2\article\models\ArticleCategory|\creocoder\nestedsets\NestedSetsBehavior */

$catData = [];
$catDataOptions = [];
$childIds = $model->children()->select('id')->column();
$query = call_user_func([Module::getInstance()->classMap['articleCategoryModel'], 'find']);
foreach ($query->excludeRoot()->all() as $category) {
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
	<legend><?= Yii::t('app', 'General configuration') ?></legend>
	<?= $form->field($model, 'title_internal')->textInput(['maxlength'=>true]) ?>
	<?php if (!$model->isNewRecord): ?>
		<?= $form->field($model, 'canonical')->textInput(['maxlength'=>true]) ?>
	<?php endif; ?>
</fieldset>

<fieldset>
	<legend><?= Yii::t('app', 'Titles') ?></legend>
	<?= $form->field($model, 'title')->textInput(['maxlength'=>true]) ?>
	<?= $form->field($model, 'title_head')->textInput(['maxlength'=>true]) ?>
</fieldset>

<fieldset>
	<legend><?= Yii::t('app', 'Settings') ?></legend>
	<?= $form->field($model, 'parentId')->dropDownList($catData, [
		'size'=>10,
		'prompt'=>['options'=>['value'=>1], 'text'=>Yii::t('app', 'None / new root category')],
		'options'=>$catDataOptions,
	]) ?>
</fieldset>

<div class="form-group">
	<?= Html::submitButton(Yii::t('app', 'Save'), ['class'=>'btn btn-primary']) ?>
</div>

<?php ActiveForm::end() ?>
