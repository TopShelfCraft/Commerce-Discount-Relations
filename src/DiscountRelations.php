<?php
namespace topshelfcraft\discountrelations;

use Craft;
use craft\base\Plugin;
use craft\console\Application as ConsoleApplication;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\FileHelper;
use craft\services\Fields;
use craft\web\Application as WebApplication;
use craft\web\twig\variables\CraftVariable;
use TopShelfCraft\DiscountRelations\relations\Relations;
use TopShelfCraft\DiscountRelations\config\Settings;
use TopShelfCraft\DiscountRelations\relations\RelationsField;
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

		Craft::setAlias('@discount-relations', __DIR__);
		parent::init();

		$this->_registerEventHandlers();
		$this->_attachVariableGlobal();

		// Register controllers via namespace map

		if (Craft::$app instanceof ConsoleApplication)
		{
			$this->controllerNamespace = 'topshelfcraft\\discountrelations\\controllers\\console';
		}
		if (Craft::$app instanceof WebApplication)
		{
			$this->controllerNamespace = 'topshelfcraft\\discountrelations\\controllers\\web';
		}

	}

	/**
	 * @param $msg
	 * @param string $level
	 * @param string $file
	 */
	public static function log($msg, $level = 'notice', $file = 'DiscountRelations')
	{
		try
		{
			$file = Craft::getAlias('@storage/logs/' . $file . '.log');
			$log = "\n" . date('Y-m-d H:i:s') . " [{$level}]" . "\n" . print_r($msg, true);
			FileHelper::writeToFile($file, $log, ['append' => true]);
		}
		catch(\Exception $e)
		{
			Craft::error($e->getMessage());
		}
	}

	/**
	 * @param $msg
	 * @param string $level
	 * @param string $file
	 */
	public static function error($msg, $level = 'error', $file = 'DiscountRelations')
	{
		static::log($msg, $level, $file);
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
