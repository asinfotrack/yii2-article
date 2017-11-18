<?php

/**
 * Migration adding the article tables as needed by the module
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class m171113_105717_tables_article extends \asinfotrack\yii2\toolbox\console\Migration
{

	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$this->createAuditedTable('{{%article_category}}', [
			'id'=>$this->primaryKey(),
			'lft'=>$this->integer()->notNull(),
			'rgt'=>$this->integer()->notNull(),
			'depth'=>$this->integer()->notNull(),
			'canonical'=>$this->string()->notNull()->unique(),
			'title'=>$this->string()->notNull(),
			'title_head'=>$this->string(70),
			'title_menu'=>$this->string(255),
		]);
		$this->insert('{{%article_category}}', ['lft'=>1,'rgt'=>2,	'depth'=>0,'title'=>'Tree root','canonical'=>'tree-root']);

		$this->createAuditedTable('{{%article}}', [
			'id'=>$this->primaryKey(),
			'canonical'=>$this->string()->notNull()->unique(),
			'title'=>$this->string()->notNull(),
			'title_head'=>$this->string(70),
			'title_menu'=>$this->string(255),
			'published_at'=>$this->integer(),
			'published_from'=>$this->integer(),
			'published_to'=>$this->integer(),
			'meta_description'=>$this->string(160),
			'meta_keywords'=>$this->string(),
			'intro'=>$this->text(),
			'content'=>$this->text(),
		]);

		$this->createTable('{{%article_article_category}}', [
			'article_id'=>$this->integer()->notNull(),
			'article_category_id'=>$this->integer()->notNull(),
			'PRIMARY KEY (article_id, article_category_id)',
		]);
		$this->addForeignKey('FK_article_article_category_article', '{{%article_article_category}}', 'article_id', '{{%article}}', 'id', 'CASCADE', 'CASCADE');
		$this->addForeignKey('FK_article_article_category_article_category', '{{%article_article_category}}', 'article_category_id', '{{%article_category}}', 'id', 'CASCADE', 'CASCADE');

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$this->dropTable('{{%article_article_category}}');
		$this->dropTable('{{%article}}');
		$this->dropTable('{{%article_category}}');

		return true;
	}

}
