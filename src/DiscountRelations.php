<?php
namespace BeSteadfast\DiscountRelations;

use Craft;
use craft\base\Plugin;
use craft\console\Application as ConsoleApplication;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\FileHelper;
use craft\services\Fields;
use craft\web\Application as WebApplication;
use craft\web\twig\variables\CraftVariable;
use BeSteadfast\DiscountRelations\relations\Relations;
use BeSteadfast\DiscountRelations\config\Settings;
use BeSteadfast\DiscountRelations\relations\RelationsField;
use yii\base\Event;

/**
 * Module to encapsulate Discount Relations functionality.
 *
 * This class will be available throughout the system via:
 * `Craft::$app->getModule('discount-relations')`
 *
 * @see http://www.yiiframework.com/doc-2.0/guide-structure-modules.html
 *
 * @property Relations $relations
 *
 * @method Settings getSettings()
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

		Craft::setAlias('@BeSteadfast/DiscountRelations', __DIR__);
		parent::init();

		$this->_registerEventHandlers();
		$this->_attachVariableGlobal();

		// Register controllers via namespace map

		if (Craft::$app instanceof ConsoleApplication)
		{
			$this->controllerNamespace = 'BeSteadfast\\DiscountRelations\\controllers\\console';
		}
		if (Craft::$app instanceof WebApplication)
		{
			$this->controllerNamespace = 'BeSteadfast\\DiscountRelations\\controllers\\web';
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
     * Protected methods
     * ===========================================================================
     */

	/**
	 * Creates and returns the model used to store the plugin’s settings.
	 *
	 * @return Settings|null
	 */
	protected function createSettingsModel()
	{
		return new Settings();
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
