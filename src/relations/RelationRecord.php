<?php
namespace TopShelfCraft\DiscountRelations\relations;

use Craft;
use TopShelfCraft\DiscountRelations\base\BaseRecord;

/**
 * A record of the relationship between an Element and a Discount, via a Field
 *
 * @property int $id
 * @property int $discountId
 * @property int $elementId
 * @property int $fieldId
 */
class RelationRecord extends BaseRecord
{

	const TableName = 'discountrelations_relations';

	/**
	 * @var array
	 */
	protected $dateTimeAttributes = [];

	/**
	 * @return RelationQuery
	 */
	public static function find()
	{
		return Craft::createObject(RelationQuery::class, [static::class]);
	}

}
