jQuery(document).ready(function ($) {
	$(".blpwpsh-selector").select2({
		placeholder: "Select Page",
		allowClear: true,
		ajax: {
			url: ajaxurl,
			dataType: "json",
			delay: 250,
			data: function (params) {
				return {
					search_key: params.term,
					action: "bpwpsh_get_pages",
				};
			},
			processResults: function (data) {
				var options = [];
				if (data) {
					$.each(data, function (index, text) {
						options.push({ id: text[0], text: text[1] });
					});
				}
				return {
					results: options,
				};
			},
			cache: true,
		},
	});
});
