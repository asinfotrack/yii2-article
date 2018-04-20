<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use asinfotrack\yii2\article\Module;
use asinfotrack\yii2\article\assets\ArticleModuleAsset;
use asinfotrack\yii2\article\models\Article;

/* @var $this \yii\web\View */
/* @var $model \asinfotrack\yii2\article\models\Article */

ArticleModuleAsset::register($this);

$module = Module::getInstance();
$form = ActiveForm::begin([
	'enableClientValidation'=>$module->backendEnableClientValidation,
	'enableAjaxValidation'=>$module->backendEnableAjaxValidation,
]);
?>

<?= $form->errorSummary($model); ?>

<?php if ($module->useArticleTypes): ?>
<fieldset>
	<legend><?= Yii::t('app', 'General configuration') ?></legend>
	<?= $form->field($model, 'type')->dropDownList($model::typeFilter()) ?>
	<?= $form->field($model, 'title_internal')->textInput(['maxlength'=>true]) ?>
	<?php if (!$model->isNewRecord): ?>
		<?= $form->field($model, 'canonical')->textInput(['maxlength'=>true]) ?>
	<?php endif; ?>
</fieldset>
<?php endif; ?>

<fieldset data-types="<?= Json::encode([Article::TYPE_ARTICLE]) ?>">
	<legend><?= Yii::t('app', 'Titles') ?></legend>
	<?= $form->field($model, 'title')->textInput(['maxlength'=>true]) ?>
	<?= $form->field($model, 'title_head')->textInput(['maxlength'=>true]) ?>
	<?= $form->field($model, 'subtitle')->textInput(['maxlength'=>true]) ?>
</fieldset>

<fieldset>
	<legend><?= Yii::t('app', 'Content') ?></legend>
	<div data-types="<?= Json::encode([Article::TYPE_ARTICLE]) ?>">
		<?= $this->render('_form_callback_input', [
			'form'=>$form, 'model'=>$model, 'attribute'=>'intro', 'callback'=>$module->introInputCallback,
		]) ?>
	</div>
	<?= $this->render('_form_callback_input', [
		'form'=>$form, 'model'=>$model, 'attribute'=>'content', 'callback'=>$module->contentInputCallback,
	]) ?>
</fieldset>

<fieldset data-types="<?= Json::encode([Article::TYPE_ARTICLE]) ?>">
	<legend><?= Yii::t('app', 'Meta & SEO') ?></legend>
	<?= $form->field($model, 'meta_keywords')->textInput(['maxlength'=>true]) ?>
	<?= $form->field($model, 'meta_description')->textarea(['rows'=>3]) ?>
</fieldset>

<fieldset data-types="<?= Json::encode([Article::TYPE_ARTICLE]) ?>">
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
	<legend><?= Yii::t('app', 'Category assignments') ?></legend>
	<?php
		$query = call_user_func([Module::getInstance()->classMap['articleCategoryModel'], 'find']);
		$catData = ArrayHelper::map($query->excludeRoot()->all(), 'id', 'treeLabel')
	?>
	<?= $form->field($model, 'categoryIds')->dropDownList($catData, ['multiple'=>true, 'size'=>count($catData) <= 5 ? 5 : 10]) ?>
</fieldset>

<div class="form-group">
	<?= Html::submitButton(Yii::t('app', 'Save'), ['class'=>'btn btn-primary']) ?>
</div>

<?php ActiveForm::end() ?>
