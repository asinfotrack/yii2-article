<?php
namespace asinfotrack\yii2\article\helpers;

use Yii;
use yii\helpers\FileHelper;
use asinfotrack\yii2\article\Module;

/**
 * Helper class to work with directories and files related to an articles attachments
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class AttachmentHelper
{

	/**
	 * Creates the directory for an article, if it does not exist yet
	 *
	 * @param integer $articleId the id of the article
	 * @return bool true upon success
	 */
	public static function createDirectoryForArticle($articleId)
	{
		return static::assertDirectoryExists($articleId);
	}

	/**
	 * Deletes the directory of an article (with all its contents)
	 *
	 * @param integer $articleId the article id
	 */
	public static function deleteDirectoryForModel($articleId)
	{
		FileHelper::removeDirectory(static::createAbsolutePathString($articleId));
	}

	/**
	 * Asserts, that a directory exists
	 *
	 * @param integer $articleId the article id
	 * @return bool true if it exists or was successfully created
	 */
	protected static function assertDirectoryExists($articleId)
	{
		$path = static::createAbsolutePathString();
		if (!file_exists($path) && !is_dir($path)) {
			return FileHelper::createDirectory($path);
		}
		return true;
	}

	/**
	 * creates the absolute path from an alias
	 *
	 * @param integer|null $articleId optional article id (if not specified, the root
	 * path of the attachments will be returned)
	 * @return string the normalized path
	 */
	protected static function createAbsolutePathString($articleId=null)
	{
		$alias = Module::getInstance()->attachmentAlias;
		$path = Yii::getAlias($alias);
		if ($articleId !== null) $path .= DIRECTORY_SEPARATOR . $articleId;
		return FileHelper::normalizePath($path);
	}

}
