<?php

/**
 * Class m180401_124903_columns_article_refactoring
 */
class m180401_124903_columns_article_refactoring extends \asinfotrack\yii2\toolbox\console\Migration
{

	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		//article
		$this->dropColumn('{{%article}}', 'title_menu');
		$this->addColumn('{{%article}}', 'title_internal', $this->string(255)->after('type'));
		$this->addColumn('{{%article}}', 'subtitle', $this->string(255)->after('title_head'));

		//article category
		$this->dropColumn('{{%article_category}}', 'title_menu');
		$this->addColumn('{{%article_category}}', 'title_internal', $this->string(255)->after('canonical'));

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		//article
		$this->addColumn('{{%article}}', 'title_menu', $this->string(255)->after('title_head'));
		$this->dropColumn('{{%article}}', 'subtitle');
		$this->dropColumn('{{%article}}', 'title_internal');

		//article category
		$this->addColumn('{{%article_category}}', 'title_menu', $this->string(255)->after('title_head'));
		$this->dropColumn('{{%article_category}}', 'title_internal');

		return true;
	}

}
