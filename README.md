# Yii2-article
Yii2-article is a lightweight cms extension

__WATCH OUT: this extension is still under development. Breaking changes can occur without notice until first release!__

## Installation

### Basic installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
$ composer require asinfotrack/yii2-article
```

or add

```
"asinfotrack/yii2-article": "~0.8.0"
```

to the `require` section of your `composer.json` file.

### Migration
    
After downloading you need to apply the migration creating the required tables:

    yii migrate --migrationPath=@vendor/asinfotrack/yii2-article/migrations
    yii migrate --migrationPath=@vendor/asinfotrack/yii2-attachments/migrations
    
To remove the table just do the same migration downwards.

### Add the module to the yii-config

```php
    'modules'=>[
        
        //your other modules...
        
        'article'=>[
            'class'=>'asinfotrack\yii2\article\Module',
            
            'userRelationCallback'=>function ($model, $attribute) {
                return $model->hasOne('app\models\User', ['id'=>$attribute]);
            },
            'backendArticleAccessControl' = [
                'class'=>'yii\filters\AccessControl',
                'rules'=>[
                    ['allow'=>true, 'roles'=>['@']],
                ],
            ],
            'backendArticleCategoryAccessControl' = [
                'class'=>'yii\filters\AccessControl',
                'rules'=>[
                    ['allow'=>true, 'roles'=>['@']],
                ],
            ],
            
            'components'=>[   
                //configuration of the renderer         
                'renderer'=>[
                    'class'=>'asinfotrack\yii2\article\components\ArticleRenderer',
                    'addDataAttributesToArticleTagOptions'=>true,
                    'showDebugTags'=>false,
                    'placeholderCallbackMap'=>[
                        //example for a custom placeholder for an image tag                        
                        'img'=>function ($params) {
                            return Html::img($params[0]);
                        },
                        
                        //your other custom placeholder tags here...
                    ],
                ],                
            ],
        ],
    ],
```

For a full list of options, see the attributes of the classes within the module. Especially check the classes
`asinfotrack\yii2\article\Module` and `asinfotrack\yii2\article\components\ArticleRenderer`. Some examples are
provided below.

### Bootstrapping the module

This step is optional and only necessary when you want to use the `ArticleAction` in a controller outside the actual 
module.

Add the module to the bootstrap-array of your yii-config to ensure it is loaded when the third party controller 
accesses the `ArticleRender`. Make sure you use the same module-ID as you use in the step right above.

```php
'bootstrap'=>['log', 'article'],
```

## Changelog

###### [v0.8.0] (work in progress)
- main classes in a stable condition
- further features will be added in a backwards-compatible way from here on
- all breaking changes will lead to a new minor version.
