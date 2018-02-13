<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use asinfotrack\yii2\article\models\Article;
use asinfotrack\yii2\article\models\ArticleCategory;
use asinfotrack\yii2\article\Module;

/* @var $this \yii\web\View */
/* @var $model \asinfotrack\yii2\article\models\Article */

$module = Module::getInstance();
$form = ActiveForm::begin([
	'enableClientValidation'=>$module->backendEnableClientValidation,
	'enableAjaxValidation'=>$module->backendEnableAjaxValidation,
]);
?>

<?= $form->errorSummary($model); ?>

<?php if (Module::getInstance()->useArticleTypes): ?>
<fieldset>
	<legend><?= Yii::t('app', 'Article type specification') ?></legend>
	<?= $form->field($model, 'type')->dropDownList(Article::typeFilter()) ?>
</fieldset>
<?php endif; ?>

<fieldset>
	<legend><?= Yii::t('app', 'Titles') ?></legend>
	<?= $form->field($model, 'title')->textInput(['maxlength'=>true]) ?>
	<?= $form->field($model, 'title_head')->textInput(['maxlength'=>true]) ?>
	<?= $form->field($model, 'title_menu')->textInput(['maxlength'=>true]) ?>
</fieldset>

<fieldset>
	<legend><?= Yii::t('app', 'Content') ?></legend>
	<?= $this->render('_form_callback_input', [
		'form'=>$form, 'model'=>$model, 'attribute'=>'intro', 'callback'=>$module->introInputCallback,
	]) ?>
	<?= $this->render('_form_callback_input', [
		'form'=>$form, 'model'=>$model, 'attribute'=>'content', 'callback'=>$module->contentInputCallback,
	]) ?>
</fieldset>

<fieldset>
	<legend><?= Yii::t('app', 'Meta & SEO') ?></legend>
	<?= $form->field($model, 'meta_keywords')->textInput(['maxlength'=>true]) ?>
	<?= $form->field($model, 'meta_description')->textarea(['rows'=>3]) ?>
</fieldset>

<fieldset>
	<legend><?= Yii::t('app', 'Settings') ?></legend>
	<?= $this->render('_form_callback_input', [
		'form'=>$form, 'model'=>$model, 'attribute'=>'published_at', 'callback'=>$module->dateInputCallback,
	]) ?>
	<?= $this->render('_form_callback_input', [
		'form'=>$form, 'model'=>$model, 'attribute'=>'published_from', 'callback'=>$module->dateInputCallback,
	]) ?>
	<?= $this->render('_form_callback_input', [
		'form'=>$form, 'model'=>$model, 'attribute'=>'published_to', 'callback'=>$module->dateInputCallback,
	]) ?>
</fieldset>

<fieldset>
	<legend><?= Yii::t('app', 'Attachments & Links') ?></legend>

</fieldset>

<fieldset>
	<legend><?= Yii::t('app', 'Category assignments') ?></legend>
	<?php $catData = ArrayHelper::map(ArticleCategory::find()->excludeRoot()->all(), 'id', 'treeLabel') ?>
	<?= $form->field($model, 'categoryIds')->dropDownList($catData, ['multiple'=>true, 'size'=>count($catData) <= 5 ? 5 : 10]) ?>
</fieldset>

<div class="form-group">
	<?= Html::submitButton(Yii::t('app', 'Save'), ['class'=>'btn btn-primary']) ?>
</div>

<?php ActiveForm::end() ?>
