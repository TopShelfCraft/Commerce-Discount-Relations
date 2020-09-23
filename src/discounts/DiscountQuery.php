<?php
namespace TopShelfCraft\DiscountRelations\discounts;

use craft\commerce\records\Discount;
use yii\db\ActiveQuery;

class DiscountQuery extends ActiveQuery
{

	/**
	 * @return DiscountQuery
	 */
	public function isFoo()
	{
		 return $this->andWhere(['foo' => 'bar']);
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

}
