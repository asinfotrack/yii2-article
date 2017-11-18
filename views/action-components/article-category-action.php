<?php
use asinfotrack\yii2\article\widgets\Articles;

/* @var $this \yii\web\View */
/* @var $title string */
/* @var $query \asinfotrack\yii2\article\models\query\ArticleQuery */
/* @var $widgetConfig array */

$this->title = $title;
?>

<?= Articles::widget($widgetConfig) ?>
