<?php

use asinfotrack\yii2\article\models\Article;
use asinfotrack\yii2\article\models\MenuItem;use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use asinfotrack\yii2\article\Module;use yii\helpers\Json;
use yii\web\JsExpression;

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


$this->registerJs(new JsExpression("
	var typeDropdown = $('#menuitem-type');
	
	function showRelevantFieldsets() {
		var typeVal = parseInt(typeDropdown.val());
		
		$('fieldset').each(function (el) {
			var attrVal = $(this).attr('data-types');
			if (typeof attrVal === typeof undefined || attrVal === false || attrVal.length < 3) return;
			
			var attrTypes = JSON.parse(attrVal);
			var found = false;
			for (var i=0; i<attrTypes.length; i++) {
				if (attrTypes[i] === typeVal) {
					found = true;
					break;
				}
			}
			
			if (found) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	} 
	
	typeDropdown.change(function (event) {
		showRelevantFieldsets();
	});
	
	showRelevantFieldsets();
"));
?>

<?= $form->errorSummary($model); ?>

<fieldset>
	<legend><?= Yii::t('app', 'Entry') ?></legend>
	<?= $form->field($model, 'label')->textInput(['maxlength'=>true]) ?>
	<?php if ($model->scenario !== MenuItem::SCENARIO_MENU): ?>
		<?= $form->field($model, 'icon')->textInput(['maxlength'=>true]) ?>
		<?= $form->field($model, 'parentId')->dropDownList($itemData, ['size'=>10]) ?>
		<?= $form->field($model, 'type')->dropDownList($typeData, ['prompt'=>Yii::t('app', 'Choose a type')]) ?>
	<?php endif; ?>
</fieldset>

<fieldset data-types="<?= Json::encode([MenuItem::TYPE_ARTICLE, MenuItem::TYPE_ROUTE]) ?>">
	<legend><?= Yii::t('app', 'URL configuration') ?></legend>
	<?= $form->field($model, 'path_info')->textInput(['maxlength'=>true]) ?>
</fieldset>

<?php if ($model->scenario !== MenuItem::SCENARIO_MENU): ?>
	<fieldset data-types="<?= Json::encode([MenuItem::TYPE_ARTICLE]) ?>">
		<legend><?= Yii::t('app', 'Article target') ?></legend>
		<?= $form->field($model, 'article_id')->dropDownList($articleData, ['prompt'=>Yii::t('app', 'Choose an article')]) ?>
	</fieldset>
	<fieldset data-types="<?= Json::encode([MenuItem::TYPE_ROUTE]) ?>">
		<legend><?= Yii::t('app', 'Route target') ?></legend>
		<?= $form->field($model, 'route')->textInput(['maxlength'=>true]) ?>
		<?= $form->field($model, 'route_params')->textInput(['maxlength'=>true]) ?>
	</fieldset>
	<fieldset data-types="<?= Json::encode([MenuItem::TYPE_URL]) ?>">
		<legend><?= Yii::t('app', 'URL target') ?></legend>
		<?= $form->field($model, 'url')->textInput(['maxlength'=>true]) ?>
	</fieldset>

	<fieldset>
		<legend><?= Yii::t('app', 'Settings') ?></legend>
		<?= $form->field($model, 'is_new_tab')->checkbox() ?>
		<?= $form->field($model, 'is_published')->checkbox() ?>
	</fieldset>

	<fieldset>
		<legend><?= Yii::t('app', 'Visibility and rights') ?></legend>
		<?= $form->field($model, 'visible_item_names')->textInput(['maxlength'=>true]) ?>
		<?= $form->field($model, 'visible_callback_class')->textInput(['maxlength'=>true]) ?>
		<?= $form->field($model, 'visible_callback_method')->textInput(['maxlength'=>true]) ?>
	</fieldset>
<?php endif; ?>

<div class="form-group">
	<?= Html::submitButton(Yii::t('app', 'Save'), ['class'=>'btn btn-primary']) ?>
</div>

<?php ActiveForm::end() ?>
