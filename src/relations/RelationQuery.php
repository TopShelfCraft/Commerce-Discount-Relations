<?php
namespace beSteadfast\DiscountRelations\relations;

use beSteadfast\DiscountRelations\discounts\DiscountQuery;
use craft\commerce\models\Discount;
use craft\helpers\ArrayHelper;
use yii\db\ActiveQuery;

class RelationQuery extends ActiveQuery
{

	/**
	 * @var array
	 */
	private $_allDiscountIdsByCode;

	/**
	 * Filters results by Discount. Accepts a single value or array of values,
	 * where value(s) may include: an ID, Discount Model, or coupon code.
	 *
	 * @param int|string|Discount|array|DiscountQuery
	 *
	 * @return self
	 */
	public function discount($value): self
	{
		if ($value instanceof DiscountQuery)
		{
			$value = $value->ids();
		}
		$values = array_map(
			[$this, '_normalizeDiscountId'],
			is_array($value) ? $value : [$value]
		);
		return $this->andWhere(['discountId' => $values]);
	}

	/**
	 * @return int[]
	 */
	public function discountIds(): array
	{
		return $this->select('discountId')->column();
	}

	/**
	 * @return int[]
	 */
	public function elementIds(): array
	{
		return $this->select('elementId')->column();
	}

	private function _getAllDiscountIdsByCode(): array
	{
		if (!$this->_allDiscountIdsByCode)
		{
			$this->_allDiscountIdsByCode = DiscountQuery::new()->indexBy('code')->ids();
		}
		return $this->_allDiscountIdsByCode;
	}

	/**
	 * Normalize a value (ID, coupon code, or Discount model) to a Discount ID.
	 * (Return zero for non-matching coupon codes or invalid types, to prevent the query from returning results from that argument.)
	 *
	 * @param int|string|Discount $value
	 */
	private function _normalizeDiscountId($value): int
	{
		if ($value instanceof Discount)
		{
			return $value->id;
		}
		if (is_string($value))
		{
			// Case-insensitive coupon code match
			$idsByCode = $this->_getAllDiscountIdsByCode();
			$couponCodeInCanonicalCase = ArrayHelper::firstWhere(array_keys($idsByCode), function($code) use ($value) {
				return strcasecmp($code, $value) == 0;
			});
			return $idsByCode[$couponCodeInCanonicalCase] ?? 0;
		}
		if (is_int($value))
		{
			return $value;
		}
		return 0;
	}

}
