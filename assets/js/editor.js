(function ($) {
	"use strict";

	/**
	 * Initializes all the handlers for the query repeater controls.
	 * This function is designed to be called once the query section panel is activated.
	 *
	 * @param {jQuery} $panel The jQuery object for the widget's editor panel.
	 */
	var initializeQueryRepeaterHandlers = function ($panel) {
		var $repeater = $panel.find(".elementor-control-query_repeater");

		if (!$repeater.length) {
			return;
		}

		/**
		 * Fetches ACF field choices via AJAX and populates the value select dropdown.
		 *
		 * @param {jQuery} $acfSelect The jQuery object for the ACF field select dropdown.
		 */
		var fetchAndPopulateAcfValues = function ($acfSelect) {
			var $repeaterRow = $acfSelect.closest(".elementor-repeater-row-controls");
			var $valueSelect = $repeaterRow.find('select[data-setting="acf_meta_value_choice"]');
			var selectedValue = $acfSelect.val();

			if (!selectedValue) {
				$valueSelect.empty().prop("disabled", true);
				return;
			}

			$valueSelect
				.prop("disabled", true)
				.empty()
				.append($("<option>", { text: "Loading..." }));

			$.ajax({
				url: dgcf_editor_data.ajax_url,
				type: "POST",
				data: {
					action: "dgcf_get_acf_choices",
					nonce: dgcf_editor_data.nonce,
					field_key: selectedValue,
				},
				success: function (response) {
					$valueSelect.empty().prop("disabled", false);
					if (response.success && response.data) {
						$valueSelect.append($("<option>", { value: "", text: "Select a value" }));
						$.each(response.data, function (value, label) {
							$valueSelect.append($("<option>", { value: value, text: label }));
						});
					} else {
						$valueSelect.append($("<option>", { text: "No values found", value: "" })).prop("disabled", true);
					}
				},
				error: function () {
					$valueSelect
						.empty()
						.append($("<option>", { text: "Error fetching values", value: "" }))
						.prop("disabled", true);
				},
			});
		};

		/**
		 * Updates the repeater item's title based on the current selections.
		 * This function now directly targets the title button.
		 *
		 * @param {jQuery} $changedElement The jQuery object of the control that was changed.
		 */
		var updateRepeaterTitle = function ($changedElement) {
			var $repeaterRow = $changedElement.closest(".elementor-repeater-fields");
			var $titleButton = $repeaterRow.find(".elementor-repeater-row-item-title");
			var $controls = $repeaterRow.find(".elementor-repeater-row-controls");

			var queryType = $controls.find('select[data-setting="query_type"]').val();
			var newTitle = "New Query";

			if (queryType === "acf") {
				var $acfSelect = $controls.find('select[data-setting="acf_meta_key"]');
				if ($acfSelect.val()) {
					newTitle = $acfSelect
						.find("option:selected")
						.text()
						.replace(/\s\(.*\)/, "")
						.trim();
				}
			} else if (queryType === "taxonomy") {
				var $taxSelect = $controls.find('select[data-setting="taxonomy"]');
				if ($taxSelect.val()) {
					newTitle = $taxSelect.find("option:selected").text().trim();
				}
			}

			$titleButton.text(newTitle);
		};

		// --- Event Delegation ---
		$repeater.on("change", 'select[data-setting="acf_meta_key"]', function (event) {
			var $select = $(event.currentTarget);
			updateRepeaterTitle($select);
			fetchAndPopulateAcfValues($select);
		});

		$repeater.on("change", 'select[data-setting="taxonomy"], select[data-setting="query_type"]', function (event) {
			updateRepeaterTitle($(event.currentTarget));
		});

		// Trigger for existing items when the panel opens
		$repeater.find('select[data-setting="acf_meta_key"], select[data-setting="taxonomy"]').each(function () {
			updateRepeaterTitle($(this));
		});
	};

	// --- Elementor Editor Hooks ---
	$(window).on("elementor/frontend/init", function () {
		// Hook for when the query section is activated
		elementor.channels.editor.on("editor:widget:dgcpf_filtered_loop:section_query:activated", function (panel) {
			console.log("Query section activated for dgcpf_filtered_loop.", panel);
			var $widgetPanel = panel.$el.closest(".elementor-panel-page");
			initializeQueryRepeaterHandlers($widgetPanel);
		});
	});
})(jQuery);
