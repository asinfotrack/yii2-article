<?php

/**
 * Class m180501_114903_column_menu_item_state
 */
class m180501_114903_column_menu_item_state extends \asinfotrack\yii2\toolbox\console\Migration
{

	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$this->addColumn('{{%menu_item}}', 'state', $this->smallInteger()->notNull()->defaultValue(10)->after('type'));
		$this->dropColumn('{{%menu_item}}', 'is_published');

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$this->addColumn('{{%menu_item}}', 'is_published', $this->boolean()->notNull()->defaultValue(1)->after('is_new_tab'));
		$this->dropColumn('{{%menu_item}}', 'state');

		return true;
	}

}
