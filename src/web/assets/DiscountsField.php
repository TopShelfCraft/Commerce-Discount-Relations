<?php
namespace BeSteadfast\DiscountRelations\web\assets;

use craft\web\AssetBundle;
use craft\web\assets\selectize\SelectizeAsset;

class DiscountsField extends AssetBundle
{

	/**
	 * @inheritdoc
	 */
	public function init()
	{

		$this->sourcePath = __DIR__ . '/DiscountsField/dist';

		$this->depends = [
			SelectizeAsset::class
		];

		$this->css = [
			'DiscountsField.css',
		];

		$this->js = [
			'DiscountsField.js',
		];

		parent::init();

	}

}
