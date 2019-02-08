<?php
namespace asinfotrack\yii2\article\components;

use yii\helpers\ArrayHelper;
use HTMLPurifier;
use HTMLPurifier_Config;
use HTMLPurifier_HTMLDefinition;

class Purifier extends \yii\base\Component {

	/** @var HTMLPurifier */
	private $purifierInstance;

	public $basicElements = [];
	public $additionalElements = [];
	public $additionalConfig = [];

	public function init()
	{
		$this->purifierInstance = $this->createPurifierInstance();
	}

	/**
	 * Getter for purifier instance
	 *
	 * @return HTMLPurifier
	 */
	public function getPurifierInstance()
	{
		return $this->purifierInstance;
	}

	/**
	 * Creates a purifier instance
	 * Loads config from config.php and sets it on the html purifier
	 * @return HTMLPurifier
	 */
	private function createPurifierInstance()
	{
		$basicConfig = [
			'HTML.DefinitionID'=>'asi-purifier',
			'HTML.DefinitionRev'=>2
		];
		$configArray = ArrayHelper::merge($basicConfig, $this->additionalConfig);

		//prepare html purifier
		$config = HTMLPurifier_Config::create($configArray);

		/** @var HTMLPurifier_HTMLDefinition $def result is null when already a definition is cached*/
		$def = $config->maybeGetRawHTMLDefinition();
		if ($def !== null) {
			$allElements = ArrayHelper::merge($this->basicElements, $this->additionalElements);
			$addedElements = [];
			foreach ($allElements as $el) {
				if (in_array($el[0], $addedElements) || !isset($el[0]) || !isset($el[1]) || !isset($el[2]) || !isset($el[3])) continue;
				isset($el[4]) ? $def->addElement($el[0], $el[1], $el[2], $el[3], $el[4]) : $def->addElement($el[0], $el[1], $el[2], $el[3]);
				$addedElements[] = $el[0];
			}
		}

		return new HTMLPurifier($config);
	}

}
