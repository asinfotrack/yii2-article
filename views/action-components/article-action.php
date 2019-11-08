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
	if (boolval($model->is_indexed_search_engine) === false) {
		$this->registerMetaTag(['name'=>'robots', 'content'=>'noindex']);
	}
}
?>

<?= $model->render($customRendererConfig) ?>
