<?php
namespace BeSteadfast\DiscountRelations\relations;

use yii\db\ActiveQuery;

class RelationQuery extends ActiveQuery
{

	/**
	 * @return int[]
	 */
	public function discountIds()
	{
		return $this->select('discountId')->column();
	}

}
