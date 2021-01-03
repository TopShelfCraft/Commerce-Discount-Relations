<?php
namespace BeSteadfast\DiscountRelations\relations;

use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
use craft\commerce\records\Discount;
use craft\fields\BaseRelationField;
use craft\helpers\Db;
use BeSteadfast\DiscountRelations\discounts\DiscountQuery;

class Relations extends Component
{

	/**
	 * @return DiscountQuery
	 *
	 * @throws \yii\base\InvalidConfigException
	 */
	public function findDiscounts()
	{
		return Craft::createObject(DiscountQuery::class, [Discount::class]);
	}

	/**
	 * Saves some relations for a field.
	 *
	 * @param BaseRelationField $field
	 * @param ElementInterface $source
	 * @param array $targetIds
	 * @throws \Throwable
	 */
	public function saveRelations(RelationsField $field, ElementInterface $element, array $targetIds)
	{

		if (!is_array($targetIds)) {
			$targetIds = [];
		}

		// Get the unique, indexed target IDs, set to their 0-indexed sort orders
		$targetIds = array_values(array_unique(array_filter($targetIds)));

		$transaction = Craft::$app->getDb()->beginTransaction();

		try {

			Db::delete(RelationRecord::tableName(), ['fieldId' => $field->id, 'elementId' => $element->id]);

			if (!empty($targetIds)) {

				$values = [];
				foreach ($targetIds as $targetId) {
					$values[] = [
						$field->id,
						$element->id,
						$targetId,
					];
				}

				Db::batchInsert(RelationRecord::tableName(), ['fieldId', 'elementId', 'discountId'], $values);

			}

			$transaction->commit();

		} catch (\Throwable $e) {

			$transaction->rollBack();
			throw $e;

		}

	}

}
