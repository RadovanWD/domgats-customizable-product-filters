<?php
namespace DomGats\ProductFilter\Widgets;

use \WP_Query;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Group_Control_Typography;
use \Elementor\Repeater;
use \Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class DomGats_Filtered_Loop_Widget extends Widget_Base {

    public function get_name() {
        return 'dgcpf_filtered_loop';
    }

    public function get_title() {
        return esc_html__( 'DomGats Filtered Loop', 'domgats-customizable-product-filters' );
    }

    public function get_icon() {
        return 'eicon-loop-builder';
    }

    public function get_categories() {
        return [ 'woocommerce-elements' ];
    }

    public function get_script_depends() {
        return [
            'dgcf-frontend-js',
            'flickity-js',
            'imagesloaded',
        ];
    }

    public function get_style_depends() {
        return [
            'dgcf-frontend-css',
            'flickity-css',
        ];
    }

    private function _is_acf_active() {
        return class_exists('ACF');
    }

    protected function register_controls() {
        $this->register_content_controls();
        $this->register_style_controls();
    }

    protected function register_content_controls() {
        // --- Layout Section ---
        $this->start_controls_section(
            'section_layout',
            [
                'label' => esc_html__( 'Layout', 'domgats-customizable-product-filters' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'template_id',
            [
                'label'   => esc_html__( 'Choose a Template', 'domgats-customizable-product-filters' ),
                'type'    => Controls_Manager::SELECT,
                'options' => $this->_get_loop_templates(),
                'default' => '',
                'frontend_available' => true,
            ]
        );

        $this->add_responsive_control(
            'layout_type',
            [
                'label' => esc_html__( 'Render As', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'grid' => [ 'title' => esc_html__( 'Grid', 'domgats-customizable-product-filters' ), 'icon' => 'eicon-thumbnails-grid' ],
                    'carousel' => [ 'title' => esc_html__( 'Carousel', 'domgats-customizable-product-filters' ), 'icon' => 'eicon-post-slider' ],
                ],
                'default' => 'grid',
                'toggle' => false,
                'frontend_available' => true,
            ]
        );

        $this->add_responsive_control(
            'columns',
            [
                'label' => esc_html__( 'Columns (Grid)', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::NUMBER, 'min' => 1, 'max' => 6, 'default' => 3,
                'condition' => [ 'layout_type' => 'grid' ],
                'selectors' => [ '{{WRAPPER}} .dgcpf-loop-container.dgcpf-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);' ],
                'frontend_available' => true,
            ]
        );

        $this->add_responsive_control(
            'columns_carousel',
            [
                'label' => esc_html__( 'Columns (Carousel)', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::NUMBER, 'min' => 1, 'max' => 6, 'default' => 3,
                'condition' => [ 'layout_type' => 'carousel' ],
                'frontend_available' => true,
                'selectors' => [
                    '{{WRAPPER}} .dgcpf-loop-container.dgcpf-carousel .elementor-loop-item' => 'width: calc(100% / {{VALUE}});'
                ],
            ]
        );

        $this->add_responsive_control(
            'horizontal_gap',
            [
                'label' => esc_html__( 'Horizontal Gap', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
                'default' => [ 'unit' => 'px', 'size' => 20 ],
                'selectors' => [
                    '{{WRAPPER}} .dgcpf-loop-container.dgcpf-grid' => 'column-gap: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .dgcpf-loop-container.dgcpf-carousel .elementor-loop-item' => 'padding-left: calc({{SIZE}}{{UNIT}} / 2); padding-right: calc({{SIZE}}{{UNIT}} / 2);',
                ],
                'frontend_available' => true,
            ]
        );

        $this->add_responsive_control(
            'vertical_gap',
            [
                'label' => esc_html__( 'Vertical Gap', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
                'default' => [ 'unit' => 'px', 'size' => 20 ],
                'selectors' => [ '{{WRAPPER}} .dgcpf-loop-container.dgcpf-grid' => 'row-gap: {{SIZE}}{{UNIT}};' ],
                'condition' => [ 'layout_type' => 'grid' ],
                'frontend_available' => true,
            ]
        );
        
        $this->end_controls_section();

        // --- Query Section ---
		$this->start_controls_section(
			'section_query',
			[
				'label' => esc_html__( 'Query', 'domgats-customizable-product-filters' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'query_label', [
				'type' => Controls_Manager::HIDDEN,
				'default' => esc_html__( 'New Query', 'domgats-customizable-product-filters' ),
			]
		);

		$repeater->add_control(
			'query_type',
			[
				'label' => esc_html__( 'Query Type', 'domgats-customizable-product-filters' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'acf',
				'options' => [
					'acf' => esc_html__( 'ACF Meta Query', 'domgats-customizable-product-filters' ),
					'taxonomy' => esc_html__( 'Taxonomy Query', 'domgats-customizable-product-filters' ),
				],
			]
		);

		// ACF Controls
		$repeater->add_control(
			'acf_meta_key',
			[
				'label' => esc_html__( 'ACF Field', 'domgats-customizable-product-filters' ),
				'type' => Controls_Manager::SELECT,
				'options' => $this->_get_all_acf_choice_field_keys(),
				'default' => '',
				'label_block' => true,
				'condition' => [
					'query_type' => 'acf',
				],
			]
		);

		$repeater->add_control(
			'acf_meta_value_choice',
			[
				'label' => esc_html__( 'Field Value', 'domgats-customizable-product-filters' ),
				'type' => Controls_Manager::SELECT,
				'options' => [], // Populated by JS
				'label_block' => true,
				'condition' => [
					'query_type' => 'acf',
				],
			]
		);

		$repeater->add_control(
			'acf_meta_compare',
			[
				'label' => esc_html__( 'Comparison', 'domgats-customizable-product-filters' ),
				'type' => Controls_Manager::SELECT,
				'default' => '=',
				'options' => [
					'=' => esc_html__( 'Equal to', 'domgats-customizable-product-filters' ),
					'!=' => esc_html__( 'Not Equal to', 'domgats-customizable-product-filters' ),
				],
				'condition' => [
					'query_type' => 'acf',
				],
			]
		);

		// Taxonomy Controls
		$repeater->add_control(
			'taxonomy',
			[
				'label' => esc_html__( 'Taxonomy', 'domgats-customizable-product-filters' ),
				'type' => Controls_Manager::SELECT,
				'options' => $this->_get_all_taxonomies(),
				'label_block' => true,
				'condition' => [
					'query_type' => 'taxonomy',
				],
			]
		);

		$repeater->add_control(
			'taxonomy_term',
			[
				'label' => esc_html__( 'Term', 'domgats-customizable-product-filters' ),
				'type' => 'query',
				'label_block' => true,
				'autocomplete' => [
					'object' => 'tax',
				],
				'condition' => [
					'query_type' => 'taxonomy',
				],
			]
		);

		$repeater->add_control(
			'relation',
			[
				'label' => esc_html__( 'Relation to Next', 'domgats-customizable-product-filters' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'AND',
				'options' => [
					'AND' => esc_html__( 'AND', 'domgats-customizable-product-filters' ),
					'OR' => esc_html__( 'OR', 'domgats-customizable-product-filters' ),
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'query_repeater',
			[
				'label'   => esc_html__( 'Initial Query Filters', 'domgats-customizable-product-filters' ),
				'type'    => Controls_Manager::REPEATER,
				'fields'  => $repeater->get_controls(),
				'title_field' => '{{{ query_label }}}',
				'separator' => 'before',
			]
		);

        $this->end_controls_section();

        // --- Filters Section ---
        $this->start_controls_section(
            'section_filters',
            [
                'label' => esc_html__( 'Filters', 'domgats-customizable-product-filters' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $filters_repeater = new Repeater();
        $filters_repeater->add_control(
            'filter_type',
            [
                'label'   => esc_html__( 'Filter By', 'domgats-customizable-product-filters' ),
                'type'    => Controls_Manager::SELECT,
                'options' => [ 
                    'taxonomy' => esc_html__( 'Taxonomy', 'domgats-customizable-product-filters' ), 
                    'acf' => esc_html__( 'Custom Field (ACF)', 'domgats-customizable-product-filters' ) 
                ],
                'default' => 'taxonomy',
                'frontend_available' => true,
            ]
        );
        $filters_repeater->add_control(
            'taxonomy_name',
            [
                'label'   => esc_html__( 'Taxonomy', 'domgats-customizable-product-filters' ),
                'type'    => Controls_Manager::SELECT,
                'options' => $this->_get_all_taxonomies(),
                'condition' => [ 'filter_type' => 'taxonomy' ],
                'frontend_available' => true,
            ]
        );
        $filters_repeater->add_control(
            'acf_field_key',
            [
                'label'     => esc_html__( 'ACF Field', 'domgats-customizable-product-filters' ),
                'type'      => Controls_Manager::SELECT,
                'options'   => $this->_get_all_acf_choice_field_keys(),
                'condition' => [ 'filter_type' => 'acf' ],
                'frontend_available' => true,
            ]
        );
        $filters_repeater->add_control(
            'display_as',
            [
                'label'   => esc_html__( 'Display As', 'domgats-customizable-product-filters' ),
                'type'    => Controls_Manager::SELECT,
                'options' => [
                    'dropdown' => esc_html__( 'Dropdown', 'domgats-customizable-product-filters' ), 
                    'checkbox' => esc_html__( 'Checkboxes', 'domgats-customizable-product-filters' ),
                    'radio'    => esc_html__( 'Radio Buttons', 'domgats-customizable-product-filters' ),
                ],
                'default' => 'dropdown',
                'frontend_available' => true,
            ]
        );
        $this->add_control(
            'filters_repeater',
            [
                'label'   => esc_html__( 'Filters', 'domgats-customizable-product-filters' ),
                'type'    => Controls_Manager::REPEATER,
                'fields'  => $filters_repeater->get_controls(),
                'title_field' => '{{{ filter_type }}} - {{{ taxonomy_name || acf_field_key }}}',
                'default' => [ [ 'filter_type' => 'taxonomy', 'taxonomy_name' => 'product_tag', 'display_as' => 'dropdown' ] ],
                'frontend_available' => true,
            ]
        );
        $this->add_control(
            'filter_logic',
            [
                'label'   => esc_html__( 'Filter Logic', 'domgats-customizable-product-filters' ),
                'type'    => Controls_Manager::SELECT,
                'options' => [ 'AND' => esc_html__( 'AND', 'domgats-customizable-product-filters' ), 'OR'  => esc_html__( 'OR', 'domgats-customizable-product-filters' ) ],
                'default' => 'AND',
                'frontend_available' => true,
            ]
        );

        $this->end_controls_section();

        // --- Pagination & Carousel Section ---
        $this->start_controls_section(
            'section_pagination_carousel',
            [
                'label' => esc_html__( 'Pagination & Carousel', 'domgats-customizable-product-filters' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'enable_load_more',
            [
                'label' => esc_html__( 'Enable Load More', 'domgats-customizable-product-filters' ),
                'type'  => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes',
                'condition' => [ 'layout_type' => 'grid' ],
                'frontend_available' => true,
            ]
        );
        $this->add_control(
            'posts_per_page',
            [
                'label' => esc_html__( 'Posts Per Page (Load More)', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::NUMBER, 'min' => 1, 'default' => 9,
                'condition' => [ 'enable_load_more' => 'yes', 'layout_type' => 'grid' ],
                'frontend_available' => true,
            ]
        );
        $this->add_control(
            'load_more_button_text',
            [
                'label' => esc_html__( 'Load More Button Text', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'Load More', 'domgats-customizable-product-filters' ),
                'condition' => [ 'enable_load_more' => 'yes', 'layout_type' => 'grid' ],
                'frontend_available' => true,
            ]
        );
        $this->add_control(
            'no_more_products_text',
            [
                'label' => esc_html__( 'No More Products Text', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'No More Products', 'domgats-customizable-product-filters' ),
                'condition' => [ 'enable_load_more' => 'yes', 'layout_type' => 'grid' ],
                'frontend_available' => true,
            ]
        );
        
        $this->add_control(
            'carousel_options_heading',
            [
                'label' => esc_html__( 'Carousel Options', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::HEADING, 'separator' => 'before',
                'condition' => [ 'layout_type' => 'carousel' ],
            ]
        );
        $this->add_control(
            'carousel_autoplay',
            [
                'label' => esc_html__( 'Autoplay', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'no',
                'condition' => [ 'layout_type' => 'carousel' ],
                'frontend_available' => true,
            ]
        );
        $this->add_control(
            'carousel_autoplay_interval',
            [
                'label' => esc_html__( 'Autoplay Interval (ms)', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::NUMBER, 'min' => 1000, 'step' => 500, 'default' => 3000,
                'condition' => [ 'layout_type' => 'carousel', 'carousel_autoplay' => 'yes' ],
                'frontend_available' => true,
            ]
        );
        $this->add_control(
            'carousel_nav_buttons',
            [
                'label' => esc_html__( 'Navigation Arrows', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes',
                'condition' => [ 'layout_type' => 'carousel' ],
                'frontend_available' => true,
            ]
        );
        
        $this->add_control(
            'carousel_page_dots',
            [
                'label' => esc_html__( 'Pagination Dots', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'no',
                'condition' => [ 'layout_type' => 'carousel' ],
                'frontend_available' => true,
            ]
        );
        $this->add_control(
            'carousel_wrap_around',
            [
                'label' => esc_html__( 'Wrap Around (Infinite)', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes',
                'condition' => [ 'layout_type' => 'carousel' ],
                'frontend_available' => true,
            ]
        );
        $this->add_control(
            'carousel_draggable',
            [
                'label' => esc_html__( 'Draggable', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes',
                'condition' => [ 'layout_type' => 'carousel' ],
                'frontend_available' => true,
            ]
        );
        $this->add_control(
            'carousel_adaptive_height',
            [
                'label' => esc_html__( 'Adaptive Height', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'no',
                'condition' => [ 'layout_type' => 'carousel' ],
                'frontend_available' => true,
            ]
        );
        $this->add_control(
            'carousel_cell_align',
            [
                'label' => esc_html__( 'Cell Align', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::SELECT,
                'options' => [ 'left' => esc_html__( 'Left', 'domgats-customizable-product-filters' ), 'center' => esc_html__( 'Center', 'domgats-customizable-product-filters' ), 'right'  => esc_html__( 'Right', 'domgats-customizable-product-filters' ) ],
                'default' => 'left',
                'condition' => [ 'layout_type' => 'carousel' ],
                'frontend_available' => true,
            ]
        );

        $this->end_controls_section();
    }
    
    protected function register_style_controls() {
        $this->start_controls_section(
            'section_filter_bar_style',
            [
                'label' => esc_html__( 'Filter Bar', 'domgats-customizable-product-filters' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'filter_bar_spacing',
            [
                'label' => esc_html__( 'Spacing', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
                'selectors' => [ '{{WRAPPER}} .dgcpf-filters-wrapper' => 'gap: {{SIZE}}{{UNIT}};' ],
            ]
        );
        $this->add_control(
            'filter_label_heading',
            [
                'label' => esc_html__( 'Filter Label', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::HEADING, 'separator' => 'before',
            ]
        );
        $this->add_control(
            'filter_label_color',
            [
                'label' => esc_html__( 'Color', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .dgcpf-filter-label' => 'color: {{VALUE}};' ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'filter_label_typography',
                'selector' => '{{WRAPPER}} .dgcpf-filter-label',
            ]
        );
        $this->add_control(
            'filter_dropdown_heading',
            [
                'label' => esc_html__( 'Dropdown/Input Fields', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::HEADING, 'separator' => 'before',
            ]
        );
        $this->add_control(
            'filter_input_text_color',
            [
                'label' => esc_html__( 'Text Color', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .dgcpf-filter-dropdown, {{WRAPPER}} .dgcpf-filter-checkboxes label, {{WRAPPER}} .dgcpf-filter-radio-buttons label' => 'color: {{VALUE}};' ],
            ]
        );
        $this->add_control(
            'filter_input_background_color',
            [
                'label' => esc_html__( 'Background Color', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .dgcpf-filter-dropdown, {{WRAPPER}} .dgcpf-filter-checkboxes label, {{WRAPPER}} .dgcpf-filter-radio-buttons label' => 'background-color: {{VALUE}};' ],
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'filter_input_border',
                'selector' => '{{WRAPPER}} .dgcpf-filter-dropdown, {{WRAPPER}} .dgcpf-filter-checkboxes label, {{WRAPPER}} .dgcpf-filter-radio-buttons label',
            ]
        );
        $this->add_control(
            'filter_input_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::DIMENSIONS, 'size_units' => [ 'px', '%' ],
                'selectors' => [ '{{WRAPPER}} .dgcpf-filter-dropdown, {{WRAPPER}} .dgcpf-filter-checkboxes label, {{WRAPPER}} .dgcpf-filter-radio-buttons label' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ]
        );
        
        $this->end_controls_section();

        $this->start_controls_section(
            'section_load_more_style',
            [
                'label' => esc_html__( 'Load More Button', 'domgats-customizable-product-filters' ),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [ 'enable_load_more' => 'yes', 'layout_type' => 'grid' ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'load_more_typography',
                'selector' => '{{WRAPPER}} .dgcpf-load-more-button',
            ]
        );
        $this->start_controls_tabs( 'tabs_load_more_button_style' );
        $this->start_controls_tab( 'tab_load_more_button_normal', [ 'label' => esc_html__( 'Normal', 'domgats-customizable-product-filters' ) ] );
        $this->add_control(
            'load_more_button_text_color',
            [
                'label' => esc_html__( 'Text Color', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .dgcpf-load-more-button' => 'color: {{VALUE}};' ],
            ]
        );
        $this->add_control(
            'load_more_button_background_color',
            [
                'label' => esc_html__( 'Background Color', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .dgcpf-load-more-button' => 'background-color: {{VALUE}};' ],
            ]
        );
        $this->end_controls_tab();
        $this->start_controls_tab( 'tab_load_more_button_hover', [ 'label' => esc_html__( 'Hover', 'domgats-customizable-product-filters' ) ] );
        $this->add_control(
            'load_more_button_hover_text_color',
            [
                'label' => esc_html__( 'Text Color', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .dgcpf-load-more-button:hover' => 'color: {{VALUE}};' ],
            ]
        );
        $this->add_control(
            'load_more_button_hover_background_color',
            [
                'label' => esc_html__( 'Background Color', 'domgats-customizable-product-filters' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .dgcpf-load-more-button:hover' => 'background-color: {{VALUE}};' ],
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();

    }

    private function _get_loop_templates() {
        $templates = get_posts( [
            'post_type' => 'elementor_library', 'posts_per_page' => -1,
            'tax_query' => [
                [
                    'taxonomy' => 'elementor_library_type',
                    'field'    => 'slug',
                    'terms'    => 'loop-item',
                ],
            ],
        ] );
        $options = [ '' => esc_html__( 'Select a template', 'domgats-customizable-product-filters' ) ];
        if ( $templates ) {
            foreach ( $templates as $template ) {
                $options[ $template->ID ] = $template->post_title;
            }
        }
        return $options;
    }

    private function _get_all_taxonomies() {
        $taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );
        $options = [ '' => esc_html__( 'Select a taxonomy', 'domgats-customizable-product-filters' ) ];
        if ( $taxonomies ) {
            foreach ( $taxonomies as $taxonomy ) {
                if ( 'post_format' === $taxonomy->name ) {
                    continue;
                }
                $options[ $taxonomy->name ] = $taxonomy->label;
            }
        }
        return $options;
    }

    private function _get_all_acf_choice_field_keys() {
        if ( !$this->_is_acf_active() ) {
            return ['' => esc_html__('ACF plugin not active', 'domgats-customizable-product-filters')];
        }
        $options = [ '' => esc_html__( 'Select an ACF field', 'domgats-customizable-product-filters' ) ];
        $field_groups = acf_get_field_groups();
        if ( $field_groups ) {
            foreach ( $field_groups as $group ) {
                $fields = acf_get_fields( $group['key'] );
                if ( $fields ) {
                    foreach ( $fields as $field ) {
                        if ( in_array( $field['type'], ['select', 'checkbox', 'radio', 'true_false', 'button_group'] ) ) {
                            $options[ $field['key'] ] = $field['label'] . ' (' . $field['type'] . ')';
                        }
                    }
                }
            }
        }
        return $options;
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $template_id = (int) $settings['template_id'];

        if ( empty( $template_id ) ) {
            echo '<div class="dgcpf-filtered-loop-widget-container"><h3>' . esc_html__( 'DomGats Filtered Loop Widget', 'domgats-customizable-product-filters' ) . '</h3><p>' . esc_html__( 'Please select a Loop Item template to display content.', 'domgats-customizable-product-filters' ) . '</p></div>';
            return;
        }

        // Build initial query args
        $query_args = [
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => $settings['posts_per_page'] ?? 9,
        ];

        // --- Merged Query Logic ---
        $query_items = $settings['query_repeater'];

		if ( ! empty( $query_items ) ) {
			$meta_query_groups = [];
			$tax_query_groups = [];
			$current_group_index = 0;
			
			$meta_query_groups[$current_group_index] = [];
			$tax_query_groups[$current_group_index] = [];

			foreach ($query_items as $index => $item) {
				if ('acf' === $item['query_type'] && !empty($item['acf_meta_key']) && isset($item['acf_meta_value_choice']) && '' !== $item['acf_meta_value_choice']) {
					$field_object = get_field_object($item['acf_meta_key']);
					if ($field_object) {
						$meta_query_groups[$current_group_index][] = [
							'key' => $field_object['name'],
							'value' => sanitize_text_field($item['acf_meta_value_choice']),
							'compare' => sanitize_text_field($item['acf_meta_compare']),
						];
					}
				} elseif ('taxonomy' === $item['query_type'] && !empty($item['taxonomy']) && !empty($item['taxonomy_term'])) {
					$tax_query_groups[$current_group_index][] = [
						'taxonomy' => sanitize_text_field($item['taxonomy']),
						'field' => 'term_id',
						'terms' => (int) $item['taxonomy_term'],
					];
				}

				if (isset($item['relation']) && 'OR' === $item['relation'] && isset($query_items[$index + 1])) {
					$current_group_index++;
					$meta_query_groups[$current_group_index] = [];
					$tax_query_groups[$current_group_index] = [];
				}
			}

			$final_meta_query = ['relation' => 'OR'];
			foreach ($meta_query_groups as $group) {
				if (!empty($group)) {
					if (count($group) > 1) {
						$group['relation'] = 'AND';
					}
					$final_meta_query[] = $group;
				}
			}

			$final_tax_query = ['relation' => 'OR'];
			foreach ($tax_query_groups as $group) {
				if (!empty($group)) {
					if (count($group) > 1) {
						$group['relation'] = 'AND';
					}
					$final_tax_query[] = $group;
				}
			}

			if (count($final_meta_query) > 1) {
				$query_args['meta_query'] = $final_meta_query;
			}

			if (count($final_tax_query) > 1) {
				$query_args['tax_query'] = $final_tax_query;
			}
		}

        $query = new \WP_Query($query_args);
        
        $settings['max_num_pages'] = $query->max_num_pages;

        $this->add_render_attribute( 'widget_container', 'class', 'dgcpf-filtered-loop-widget-container' );
        $this->add_render_attribute( 'widget_container', 'data-settings', wp_json_encode( $settings ) );
        
        $layout_type_class = 'dgcpf-' . $settings['layout_type'];
        $this->add_render_attribute( 'loop_container', 'class', [ 'dgcpf-loop-container', $layout_type_class ] );

        echo '<div ' . $this->get_render_attribute_string( 'widget_container' ) . '>';
        
        // --- Render Frontend Filters ---
        if ( ! empty( $settings['filters_repeater'] ) ) {
             echo '<div class="dgcpf-filters-wrapper">';
             // Logic to render filters based on repeater settings from old file
             echo '</div>';
        }
       
        echo '<div ' . $this->get_render_attribute_string( 'loop_container' ) . '>';
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display($template_id);
            }
        } else {
            echo '<p class="no-products-found">' . esc_html__('No products found.', 'domgats-customizable-product-filters') . '</p>';
        }
        echo '</div>';
        
        // --- Render Load More / Carousel Nav ---
        if ( 'grid' === $settings['layout_type'] && 'yes' === $settings['enable_load_more'] ) {
            echo '<div class="dgcpf-load-more-container">';
            echo '<button class="dgcpf-load-more-button elementor-button">' . esc_html( $settings['load_more_button_text'] ) . '</button>';
            echo '</div>';
        }
        
        echo '</div>';

        wp_reset_postdata();
    }
}
