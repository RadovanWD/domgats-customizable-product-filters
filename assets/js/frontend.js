(function ($) {
	"use strict";

	var DomGats_Filtered_Loop_Widget_Handler = function ($scope, $) {
		const self = this;
		self.$widgetContainer = $scope;
		self.$filtersWrapper = self.$widgetContainer.find(".dgcpf-filters-wrapper");
		self.$loopContainer = self.$widgetContainer.find(".dgcpf-loop-container");
		self.$loadMoreContainer = self.$widgetContainer.find(".dgcpf-load-more-container");
		self.$loadMoreButton = self.$loadMoreContainer.find(".dgcpf-load-more-button");
		self.$clearAllButton = self.$filtersWrapper.find(".dgcpf-clear-all-filters-button");
		self.settings = self.$widgetContainer.data("settings") || {};
		self.templateId = self.settings.template_id;
		self.widgetId = self.$widgetContainer.data("id");
		self.nonce = self.$widgetContainer.data("nonce"); // This needs to be added in render()
		self.selectedTermsByTaxonomy = {};
		self.selectedAcfFields = {};
		self.currentPage = 1;
		self.maxPages = parseInt(self.settings.max_num_pages || 1);
		self.isLoading = false;
		self.flickityInstance = null;

		self.init = function () {
			self.bindEvents();
			self.initializeCarousel();
			self.updateLoadMoreButtonVisibility();
		};

		self.bindEvents = function () {
			// Bind events for dropdowns, checkboxes, and radio buttons
			self.$filtersWrapper.on("change", ".dgcpf-filter-dropdown, .dgcpf-filter-checkbox, .dgcpf-filter-radio", self.handleFilterChange);
			self.$loadMoreButton.on("click", self.handleLoadMore);
			self.$clearAllButton.on("click", self.handleClearAll);
		};

		self.handleFilterChange = function () {
			self.currentPage = 1; // Reset page on filter change
			self.collectFilterData();
			self.fetchPosts(false); // false = don't append, replace content
		};

		self.handleLoadMore = function () {
			if (!self.isLoading && self.currentPage < self.maxPages) {
				self.currentPage++;
				self.fetchPosts(true); // true = append results
			}
		};

		self.handleClearAll = function () {
			// Reset all filter inputs to their default state
			self.$filtersWrapper.find("select").val("");
			self.$filtersWrapper.find('input[type="checkbox"]').prop("checked", false);
			self.$filtersWrapper.find('input[type="radio"]').prop("checked", false);
			self.$filtersWrapper.find('input[type="radio"][value=""]').prop("checked", true); // Check the 'All' radio
			self.handleFilterChange();
		};

		self.collectFilterData = function () {
			self.selectedTermsByTaxonomy = {};
			self.selectedAcfFields = {};

			self.$filtersWrapper.find("[data-taxonomy]").each(function () {
				var $filterGroup = $(this);
				var taxonomy = $filterGroup.data("taxonomy");
				var displayAs = $filterGroup.data("display-as");
				var selectedValues = [];

				if (displayAs === "dropdown") {
					var val = $filterGroup.find("select").val();
					if (val) selectedValues.push(val);
				} else if (displayAs === "checkbox") {
					$filterGroup.find("input:checked").each(function () {
						selectedValues.push($(this).val());
					});
				} else if (displayAs === "radio") {
					var val = $filterGroup.find("input:checked").val();
					if (val) selectedValues.push(val);
				}

				if (selectedValues.length > 0) {
					self.selectedTermsByTaxonomy[taxonomy] = selectedValues;
				}
			});
			// Similar logic for ACF fields would go here

			// Show/hide clear all button
			if (Object.keys(self.selectedTermsByTaxonomy).length > 0 || Object.keys(self.selectedAcfFields).length > 0) {
				self.$clearAllButton.show();
			} else {
				self.$clearAllButton.hide();
			}
		};

		self.fetchPosts = function (appendResults) {
			if (self.isLoading) return;
			self.isLoading = true;
			self.$loopContainer.addClass("loading");

			$.ajax({
				url: dgcf_editor_data.ajax_url, // Use localized data
				type: "POST",
				data: {
					action: "dgcf_filter_products",
					nonce: self.nonce, // Use nonce from data attribute
					settings: JSON.stringify(self.settings),
					template_id: self.templateId,
					taxonomies: self.selectedTermsByTaxonomy,
					acf_fields: self.selectedAcfFields,
					page: self.currentPage,
				},
				success: function (response) {
					if (response.success) {
						self.maxPages = response.data.max_num_pages;
						const newHtml = $(response.data.html);

						if (self.settings.layout_type === "carousel") {
							self.destroyCarousel();
							self.$loopContainer.html(newHtml);
							self.initializeCarousel();
						} else {
							if (appendResults) {
								self.$loopContainer.append(newHtml);
							} else {
								self.$loopContainer.html(newHtml);
							}
						}
						self.updateLoadMoreButtonVisibility();
					}
				},
				error: function () {
					self.$loopContainer.html('<p class="dgcpf-error-message">Error loading products.</p>');
				},
				complete: function () {
					self.isLoading = false;
					self.$loopContainer.removeClass("loading");
				},
			});
		};

		self.updateLoadMoreButtonVisibility = function () {
			if (self.currentPage >= self.maxPages) {
				self.$loadMoreButton.hide();
				if (self.settings.no_more_products_text) {
					self.$loadMoreContainer.append('<span class="dgcpf-no-more-posts">' + self.settings.no_more_products_text + "</span>");
				}
			} else {
				self.$loadMoreButton.show();
				self.$loadMoreContainer.find(".dgcpf-no-more-posts").remove();
			}
		};

		self.initializeCarousel = function () {
			if (self.settings.layout_type !== "carousel" || !$.fn.flickity) return;

			self.$loopContainer.imagesLoaded(function () {
				self.flickityInstance = new Flickity(self.$loopContainer[0], {
					cellSelector: ".elementor-loop-item",
					prevNextButtons: self.settings.carousel_nav_buttons === "yes",
					pageDots: self.settings.carousel_page_dots === "yes",
					wrapAround: self.settings.carousel_wrap_around === "yes",
					autoPlay: self.settings.carousel_autoplay === "yes" ? parseInt(self.settings.carousel_autoplay_interval) : false,
					draggable: self.settings.carousel_draggable === "yes",
					adaptiveHeight: self.settings.carousel_adaptive_height === "yes",
					cellAlign: self.settings.carousel_cell_align,
				});
			});
		};

		self.destroyCarousel = function () {
			if (self.flickityInstance) {
				self.flickityInstance.destroy();
				self.flickityInstance = null;
			}
		};

		self.init();
	};
})(jQuery);
