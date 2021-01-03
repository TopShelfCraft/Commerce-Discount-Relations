<?php
namespace BeSteadfast\DiscountRelations\relations;

use yii\db\ActiveQuery;

class RelationQuery extends ActiveQuery
{

	/**
	 * @return RelationQuery
	 */
	public function isFoo()
	{
		return $this->andWhere(['foo' => 'bar']);
	}

	/**
	 * @return int[]
	 */
	public function discountIds()
	{
		return $this->select('discountId')->column();
	}

}
