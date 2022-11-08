<?php
namespace TopShelfCraft\DiscountRelations\relations;

use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
use craft\helpers\Db;

class Relations extends Component
{

	/**
	 * Saves some relations for a field.
	 *
	 * @param RelationsField $field
	 * @param ElementInterface $element
	 * @param array $targetIds
	 *
	 * @throws \Throwable
	 * @throws \yii\db\Exception
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
