<?php

namespace asinfotrack\yii2\article\helpers;

use Yii;

/**
 * Helper method to handle category permissions
 *
 * @author Leonardo Parrino, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class ArticleCategoryHelper
{

	/**
	 * Checks if the current user is allowed to edit this article
	 *
	 * @param \yii\db\ActiveRecord $model
	 * @return bool
	 */
	public static function checkEditCategoryPermissions($model)
	{

		$allRolesEmpty = true;
		foreach($model->articleCategories as $articleCategory) {
			/** @var \asinfotrack\yii2\article\models\ArticleCategory|\creocoder\nestedsets\NestedSetsBehavior $articleCategory */

			$categories= $articleCategory->parents()->all();
			$categories = array_merge($categories, $model->articleCategories);

			$roles = [];

			$split_char = ',';
			foreach($categories as $category) {
				/** @var \asinfotrack\yii2\article\models\ArticleCategory $category */

				// check callback
				if (!empty($category->editor_callback_class)) {
					$allRolesEmpty = false;
					if (call_user_func([$category->editor_callback_class, $category->editor_callback_method])) {
						return true;
					}
				}

				// add roles to array
				if (null !== $category->editor_item_names) {
					$roles = array_merge($roles, explode($split_char, $category->editor_item_names));
				}
			}

			$roles = array_filter($roles, function ($elem) { return (null !== $elem) && ('' !== $elem);});

			$allRolesEmpty &= (count($roles) === 0);

			foreach ($roles as $role) {
				if (Yii::$app->user->can($role)) {
					return true;
				}
			}
		}

		return $allRolesEmpty;
	}

}
