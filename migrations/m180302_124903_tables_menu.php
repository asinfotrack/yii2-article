<?php

/**
 * Class m180302_124903_tables_menu
 */
class m180302_124903_tables_menu extends \asinfotrack\yii2\toolbox\console\Migration
{

	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$this->createAuditedTable('{{%menu_item}}', [
			'id'=>$this->primaryKey(),
			'tree'=>$this->integer(),
			'lft'=>$this->integer()->notNull(),
			'rgt'=>$this->integer()->notNull(),
			'depth'=>$this->integer()->notNull(),
			'type'=>$this->smallInteger(),
			'icon'=>$this->string(),
			'label'=>$this->string()->notNull(),
			'is_new_tab'=>$this->boolean()->notNull()->defaultValue(0),

			'article_id'=>$this->integer(),
			'route'=>$this->string(),
			'params'=>$this->string(),

			'active_regex'=>$this->string(2048),
			'visible_item_names'=>$this->string(),
			'visible_callback_class'=>$this->string(),
			'visible_callback_method'=>$this->string(),
		]);
		$this->addForeignKey('FK_menu_item_article', '{{%menu_item}}', ['article_id'], '{{%article}}', ['id'], 'SET NULL', 'CASCADE');
		$this->createIndex('IN_menu_item_lft', '{{%menu_item}}', ['lft']);
		$this->createIndex('IN_menu_item_rgt', '{{%menu_item}}', ['rgt']);

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$this->dropTable('{{%menu_item}}');

		return true;
	}

}
