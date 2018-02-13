<?php

/**
 * Migration adding the article tables as needed by the module
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class m180109_125617_table_article_links extends \asinfotrack\yii2\toolbox\console\Migration
{

	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$this->createAuditedTable('{{%article_link}}', [
			'id'=>$this->primaryKey(),
			'article_id'=>$this->integer()->notNull(),
			'order'=>$this->smallInteger()->notNull()->defaultValue(0),
			'is_new_tab'=>$this->boolean()->notNull()->defaultValue(1),
			'url'=>$this->string()->notNull(),
			'title'=>$this->string()->notNull(),
			'description'=>$this->text(),
		]);
		$this->addForeignKey('FK_article_link_article', '{{%article_link}}', 'article_id', '{{%article}}', 'id', 'CASCADE', 'CASCADE');

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$this->dropTable('{{%article_link}}');

		return true;
	}

}
