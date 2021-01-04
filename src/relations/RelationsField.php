<?php
namespace BeSteadfast\DiscountRelations\relations;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\commerce\models\Discount;
use BeSteadfast\DiscountRelations\DiscountRelations;
use BeSteadfast\DiscountRelations\discounts\DiscountQuery;

class RelationsField extends Field
{

	/*
	 * Static
	 * ---------------------------------------------------------------------
	 */

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
	public static function supportedTranslationMethods(): array
	{
		return [
			self::TRANSLATION_METHOD_NONE,
		];
	}

	/**
	 * @inheritdoc
	 */
	public static function valueType(): string
	{
		return DiscountQuery::class;
	}

	/*
	 * Instance
	 * ---------------------------------------------------------------------
	 */

	/**
	 * @inheritdoc
	 */
	public function afterElementSave(ElementInterface $element, bool $isNew)
	{

		// Skip if nothing changed, or the element is just propagating
		if (!$element->isFieldDirty($this->handle) || $element->propagating)
		{
			parent::afterElementSave($element, $isNew);
			return;
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
	 * Returns the field’s input HTML.
	 *
	 * @param mixed $value The field’s value. This will either be the [[normalizeValue()|normalized value]],
	 * raw POST data (i.e. if there was a validation error), or null
	 * @param ElementInterface|null $element The element the field is associated with, if there is one
	 *
	 * @return string The input HTML.
	 *
	 * @see getInputHtml()
	 */
	protected function inputHtml($value, ElementInterface $element = null): string
	{

		$currentValue = null;
		$currentOptions = null;

		if ($value instanceof DiscountQuery)
		{
			$currentValue = $value;
			$value = implode(',', array_column($value->all(), 'code'));
		}

		if ($element && !$currentValue)
		{
			$currentValue = $element->getFieldValue($this->handle);
		}

		if ($currentValue instanceof DiscountQuery)
		{
			$currentOptions = array_map(
				function(Discount $discount)
				{
					return [
						'code' => $discount->code,
						'name' => $discount->name,
						'enabled' => $discount->enabled,
					];
				},
				$currentValue->all()
			);
		}

		return Craft::$app->getView()->renderTemplate('discount-relations/fieldtype/input', [
			'name' => $this->handle,
			'value' => $value,
			'field' => $this,
			'currentOptions' => $currentOptions,
		]);

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
	public function normalizeValue($value, ElementInterface $element = null)
	{

		if ($value instanceof DiscountQuery)
		{
			return $value;
		}

		$discountQuery = DiscountQuery::new();

		// No element? No related Discounts.

		if (empty($element))
		{
			// Tell the query to just not bother.
			return $discountQuery->emulateExecution();
		}

		// Getting the value from POST on a saved/saving element.

		if (is_string($value))
		{
			$codes = explode(',', $value);
			$codes = array_map('trim', $codes);
			return $discountQuery->andWhere(['in', 'code', $codes]);
		}

		if (is_array($value))
		{
			// TODO: Allow an array in addition to comma-separated list, to be more like Element Relation fields.
		}

		// Getting the value from an element.

		$relations = RelationRecord::find()
			->andWhere(['elementId' => $element->id])
			->andWhere(['fieldId' => $this->id]);

		$discountQuery->andWhere([
			'in',
			'id',
			$relations->discountIds()
		]);

		return $discountQuery;

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
	 * @inheritdoc
	 */
	public function serializeValue($value, ElementInterface $element = null)
	{
		/** @var DiscountQuery $value */
		return $value->ids();
	}

}
