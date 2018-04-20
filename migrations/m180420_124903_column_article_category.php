<?php

/**
 * Class m180401_124903_columns_article_refactoring
 */
class m180420_124903_column_article_category extends \asinfotrack\yii2\toolbox\console\Migration
{

	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$this->addColumn('{{%article_category}}', 'editor_item_names', $this->string()->after('title_head'));
		$this->addColumn('{{%article_category}}', 'editor_callback_class', $this->string()->after('editor_item_names'));
		$this->addColumn('{{%article_category}}', 'editor_callback_method', $this->string()->after('editor_callback_class'));

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$this->dropColumn('{{%article_category}}', 'editor_callback_method');
		$this->dropColumn('{{%article_category}}', 'editor_callback_class');
		$this->dropColumn('{{%article_category}}', 'editor_item_names');

		return true;
	}

}
