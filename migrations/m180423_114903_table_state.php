<?php

/**
 * Class m180420_124903_columns_menu
 */
class m180423_114903_table_state extends \asinfotrack\yii2\toolbox\console\Migration
{

	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$this->createAuditedTable('{{%state}}', [
			'id'=>$this->primaryKey(),
			'name'=>$this->string()->notNull(),
		]);
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$this->dropTable('{{%state}}');
		return true;
	}

}
