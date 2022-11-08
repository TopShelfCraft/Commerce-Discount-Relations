<?php
namespace TopShelfCraft\DiscountRelations;

use TopShelfCraft\DiscountRelations\relations\Relations;
use TopShelfCraft\DiscountRelations\relations\RelationsField;
use Craft;
use craft\base\Plugin;
use craft\console\Application as ConsoleApplication;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;
use craft\web\Application as WebApplication;
use craft\web\twig\variables\CraftVariable;
use yii\base\Event;

/**
 * Module to encapsulate Discount Relations functionality.
 *
 * This class will be available throughout the system via:
 * `Craft::$app->getModule('commerce-discount-relations')`
 *
 * @see http://www.yiiframework.com/doc-2.0/guide-structure-modules.html
 *
 * @property Relations $relations
 */
class DiscountRelations extends Plugin
{

	/**
	 * @var bool
	 */
	public $hasCpSettings = false;

	/**
	 * @var bool
	 */
	public $hasCpSection = false;

	/**
	 * @var string
	 */
	public $schemaVersion = '0.0.1.0';


	/*
     * Public methods
     * ===========================================================================
     */

	public function __construct($id, $parent = null, array $config = [])
	{

		$config['components'] = [
			'relations' => Relations::class,
		];

		parent::__construct($id, $parent, $config);

	}

	/**
	 * Initializes the module.
	 */
	public function init()
	{

		Craft::setAlias('@TopShelfCraft/DiscountRelations', __DIR__);
		parent::init();

		$this->_registerEventHandlers();
		$this->_attachVariableGlobal();

		// Register controllers via namespace map

		if (Craft::$app instanceof ConsoleApplication)
		{
			$this->controllerNamespace = 'TopShelfCraft\\DiscountRelations\\controllers\\console';
		}
		if (Craft::$app instanceof WebApplication)
		{
			$this->controllerNamespace = 'TopShelfCraft\\DiscountRelations\\controllers\\web';
		}

	}

	/**
	 * @param $message
	 * @param array $params
	 * @param null $language
	 *
	 * @return string
	 */
	public static function t($message, $params = [], $language = null)
	{
		return Craft::t(self::getInstance()->getHandle(), $message, $params, $language);
	}

	/*
     * Private methods
     * ===========================================================================
     */

	/**
	 * Makes the plugin instance available to Twig via the `craft.discountRelations` variable.
	 */
	private function _attachVariableGlobal() {

		Event::on(
			CraftVariable::class,
			CraftVariable::EVENT_INIT,
			function (Event $event) {
				/** @var CraftVariable $variable **/
				$variable = $event->sender;
				$variable->set('discountRelations', $this);
			}
		);

	}

	/**
	 * Registers handlers for various Event hooks
	 */
	private function _registerEventHandlers()
	{

		Event::on(
			Fields::class,
			Fields::EVENT_REGISTER_FIELD_TYPES,
			function(RegisterComponentTypesEvent $event) {
				$event->types[] = RelationsField::class;
			}
		);

	}

}
