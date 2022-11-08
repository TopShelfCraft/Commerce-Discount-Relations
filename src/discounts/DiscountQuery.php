<?php
namespace TopShelfCraft\DiscountRelations\discounts;

use Craft;
use craft\commerce\db\Table;
use craft\commerce\models\Discount;
use craft\commerce\records\Discount as DiscountRecord;
use yii\db\ActiveQuery;

class DiscountQuery extends ActiveQuery
{

	public static function new(): DiscountQuery
	{
		return Craft::createObject(static::class, [DiscountRecord::class]);
	}

	public function init()
	{

		parent::init();

		// @see Discounts::_createDiscountQuery()

		$this->select('discounts.*')
			->from(['discounts' => Table::DISCOUNTS])
			->orderBy(['sortOrder' => SORT_ASC]);

		$this->addSelect([
			'dp.purchasableId',
			'dpt.categoryId',
			'dug.userGroupId',
		])->leftJoin(Table::DISCOUNT_PURCHASABLES . ' dp', '[[dp.discountId]]=[[discounts.id]]')
			->leftJoin(Table::DISCOUNT_CATEGORIES . ' dpt', '[[dpt.discountId]]=[[discounts.id]]')
			->leftJoin(Table::DISCOUNT_USERGROUPS . ' dug', '[[dug.discountId]]=[[discounts.id]]');

	}

	/**
	 * @return static
	 */
	public function search(string $param)
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
	public function ids(): array
	{
		return $this->select('discounts.id')->column();
	}

	/**
	 * @return string[]
	 */
	public function codes(): array
	{
		return $this->select('code')->column();
	}

	/**
	 * Transform the raw query data into an array of proper models.
	 *
	 * @param array $rows The raw query data
	 *
	 * @return Discount[] A list of Discount models, with purchasableIds, categoryIds, and userGroupIds loaded
	 *
	 * @see Discounts::_populateDiscounts()
	 */
	public function populate($rows): array
	{

		if (empty($rows)) {
			return [];
		}

		$discounts = [];
		$purchasables = [];
		$categories = [];
		$userGroups = [];

		foreach ($rows as $discount) {
			$id = $discount['id'];
			if ($discount['purchasableId']) {
				$purchasables[$id][] = $discount['purchasableId'];
			}

			if ($discount['categoryId']) {
				$categories[$id][] = $discount['categoryId'];
			}

			if ($discount['userGroupId']) {
				$userGroups[$id][] = $discount['userGroupId'];
			}

			unset($discount['purchasableId'], $discount['userGroupId'], $discount['categoryId']);

			if (!isset($discounts[$id])) {
				unset($discount['uid']);
				$discounts[$id] = new Discount($discount);
			}
		}

		foreach ($discounts as $id => $discount) {
			$discount->setPurchasableIds($purchasables[$id] ?? []);
			$discount->setCategoryIds($categories[$id] ?? []);
			$discount->setUserGroupIds($userGroups[$id] ?? []);
		}

		// Return a list array (rather than associative).
		// (Results might get encoded into JSON later, and we need it to be a plain array in JS rather than a keyed object.)
		return array_values($discounts);

	}

}
