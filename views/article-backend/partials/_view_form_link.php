<?php
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

/* @var $this \yii\web\View */
/* @var $model \asinfotrack\yii2\article\models\ArticleLink */
/* @var $articleModel \asinfotrack\yii2\article\models\Article */
?>

<?php $form = ActiveForm::begin(['enableClientValidation'=>false]) ?>
	<?= $form->errorSummary($model); ?>
	<?= $form->field($model, 'url')->textInput(['maxlength'=>true]) ?>
	<?= $form->field($model, 'is_new_tab')->checkbox([]) ?>
	<?= $form->field($model, 'title')->textInput(['maxlength'=>true]) ?>
	<?= $form->field($model, 'description')->textarea(['rows'=>4]) ?>

	<?= Html::submitButton(Yii::t('app', 'Save'), ['class'=>'btn btn-primary']) ?>
<?php ActiveForm::end() ?>
