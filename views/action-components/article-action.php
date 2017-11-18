<?php
/* @var $this \yii\web\View*/
/* @var $model \asinfotrack\yii2\article\models\Article */
/* @var $registerMetaTags bool */
/* @var $customRendererConfig array */

$this->title = !empty($model->title_head) ? $model->title_head : $model->title;

//registering of meta tags
if ($registerMetaTags) {
	if (!empty($model->meta_keywords)) {
		$this->registerMetaTag(['name'=>'keywords', 'content'=>$model->meta_keywords]);
	}
	if (!empty($model->meta_description)) {
		$this->registerMetaTag(['name'=>'description', 'content'=>$model->meta_description]);
	}
}
?>

<?= $model->render($customRendererConfig) ?>
