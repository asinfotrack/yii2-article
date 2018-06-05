<?php

/**
 * Class m180501_114903_column_menu_item_state
 */
class m180511_091530_column_menu_item_article_category_id extends \asinfotrack\yii2\toolbox\console\Migration
{

	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$this->addColumn('{{%menu_item}}', 'article_category_id', $this->integer()->after('article_id'));
		$this->addForeignKey('FK_menu_item_article_category', '{{%menu_item}}', ['article_category_id'], '{{%article_category}}', ['id'], 'SET NULL', 'CASCADE');

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$this->dropForeignKey('FK_menu_item_article_category', '{{%menu_item}}');
		$this->dropColumn('{{%menu_item}}','article_category_id');
		return true;
	}

}
