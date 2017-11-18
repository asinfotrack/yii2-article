<?php
use yii\helpers\Html;
use yii\helpers\Markdown;
use asinfotrack\yii2\article\Module;

return [

	'defaultRoute'=>'article/index',

	'introInputCallback'=>[Module::className(), 'defaultEditorInput'],
	'contentInputCallback'=>[Module::className(), 'defaultEditorInput'],
	'dateInputCallback'=>[Module::className(), 'defaultDateInput'],

	'components'=>[
		'renderer'=>[
			'class'=>'asinfotrack\yii2\article\components\ArticleRenderer',
			'introRenderCallback'=>function ($intro) {
				return Html::tag('p', $intro, ['class'=>'lead']);
			},
			'contentRenderCallback'=>function ($content) {
				return Markdown::process($content);
			},
			'placeholderCallbackMap'=>[

			],
		],
	],

	'params'=>[
		'treeLevelPrefix'=>'—',
	],

];
