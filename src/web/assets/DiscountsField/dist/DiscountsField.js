const DiscountRelations = window.DiscountRelations || {};

DiscountRelations.DiscountsField = {

	initField: function(el, options) {

		let itemHtml = function(item, escape)
		{
			return '<div class="item">'
				+ '<span class="couponCode' + (item.enabled == true ? ' enabled' : '') + '">' + escape(item.code) + '</span>'
				+ '<span class="name">' + escape(item.name) + '</span>'
				+ '</div>';
		}

		let newOptionHtml = function(data, escape)
		{
			return '<div class="item create">'
				+ '<span class="couponCode">' + escape(data.input) + '</span>'
				+ '<span class="name">Add <strong>"' + escape(data.input) + '"</strong></span>'
				+ '</div>';
		}

		jQuery(el).selectize({
			valueField: 'value',
			labelField: 'name',
			searchField: ['name', 'code'],
			maxItems: null,
			persist: false,
			options: options.currentOptions,
			render: {
				item: itemHtml,
				option: itemHtml,
				option_create: newOptionHtml
			},
			load: function(query, callback) {
				if (query.length < 2) return callback();
				Craft.postActionRequest(
					'discount-relations/discounts/search-discount-options',
					{
						search: query,
					},
					function(response, textStatus, jqXHR) {
						if (textStatus === 'success')
						{
							return callback(response);
						}
					}
				);
				return callback();
			},
			loadThrottle: 442,
			createFilter: function(input) {
				return !this.options.hasOwnProperty(input.toLowerCase());
			},
			create: function(input) {
				return {
					value: input.toLowerCase(),
					code: input,
					name: '',
					enabled: false
				};
			}
		});

	}

}

