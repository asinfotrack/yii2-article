<?php

/**
 * Migration adding the article tables as needed by the module
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class m180109_130217_table_article_attachments extends \asinfotrack\yii2\toolbox\console\Migration
{

	/**
	 * @inheritdoc
	 */
	public function safeUp()
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

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$this->dropTable('{{%article_attachment}}');

		return true;
	}

}
