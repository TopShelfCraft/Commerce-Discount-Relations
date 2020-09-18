<?php
namespace TopShelfCraft\DiscountRelations\relations;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\commerce\models\Discount;
use topshelfcraft\discountrelations\DiscountRelations;
use TopShelfCraft\DiscountRelations\discounts\DiscountQuery;

class RelationsField extends Field
{

	/**
	 * @inheritdoc
	 */
	public static function displayName(): string
	{
		return DiscountRelations::t('Discounts');
	}

	/**
	 * @inheritdoc
	 */
	public static function hasContentColumn(): bool
	{
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public static function valueType(): string
	{
		return DiscountQuery::class;
	}

	/**
	 * @inheritdoc
	 */
	public function isValueEmpty($value, ElementInterface $element): bool
	{

		/** @var DiscountQuery|Discount[] $value */
		if ($value instanceof DiscountQuery) {
			return !$value->exists();
		}
		return empty($value);

		// TODO: See BaseRelationField::isValueEmpty()

	}

	/**
	 * @inheritdoc
	 */
	public function serializeValue($value, ElementInterface $element = null)
	{
		/** @var DiscountQuery $value */
		return $value->ids();
	}

	/**
	 * @inheritdoc
	 */
	protected function searchKeywords($value, ElementInterface $element): string
	{
		/** @var DiscountQuery $value */
		return parent::searchKeywords($value->codes(), $element);
	}

	/**
	 * Returns the field’s input HTML.
	 *
	 * @param mixed $value The field’s value. This will either be the [[normalizeValue()|normalized value]],
	 * raw POST data (i.e. if there was a validation error), or null
	 * @param ElementInterface|null $element The element the field is associated with, if there is one
	 * @return string The input HTML.
	 * @see getInputHtml()
	 * @since 3.5.0
	 */
	protected function inputHtml($value, ElementInterface $element = null): string
	{

		if ($value instanceof DiscountQuery)
		{
			$value = implode(',', $value->codes());
		}

		return Craft::$app->getView()->renderTemplate('discount-relations/fieldtype/input', [
			'name' => $this->handle,
			'value' => $value,
			'field' => $this,
		]);

	}

	/**
	 * Returns the HTML that should be shown for this field in Table View.
	 *
	 * @param mixed $value The field’s value
	 * @param ElementInterface $element The element the field is associated with
	 * @return string The HTML that should be shown for this field in Table View
	 */
	public function getTableAttributeHtml($value, ElementInterface $element): string
	{
		/** @var DiscountQuery $value */
		return implode(',', $value->codes());
	}

	/**
	 * Returns the sort option array that should be included in the element’s
	 * [[\craft\base\ElementInterface::sortOptions()|sortOptions()]] response.
	 *
	 * @return array
	 * @see \craft\base\SortableFieldInterface::getSortOption()
	 * @since 3.2.0
	 */
	public function getSortOption(): array
	{
		return [];
	}

	/**
	 * @inheritdoc
	 */
	public function normalizeValue($value, ElementInterface $element = null)
	{

		if ($value instanceof DiscountQuery)
		{
			return $value;
		}

		$discountQuery = DiscountRelations::getInstance()->relations->findDiscounts();

		// No element? No related Discounts.

		if (empty($element))
		{
			return $discountQuery;
		}

		// Getting the value from POST on a saved/saving element.

		if (is_string($value))
		{
			$codes = explode(',', $value);
			$codes = array_map('trim', $codes);
			return $discountQuery->andWhere(['in', 'code', $codes]);
		}

		// Getting the value from an element.

		$relations = RelationRecord::find()
			->andWhere(['elementId' => $element->id])
			->andWhere(['fieldId' => $this->id]);

//		Craft::dd($relations->all());
//		Craft::dd($relations->discountIds());

		$discountQuery->andWhere([
			'in',
			'id',
			$relations->discountIds()
		]);

//		Craft::dd($discountQuery);

		return $discountQuery;

	}

	/**
	 * @inheritdoc
	 */
	public function afterElementSave(ElementInterface $element, bool $isNew)
	{

		// Skip if nothing changed, or the element is just propagating
		if (!$element->isFieldDirty($this->handle) || $element->propagating)
		{
			return parent::afterElementSave($element, $isNew);
		}

		/** @var DiscountQuery $value */
		$value = $element->getFieldValue($this->handle);

		$targetIds = $value->ids();

		/** @var int|int[]|false|null $targetIds */
		DiscountRelations::getInstance()->relations->saveRelations($this, $element, $targetIds);

		// Reset the field value if this is a new element
		if ($isNew) {
			$element->setFieldValue($this->handle, null);
		}

		parent::afterElementSave($element, $isNew);

	}

}
