<?php

/**
 * Migration removing the article attachment table
 *
 * @author Tom Lutzenberger, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class m180227_110420_drop_table_article_attachments extends \asinfotrack\yii2\toolbox\console\Migration
{

	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$this->dropTable('{{%article_attachment}}');

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$this->createAuditedTable('{{%article_attachment}}', [
			'id'=>$this->primaryKey(),
			'article_id'=>$this->integer()->notNull(),
			'order'=>$this->smallInteger()->notNull()->defaultValue(0),
			'title'=>$this->string()->notNull(),
			'description'=>$this->text(),
			'filename'=>$this->string()->notNull(),
			'mime_type'=>$this->string(),
			'file_size'=>$this->integer(),
		]);
		$this->addForeignKey('FK_article_attachment_article', '{{%article_attachment}}', 'article_id', '{{%article}}', 'id', 'CASCADE', 'CASCADE');

		return true;
	}

}
