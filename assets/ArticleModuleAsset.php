<?php
namespace asinfotrack\yii2\article\assets;

/**
 * Assets for the article extension
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class ArticleModuleAsset extends \yii\web\AssetBundle
{

	/**
	 * @inheritdoc
	 */
	public $sourcePath = '@vendor/asinfotrack/yii2-article/assets/src';

	/**
	 * @inheritdoc
	 */
	public $depends = [
		'yii\web\YiiAsset',
	];

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		$this->js[] = YII_DEBUG ? 'js/yii2_article.js' : 'js/yii2_article.min.js';
	}

}
