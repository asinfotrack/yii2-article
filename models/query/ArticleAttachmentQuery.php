<?php
namespace asinfotrack\yii2\article\models\query;

use asinfotrack\yii2\article\models\Article;

/**
 * The query class for article attachments providing the most common named scopes.
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class ArticleAttachmentQuery extends \yii\db\ActiveQuery
{

	/**
	 * Named scope to filter article links by their assigned articles.
	 * The param can be specified with a single id or am article model or a mixed array of the two
	 * types.
	 *
	 * @param integer|integer[]|\asinfotrack\yii2\article\models\Article|\asinfotrack\yii2\article\models\Article[] $articles the articles
	 * @return \asinfotrack\yii2\article\models\query\ArticleAttachmentQuery $this self for chaining
	 */
	public function article($articles)
	{
		//assert array of ids
		if (!is_array($articles)) $articles = [$articles];
		foreach ($articles as &$article) {
			$article = $article instanceof Article ? $article->id : $article;
		}

		$this->andWhere(['article_attachment.article_id'=>$articles]);
		return $this;
	}

}
