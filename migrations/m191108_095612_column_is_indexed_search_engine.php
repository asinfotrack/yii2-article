<?php

namespace asinfotrack\yii2\article\migrations;

/**
 * Class 191108_095612_column_is_indexed_search_engine
 */
class m191108_095612_column_is_indexed_search_engine extends \asinfotrack\yii2\toolbox\console\Migration
{
	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$this->addColumn('{{%article}}', 'is_indexed_search_engine', $this->boolean()->notNull()->defaultValue(true)->after('type'));

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$this->dropColumn('{{%article}}', 'is_indexed_search_engine');
		return true;
	}


}
