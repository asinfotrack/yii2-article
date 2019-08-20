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
		'purifier'=>[
			'class'=>'asinfotrack\yii2\article\components\Purifier',
			'basicElements'=>[
				[
					'video',
					'Block',
					'Flow',
					'Common',
					[
						'src' => 'URI',
						'poster'=>'URI',
						'preload'=>'Enum#auto,metadata,none',
						'width' => 'Length',
						'height' => 'Length',
						'controls' => 'CDATA',
						'style' => 'CDATA'
					],
				],
				[
					'source',
					'Block',
					'Flow',
					'Common',
					[
						'src' => 'URI',
						'width' => 'Length',
						'height' => 'Length',
						'type' => 'CDATA',
						'style' => 'CDATA'
					],
				],
			],
			'additionalElements'=>[
				//add custom elements for your project
				// the structure should be like in basicElements
				// HTMLPurifier_HTMLModule::addElement()
			],
			'additionalConfig'=>[
				'Attr.AllowedFrameTargets'=>['_blank'],
				'HTML.SafeIframe'=>true,
				'URI.SafeIframeRegexp'=>'%^//www\.youtube\.com/embed/%',
			],
		],
	],

	'params'=>[
		'treeLevelPrefix'=>'—',
	],

];
