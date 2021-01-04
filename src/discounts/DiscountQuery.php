<?php
namespace BeSteadfast\DiscountRelations\discounts;

use Craft;
use craft\commerce\db\Table;
use craft\commerce\models\Discount;
use craft\commerce\records\Discount as DiscountRecord;
use yii\db\ActiveQuery;

class DiscountQuery extends ActiveQuery
{

	/**
	 * @return DiscountQuery
	 */
	public static function new()
	{
		return $query = Craft::createObject(static::class, [DiscountRecord::class]);
	}

	/**
	 * @inheritdoc
	 */
	public function init()
	{

		parent::init();

		$this->select([
			'[[discounts.id]]',
			'[[discounts.name]]',
			'[[discounts.description]]',
			'[[discounts.code]]',
			'[[discounts.perUserLimit]]',
			'[[discounts.perEmailLimit]]',
			'[[discounts.totalDiscountUseLimit]]',
			'[[discounts.totalDiscountUses]]',
			'[[discounts.dateFrom]]',
			'[[discounts.dateTo]]',
			'[[discounts.purchaseTotal]]',
			'[[discounts.orderConditionFormula]]',
			'[[discounts.purchaseQty]]',
			'[[discounts.maxPurchaseQty]]',
			'[[discounts.baseDiscount]]',
			'[[discounts.baseDiscountType]]',
			'[[discounts.perItemDiscount]]',
			'[[discounts.percentDiscount]]',
			'[[discounts.percentageOffSubject]]',
			'[[discounts.excludeOnSale]]',
			'[[discounts.hasFreeShippingForMatchingItems]]',
			'[[discounts.hasFreeShippingForOrder]]',
			'[[discounts.allGroups]]',
			'[[discounts.allPurchasables]]',
			'[[discounts.allCategories]]',
			'[[discounts.categoryRelationshipType]]',
			'[[discounts.enabled]]',
			'[[discounts.stopProcessing]]',
			'[[discounts.ignoreSales]]',
			'[[discounts.sortOrder]]',
			'[[discounts.dateCreated]]',
			'[[discounts.dateUpdated]]',
		])
		->from(['discounts' => Table::DISCOUNTS])
		->orderBy(['sortOrder' => SORT_ASC]);

		$commerce = Craft::$app->getPlugins()->getStoredPluginInfo('commerce');
		if ($commerce && version_compare($commerce['version'], '3.1', '>=')) {
			$this->addSelect('[[discounts.appliedTo]]');
		}

	}

	/**
	 * @param $param
	 *
	 * @return static
	 */
	public function search($param)
	{
		$param = strtolower($param);

		return $this->andWhere([
			'or',
			['like', 'name', $param],
			['like', 'code', $param]
		]);
	}

	/**
	 * @return int[]
	 */
	public function ids()
	{
		return $this->select('id')->column();
	}

	/**
	 * @return string[]
	 */
	public function codes()
	{
		return $this->select('code')->column();
	}

	/**
	 * Transform the raw query data into an array of proper models.
	 *
	 * @param array $rows
	 *
	 * @return Discount[]
	 */
	public function populate($rows)
	{

		if (empty($rows))
		{
			return [];
		}

		return array_map(
			function($row)
			{
				return new Discount($row);
			},
			$rows
		);

	}

}
