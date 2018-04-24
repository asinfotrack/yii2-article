<?php

use asinfotrack\yii2\article\models\State;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\bootstrap\ActiveForm;
use asinfotrack\yii2\article\assets\ArticleModuleAsset;
use asinfotrack\yii2\article\models\Article;
use asinfotrack\yii2\article\models\MenuItem;
use asinfotrack\yii2\article\Module;

/* @var $this \yii\web\View */
/* @var $model \asinfotrack\yii2\article\models\MenuItem|\creocoder\nestedsets\NestedSetsBehavior */

ArticleModuleAsset::register($this);

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
		<?= $form->field($model, 'state_id')->dropDownList(ArrayHelper::map(State::find()->all(), 'id', 'name'), ['prompt'=>Yii::t('app', 'Choose an state')]) ?>
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
