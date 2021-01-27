<?php
namespace beSteadfast\DiscountRelations\controllers\web;

use beSteadfast\DiscountRelations\discounts\DiscountQuery;
use craft\commerce\models\Discount;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use craft\web\Controller;

class DiscountsController extends Controller
{

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		$this->requirePermission('commerce-managePromotions');
		parent::init();
	}

	public function actionSearchDiscountOptions()
	{

		$search = trim($this->request->getParam('search'));

		if ($search === '')
		{
			return $this->asJson([]);
		}

		$query = DiscountQuery::new()->search(Db::escapeParam($search));

		$data = array_map(
			function(Discount $discount)
			{
				return [
					'value' => StringHelper::toLowerCase($discount->code ?: ''),
					'name' => $discount->name,
					'code' => $discount->code,
					'enabled' => $discount->enabled,
				];
			},
			$query->all()
		);

		return $this->asJson($data);

	}

}
