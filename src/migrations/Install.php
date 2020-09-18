<?php
namespace TopShelfCraft\DiscountRelations\migrations;

use craft\commerce\records\Discount;
use craft\db\Migration;
use craft\records\Element;
use craft\records\Field;
use TopShelfCraft\DiscountRelations\relations\RelationRecord;

class Install extends Migration
{

	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		return $this->_addTables();
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		return $this->_removeTables();
	}

	/**
	 *
	 */
	private function _addTables()
	{

		/*
		 * Add Relations table
		 */

		if (!$this->db->tableExists(RelationRecord::tableName())) {

			$this->createTable(RelationRecord::tableName(), [

				'id' => $this->primaryKey(),

				'discountId' => $this->integer()->notNull(),
				'elementId' => $this->integer()->notNull(),
				'fieldId' => $this->integer()->notNull(),

				'dateCreated' => $this->dateTime()->notNull(),
				'dateUpdated' => $this->dateTime()->notNull(),
				'uid' => $this->uid(),

			]);

			// Delete the Relation if the Discount is deleted.

			$this->addForeignKey(
				null,
				RelationRecord::tableName(),
				['discountId'],
				Discount::tableName(),
				['id'],
				'CASCADE'
			);

			// Delete the Relation if the Element is deleted.

			$this->addForeignKey(
				null,
				RelationRecord::tableName(),
				['elementId'],
				Element::tableName(),
				['id'],
				'CASCADE'
			);

			// Delete the Relation if the Field is deleted.

			$this->addForeignKey(
				null,
				RelationRecord::tableName(),
				['fieldId'],
				Field::tableName(),
				['id'],
				'CASCADE'
			);

		}

	}

	private function _removeTables()
	{

		// Drop tables in reverse of the order we created them, to avoid foreign key constraint failures.

		$this->dropTableIfExists(RelationRecord::tableName());

		return true;

	}

}
