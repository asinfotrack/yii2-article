<?php

/**
 * Class m180420_124903_columns_menu
 */
class m180424_124903_columns_menu extends \asinfotrack\yii2\toolbox\console\Migration
{

	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$this->addColumn('{{%menu_item}}', 'state_id', $this->integer()->notNull()->after('is_new_tab'));
		$this->addForeignKey('FK_menu_item_state_id', '{{%menu_item}}', 'state_id', '{{%state}}', 'id', 'RESTRICT', 'CASCADE');
		$this->dropColumn('{{%menu_item}}', 'is_published');
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$this->addColumn('{{%menu_item}}', 'is_published', $this->boolean()->notNull()->defaultValue(1)->after('is_new_tab'));
		$this->dropColumn('{{%menu_item}}', 'state_id');
		return true;
	}

}
