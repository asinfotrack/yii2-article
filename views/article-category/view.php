<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use asinfotrack\yii2\article\Module;
use asinfotrack\yii2\toolbox\widgets\Button;
use asinfotrack\yii2\article\controllers\ArticleBackendController;

/* @var $this \yii\web\View */
/* @var $model \asinfotrack\yii2\article\models\ArticleCategory */


$this->title = Yii::t('app', 'Article category');

?>


<?php foreach ($model->articles as $article): ?>
	<h2><?= $article->title?></h2>
	<?php $article->render() ?>
<?php endforeach; ?>

