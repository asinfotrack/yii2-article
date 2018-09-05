# Yii2-article
Yii2-article is a lightweight cms extension

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

###### [v0.8.11](https://github.com/asinfotrack/yii2-article/releases/tag/0.8.11)
- simplified params handling in MenuItemUrlRule

###### [v0.8.10](https://github.com/asinfotrack/yii2-article/releases/tag/0.8.10)
- bugfix in MenuItemHelper for routes

###### [v0.8.9](https://github.com/asinfotrack/yii2-article/releases/tag/0.8.9)
- added support for url parameters in MenuItemUrlRule

###### [v0.8.8](https://github.com/asinfotrack/yii2-article/releases/tag/0.8.8)
- created ArticleCategoryHelper
- moved checkEditCategoryPermissions from ArticleBackendController to ArticleCategoryHelper

###### [v0.8.7](https://github.com/asinfotrack/yii2-article/releases/tag/0.8.7)
- updated documentation of ArticleBackendController->checkEditCategoryPermissions

###### [v0.8.6](https://github.com/asinfotrack/yii2-article/releases/tag/0.8.6)
- bugfix, module id was deleted

###### [v0.8.5](https://github.com/asinfotrack/yii2-article/releases/tag/0.8.5)
- bugfix in MenuItem model in route validation
- added support for is_new_tab in MenuItem

###### [v0.8.4](https://github.com/asinfotrack/yii2-article/releases/tag/0.8.4)
- extended MenuItem to handle article categories

###### [v0.8.3](https://github.com/asinfotrack/yii2-article/releases/tag/0.8.3)
- dependency update
- replaced old usages of fa-icons with generic version

###### [v0.8.2](https://github.com/asinfotrack/yii2-article/releases/tag/0.8.2)
- bugfix in articles Widget

###### [v0.8.1](https://github.com/asinfotrack/yii2-article/releases/tag/0.8.1)
- check permisssions in article backend based on ArticleCategory editor fields

###### [v0.8.0](https://github.com/asinfotrack/yii2-article/releases/tag/0.8.0)
- main classes in a stable condition
- further features will be added in a backwards-compatible way from here on
- all breaking changes will lead to a new minor version.
