<?php
/**
 * Accelerate Theme Customizer
 *
 * @package    ThemeGrill
 * @subpackage Accelerate
 * @since      Accelerate 1.2
 */

function accelerate_customize_register( $wp_customize ) {
	// Transport postMessage variable set
	$customizer_selective_refresh = isset( $wp_customize->selective_refresh ) ? 'postMessage' : 'refresh';

	$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial( 'blogname', array(
			'selector'        => '#site-title a',
			'render_callback' => 'accelerate_customize_partial_blogname',
		) );

		$wp_customize->selective_refresh->add_partial( 'blogdescription', array(
			'selector'        => '#site-description',
			'render_callback' => 'accelerate_customize_partial_blogdescription',
		) );
	}

	/**
	 * Class to include upsell link campaign for theme.
	 *
	 * Class ACCELERATE_Upsell_Section
	 */
	class ACCELERATE_Upsell_Section extends WP_Customize_Section {
		public $type = 'accelerate-upsell-section';
		public $url  = '';
		public $id   = '';

		/**
		 * Gather the parameters passed to client JavaScript via JSON.
		 *
		 * @return array The array to be exported to the client as JSON.
		 */
		public function json() {
			$json        = parent::json();
			$json['url'] = esc_url( $this->url );
			$json['id']  = $this->id;

			return $json;
		}

		/**
		 * An Underscore (JS) template for rendering this section.
		 */
		protected function render_template() {
			?>
			<li id="accordion-section-{{ data.id }}" class="accelerate-upsell-accordion-section control-section-{{ data.type }} cannot-expand accordion-section">
				<h3 class="accordion-section-title"><a href="{{{ data.url }}}" target="_blank">{{ data.title }}</a></h3>
			</li>
			<?php
		}
	}

	// Register `ACCELERATE_Upsell_Section` type section.
	$wp_customize->register_section_type( 'ACCELERATE_Upsell_Section' );

	// Add `ACCELERATE_Upsell_Section` to display pro link.
	$wp_customize->add_section(
		new ACCELERATE_Upsell_Section( $wp_customize, 'accelerate_upsell_section',
			array(
				'title'      => esc_html__( 'View PRO version', 'accelerate' ),
				'url'        => 'https://themegrill.com/themes/accelerate/?utm_source=accelerate-customizer&utm_medium=view-pro-link&utm_campaign=view-pro#free-vs-pro',
				'capability' => 'edit_theme_options',
				'priority'   => 1,
			)
		)
	);

	/*
	 * Assigning the theme name
	 */
	$accelerate_themename = get_option( 'stylesheet' );
	$accelerate_themename = preg_replace( "/\W/", "_", strtolower( $accelerate_themename ) );

	// Start of the Header Options
	// Header Options Area
	$wp_customize->add_panel( 'accelerate_header_options', array(
		'capabitity' => 'edit_theme_options',
		'priority'   => 500,
		'title'      => __( 'Header', 'accelerate' ),
	) );

	// Header Logo upload option
	$wp_customize->add_section( 'accelerate_header_logo', array(
		'priority' => 1,
		'title'    => __( 'Header Logo', 'accelerate' ),
		'panel'    => 'accelerate_header_options',
	) );

	// Header logo and text display type option
	$wp_customize->add_section( 'accelerate_show_option', array(
		'priority' => 2,
		'title'    => __( 'Show', 'accelerate' ),
		'panel'    => 'accelerate_header_options',
	) );

	$wp_customize->add_setting( $accelerate_themename . '[accelerate_show_header_logo_text]', array(
		'default'           => 'text_only',
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'accelerate_radio_select_sanitize',
	) );

	$wp_customize->add_control( $accelerate_themename . '[accelerate_show_header_logo_text]', array(
		'type'    => 'radio',
		'label'   => __( 'Choose the option that you want.', 'accelerate' ),
		'section' => 'title_tagline',
		'choices' => array(
			'logo_only' => __( 'Header Logo Only', 'accelerate' ),
			'text_only' => __( 'Header Text Only', 'accelerate' ),
			'both'      => __( 'Show Both', 'accelerate' ),
			'none'      => __( 'Disable', 'accelerate' ),
		),
	) );

	// Header image position option
	$wp_customize->add_section( 'accelerate_header_image_position_section', array(
		'priority' => 3,
		'title'    => __( 'Header Image Position', 'accelerate' ),
		'panel'    => 'accelerate_header_options',
	) );

	$wp_customize->add_setting( $accelerate_themename . '[accelerate_header_image_position]', array(
		'default'           => 'position_two',
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'accelerate_radio_select_sanitize',
	) );

	$wp_customize->add_control( $accelerate_themename . '[accelerate_header_image_position]', array(
		'type'    => 'radio',
		'label'   => __( 'Choose top header image display position.', 'accelerate' ),
		'section' => 'accelerate_header_image_position_section',
		'choices' => array(
			'position_one'   => __( 'Position One: Display the Header image just above the site title/text.', 'accelerate' ),
			'position_two'   => __( 'Position Two (Default): Display the Header image between site title/text and the main/primary menu.', 'accelerate' ),
			'position_three' => __( 'Position Three: Display the Header image below main/primary menu.', 'accelerate' ),
		),
	) );

	// New Responsive Menu
	$wp_customize->add_section( 'accelerate_new_menu', array(
		'priority' => 4,
		'title'    => __( 'Responsive Menu Style', 'accelerate' ),
		'panel'    => 'accelerate_header_options',
	) );

	$wp_customize->add_setting( $accelerate_themename . '[accelerate_new_menu]', array(
		'default'           => '1',
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'accelerate_checkbox_sanitize',
	) );

	$wp_customize->add_control( $accelerate_themename . '[accelerate_new_menu]', array(
		'type'    => 'checkbox',
		'label'   => __( 'Switch to new responsive menu.', 'accelerate' ),
		'section' => 'accelerate_new_menu',
	) );

	// End of Header Options

	// Start of the Design Options
	$wp_customize->add_panel( 'accelerate_design_options', array(
		'capabitity' => 'edit_theme_options',
		'priority'   => 505,
		'title'      => __( 'Design', 'accelerate' ),
	) );

	// site layout setting
	$wp_customize->add_section( 'accelerate_site_layout_setting', array(
		'priority' => 1,
		'title'    => __( 'Site Layout', 'accelerate' ),
		'panel'    => 'accelerate_design_options',
	) );

	$wp_customize->add_setting( $accelerate_themename . '[accelerate_site_layout]', array(
		'default'           => 'wide',
		'transport'         => 'postMessage',
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'accelerate_radio_select_sanitize',
	) );

	$wp_customize->add_control( $accelerate_themename . '[accelerate_site_layout]', array(
		'type'    => 'radio',
		'label'   => __( 'Choose your site layout. The change is reflected in whole site.', 'accelerate' ),
		'choices' => array(
			'box'  => __( 'Boxed layout', 'accelerate' ),
			'wide' => __( 'Wide layout', 'accelerate' ),
		),
		'section' => 'accelerate_site_layout_setting',
	) );

	// Selective refresh for slider
	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial( $accelerate_themename . '[accelerate_activate_slider]', array(
			'selector'        => '#featured-slider',
			'render_callback' => '',
		) );
	}

	class Accelerate_Image_Radio_Control extends WP_Customize_Control {

		public function render_content() {

			if ( empty( $this->choices ) ) {
				return;
			}

			$name = '_customize-radio-' . $this->id;

			?>
			<style>
				#accelerate-img-container .accelerate-radio-img-img {
					border: 3px solid #DEDEDE;
					margin: 0 5px 5px 0;
					cursor: pointer;
					border-radius: 3px;
					-moz-border-radius: 3px;
					-webkit-border-radius: 3px;
				}

				#accelerate-img-container .accelerate-radio-img-selected {
					border: 3px solid #AAA;
					border-radius: 3px;
					-moz-border-radius: 3px;
					-webkit-border-radius: 3px;
				}

				input[type=checkbox]:before {
					content: '';
					margin: -3px 0 0 -4px;
				}
			</style>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<ul class="controls" id='accelerate-img-container'>
				<?php
				foreach ( $this->choices as $value => $label ) :
					$class = ( $this->value() == $value ) ? 'accelerate-radio-img-selected accelerate-radio-img-img' : 'accelerate-radio-img-img';
					?>
					<li style="display: inline;">
						<label>
							<input <?php $this->link(); ?>style='display:none' type="radio" value="<?php echo esc_attr( $value ); ?>" name="<?php echo esc_attr( $name ); ?>" <?php $this->link();
							checked( $this->value(), $value ); ?> />
							<img src='<?php echo esc_html( $label ); ?>' class='<?php echo $class; ?>' />
						</label>
					</li>
				<?php
				endforeach;
				?>
			</ul>
			<script type="text/javascript">
				jQuery( document ).ready( function ( $ ) {
					$( '.controls#accelerate-img-container li img' ).click( function () {
						$( '.controls#accelerate-img-container li' ).each( function () {
							$( this ).find( 'img' ).removeClass( 'accelerate-radio-img-selected' );
						} );
						$( this ).addClass( 'accelerate-radio-img-selected' );
					} );
				} );
			</script>
			<?php
		}
	}

	// default layout setting
	$wp_customize->add_section( 'accelerate_default_layout_setting', array(
		'priority' => 2,
		'title'    => __( 'Default layout', 'accelerate' ),
		'panel'    => 'accelerate_design_options',
	) );

	$wp_customize->add_setting( $accelerate_themename . '[accelerate_default_layout]', array(
		'default'           => 'right_sidebar',
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'accelerate_radio_select_sanitize',
	) );

	$wp_customize->add_control( new Accelerate_Image_Radio_Control( $wp_customize, $accelerate_themename . '[accelerate_default_layout]', array(
		'type'     => 'radio',
		'label'    => __( 'Select default layout. This layout will be reflected in whole site archives, search etc. The layout for a single post and page can be controlled from below options.', 'accelerate' ),
		'section'  => 'accelerate_default_layout_setting',
		'settings' => $accelerate_themename . '[accelerate_default_layout]',
		'choices'  => array(
			'right_sidebar'               => ACCELERATE_ADMIN_IMAGES_URL . '/right-sidebar.png',
			'left_sidebar'                => ACCELERATE_ADMIN_IMAGES_URL . '/left-sidebar.png',
			'no_sidebar_full_width'       => ACCELERATE_ADMIN_IMAGES_URL . '/no-sidebar-full-width-layout.png',
			'no_sidebar_content_centered' => ACCELERATE_ADMIN_IMAGES_URL . '/no-sidebar-content-centered-layout.png',
		),
	) ) );

	// default layout for pages
	$wp_customize->add_section( 'accelerate_default_page_layout_setting', array(
		'priority' => 3,
		'title'    => __( 'Default layout for pages only', 'accelerate' ),
		'panel'    => 'accelerate_design_options',
	) );

	$wp_customize->add_setting( $accelerate_themename . '[accelerate_pages_default_layout]', array(
		'default'           => 'right_sidebar',
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'accelerate_radio_select_sanitize',
	) );

	$wp_customize->add_control( new Accelerate_Image_Radio_Control( $wp_customize, $accelerate_themename . '[accelerate_pages_default_layout]', array(
		'type'     => 'radio',
		'label'    => __( 'Select default layout for pages. This layout will be reflected in all pages unless unique layout is set for specific page.', 'accelerate' ),
		'section'  => 'accelerate_default_page_layout_setting',
		'settings' => $accelerate_themename . '[accelerate_pages_default_layout]',
		'choices'  => array(
			'right_sidebar'               => ACCELERATE_ADMIN_IMAGES_URL . '/right-sidebar.png',
			'left_sidebar'                => ACCELERATE_ADMIN_IMAGES_URL . '/left-sidebar.png',
			'no_sidebar_full_width'       => ACCELERATE_ADMIN_IMAGES_URL . '/no-sidebar-full-width-layout.png',
			'no_sidebar_content_centered' => ACCELERATE_ADMIN_IMAGES_URL . '/no-sidebar-content-centered-layout.png',
		),
	) ) );

	// default layout for single posts
	$wp_customize->add_section( 'accelerate_default_single_posts_layout_setting', array(
		'priority' => 4,
		'title'    => __( 'Default layout for single posts only', 'accelerate' ),
		'panel'    => 'accelerate_design_options',
	) );

	$wp_customize->add_setting( $accelerate_themename . '[accelerate_single_posts_default_layout]', array(
		'default'           => 'right_sidebar',
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'accelerate_radio_select_sanitize',
	) );

	$wp_customize->add_control( new Accelerate_Image_Radio_Control( $wp_customize, $accelerate_themename . '[accelerate_single_posts_default_layout]', array(
		'type'     => 'radio',
		'label'    => __( 'Select default layout for single posts. This layout will be reflected in all single posts unless unique layout is set for specific post.', 'accelerate' ),
		'section'  => 'accelerate_default_single_posts_layout_setting',
		'settings' => $accelerate_themename . '[accelerate_single_posts_default_layout]',
		'choices'  => array(
			'right_sidebar'               => ACCELERATE_ADMIN_IMAGES_URL . '/right-sidebar.png',
			'left_sidebar'                => ACCELERATE_ADMIN_IMAGES_URL . '/left-sidebar.png',
			'no_sidebar_full_width'       => ACCELERATE_ADMIN_IMAGES_URL . '/no-sidebar-full-width-layout.png',
			'no_sidebar_content_centered' => ACCELERATE_ADMIN_IMAGES_URL . '/no-sidebar-content-centered-layout.png',
		),
	) ) );

	// Posts page listing display type setting
	$wp_customize->add_section( 'accelerate_post_page_display_type_setting', array(
		'priority' => 5,
		'title'    => __( 'Posts page listing display type', 'accelerate' ),
		'panel'    => 'accelerate_design_options',
	) );

	$wp_customize->add_setting( $accelerate_themename . '[accelerate_posts_page_display_type]', array(
		'default'           => 'large_image',
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'accelerate_radio_select_sanitize',
	) );

	$wp_customize->add_control( $accelerate_themename . '[accelerate_posts_page_display_type]', array(
		'type'    => 'radio',
		'label'   => __( 'Choose the display type for the latests posts view or posts page view (static front page).', 'accelerate' ),
		'choices' => array(
			'large_image'           => __( 'Large featured image', 'accelerate' ),
			'small_image'           => __( 'Small featured image', 'accelerate' ),
			'small_image_alternate' => __( 'Small featured image with alternating sides', 'accelerate' ),
		),
		'section' => 'accelerate_post_page_display_type_setting',
	) );

	// Site primary color option
	$wp_customize->add_section( 'accelerate_primary_color_setting', array(
		'panel'    => 'accelerate_design_options',
		'priority' => 6,
		'title'    => __( 'Primary color option', 'accelerate' ),
	) );

	$wp_customize->add_setting( $accelerate_themename . '[accelerate_primary_color]', array(
		'default'              => '#77CC6D',
		'type'                 => 'option',
		'capability'           => 'edit_theme_options',
		'sanitize_callback'    => 'accelerate_color_option_hex_sanitize',
		'sanitize_js_callback' => 'accelerate_color_escaping_option_sanitize',
	) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $accelerate_themename . '[accelerate_primary_color]', array(
		'label'    => __( 'This will reflect in links, buttons and many others. Choose a color to match your site and logo.', 'accelerate' ),
		'section'  => 'accelerate_primary_color_setting',
		'settings' => $accelerate_themename . '[accelerate_primary_color]',
	) ) );

	// Custom CSS setting
	if ( ! function_exists( 'wp_update_custom_css_post' ) ) {
		class Accelerate_Custom_CSS_Control extends WP_Customize_Control {

			public $type = 'custom_css';

			public function render_content() {
				?>
				<label>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<textarea rows="5" style="width:100%;" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
				</label>
				<?php
			}

		}

		$wp_customize->add_section( 'accelerate_custom_css_setting', array(
			'priority' => 7,
			'title'    => __( 'Custom CSS', 'accelerate' ),
			'panel'    => 'accelerate_design_options',
		) );

		$wp_customize->add_setting( $accelerate_themename . '[accelerate_custom_css]', array(
			'default'              => '',
			'type'                 => 'option',
			'capability'           => 'edit_theme_options',
			'sanitize_callback'    => 'wp_filter_nohtml_kses',
			'sanitize_js_callback' => 'wp_filter_nohtml_kses',
		) );

		$wp_customize->add_control( new Accelerate_Custom_CSS_Control( $wp_customize, $accelerate_themename . '[accelerate_custom_css]', array(
			'label'    => __( 'Write your custom css.', 'accelerate' ),
			'section'  => 'accelerate_custom_css_setting',
			'settings' => $accelerate_themename . '[accelerate_custom_css]',
		) ) );
	}
	// End of Design Options

	// Start of the Additional Options
	$wp_customize->add_panel( 'accelerate_additional_options', array(
		'capabitity' => 'edit_theme_options',
		'priority'   => 510,
		'title'      => __( 'Additional', 'accelerate' ),
	) );

	// Author Bio Option.
	$wp_customize->add_section( 'accelerate_author_bio_section', array(
		'priority' => 7,
		'title'    => esc_html__( 'Author Bio Option', 'accelerate' ),
		'panel'    => 'accelerate_additional_options',
	) );

	$wp_customize->add_setting( $accelerate_themename . '[accelerate_author_bio_setting]', array(
		'default'           => 0,
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'accelerate_checkbox_sanitize',
	) );

	$wp_customize->add_control( $accelerate_themename . '[accelerate_author_bio_setting]', array(
		'type'    => 'checkbox',
		'label'   => esc_html__( 'Check to display the author bio.', 'accelerate' ),
		'setting' => $accelerate_themename . '[accelerate_author_bio_setting]',
		'section' => 'accelerate_author_bio_section',
	) );

	// Related posts.
	$wp_customize->add_section( 'accelerate_related_posts_section', array(
		'priority' => 4,
		'title'    => esc_html__( 'Related Posts', 'accelerate' ),
		'panel'    => 'accelerate_additional_options',
	) );

	$wp_customize->add_setting( $accelerate_themename . '[accelerate_related_posts_activate]', array(
		'default'           => 0,
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'accelerate_checkbox_sanitize',
	) );

	$wp_customize->add_control( $accelerate_themename . '[accelerate_related_posts_activate]', array(
		'type'     => 'checkbox',
		'label'    => esc_html__( 'Check to activate the related posts', 'accelerate' ),
		'section'  => 'accelerate_related_posts_section',
		'settings' => $accelerate_themename . '[accelerate_related_posts_activate]',
	) );

	$wp_customize->add_setting( $accelerate_themename . '[accelerate_related_posts]', array(
		'default'           => 'categories',
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'accelerate_radio_select_sanitize',
	) );

	$wp_customize->add_control( $accelerate_themename . '[accelerate_related_posts]', array(
		'type'     => 'radio',
		'label'    => esc_html__( 'Related Posts Must Be Shown As:', 'accelerate' ),
		'section'  => 'accelerate_related_posts_section',
		'settings' => $accelerate_themename . '[accelerate_related_posts]',
		'choices'  => array(
			'categories' => esc_html__( 'Related Posts By Categories', 'accelerate' ),
			'tags'       => esc_html__( 'Related Posts By Tags', 'accelerate' ),
		),
	) );
	// End of Additional Options

	// Adding Text Area Control For Use In Customizer
	class Accelerate_Text_Area_Control extends WP_Customize_Control {

		public $type = 'text_area';

		public function render_content() {
			?>
			<label>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<textarea rows="5" style="width:100%;" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
			</label>
			<?php
		}

	}

	// Start of the Slider Options
	$wp_customize->add_panel( 'accelerate_slider_options', array(
		'capabitity' => 'edit_theme_options',
		'priority'   => 515,
		'title'      => __( 'Slider', 'accelerate' ),
	) );

	// Slider activate option
	$wp_customize->add_section( 'accelerate_slider_activate_section', array(
		'priority' => 1,
		'title'    => __( 'Activate slider', 'accelerate' ),
		'panel'    => 'accelerate_slider_options',
	) );

	$wp_customize->add_setting( $accelerate_themename . '[accelerate_activate_slider]', array(
		'default'           => 0,
		'type'              => 'option',
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'accelerate_checkbox_sanitize',
	) );

	$wp_customize->add_control( $accelerate_themename . '[accelerate_activate_slider]', array(
		'type'     => 'checkbox',
		'label'    => __( 'Check to activate slider.', 'accelerate' ),
		'section'  => 'accelerate_slider_activate_section',
		'settings' => $accelerate_themename . '[accelerate_activate_slider]',
	) );

	for ( $i = 1; $i <= 4; $i ++ ) {
		// adding slider section
		$wp_customize->add_section( 'accelerate_slider_number_section' . $i, array(
			'priority' => 10,
			'title'    => sprintf( __( 'Slider #%1$s', 'accelerate' ), $i ),
			'panel'    => 'accelerate_slider_options',
		) );

		// adding slider image url
		$wp_customize->add_setting( $accelerate_themename . '[accelerate_slider_image' . $i . ']', array(
			'default'           => '',
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'esc_url_raw',
		) );

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $accelerate_themename . '[accelerate_slider_image' . $i . ']', array(
			'label'   => __( 'Upload image', 'accelerate' ),
			'section' => 'accelerate_slider_number_section' . $i,
			'setting' => $accelerate_themename . '[accelerate_slider_image' . $i . ']',
		) ) );

		// adding slider title
		$wp_customize->add_setting( $accelerate_themename . '[accelerate_slider_title' . $i . ']', array(
			'default'           => '',
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'wp_filter_nohtml_kses',
		) );

		$wp_customize->add_control( $accelerate_themename . '[accelerate_slider_title' . $i . ']', array(
			'label'   => __( 'Enter title for this slide', 'accelerate' ),
			'section' => 'accelerate_slider_number_section' . $i,
			'setting' => $accelerate_themename . '[accelerate_slider_title' . $i . ']',
		) );

		// adding slider description
		$wp_customize->add_setting( $accelerate_themename . '[accelerate_slider_text' . $i . ']', array(
			'default'           => '',
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'accelerate_text_sanitize',
		) );

		$wp_customize->add_control( new Accelerate_Text_Area_Control( $wp_customize, $accelerate_themename . '[accelerate_slider_text' . $i . ']', array(
			'label'   => __( 'Enter description for this slide', 'accelerate' ),
			'section' => 'accelerate_slider_number_section' . $i,
			'setting' => $accelerate_themename . '[accelerate_slider_text' . $i . ']',
		) ) );

		// adding slider text position
		$wp_customize->add_setting( $accelerate_themename . '[accelerate_slide_text_position' . $i . ']', array(
			'default'           => 'right',
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'accelerate_radio_select_sanitize',
		) );

		$wp_customize->add_control( $accelerate_themename . '[accelerate_slide_text_position' . $i . ']', array(
			'type'    => 'radio',
			'label'   => __( 'Slider text position.', 'accelerate' ),
			'section' => 'accelerate_slider_number_section' . $i,
			'setting' => $accelerate_themename . '[accelerate_slide_text_position' . $i . ']',
			'choices' => array(
				'right' => __( 'Right side', 'accelerate' ),
				'left'  => __( 'Left side', 'accelerate' ),
			),
		) );

		// adding button url
		$wp_customize->add_setting( $accelerate_themename . '[accelerate_slider_link' . $i . ']', array(
			'default'           => '',
			'type'              => 'option',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'esc_url_raw',
		) );

		$wp_customize->add_control( $accelerate_themename . '[accelerate_slider_link' . $i . ']', array(
			'label'   => __( 'Enter link to redirect for the slide title', 'accelerate' ),
			'section' => 'accelerate_slider_number_section' . $i,
			'setting' => $accelerate_themename . '[accelerate_slider_link' . $i . ']',
		) );
	}
	// End of Slider Options

	// Start of data sanitization
	function accelerate_radio_select_sanitize( $input, $setting ) {
		// Ensuring that the input is a slug.
		$input = sanitize_key( $input );
		// Get the list of choices from the control associated with the setting.
		$choices = $setting->manager->get_control( $setting->id )->choices;

		// If the input is a valid key, return it, else, return the default.
		return ( array_key_exists( $input, $choices ) ? $input : $setting->default );
	}

	// color sanitization
	function accelerate_color_option_hex_sanitize( $color ) {
		if ( $unhashed = sanitize_hex_color_no_hash( $color ) ) {
			return '#' . $unhashed;
		}

		return $color;
	}

	function accelerate_color_escaping_option_sanitize( $input ) {
		$input = esc_attr( $input );

		return $input;
	}

	// text-area sanitize
	function accelerate_text_sanitize( $input ) {
		return wp_kses_post( force_balance_tags( $input ) );
	}

	// checkbox sanitize
	function accelerate_checkbox_sanitize( $input ) {
		if ( $input == 1 ) {
			return 1;
		} else {
			return '';
		}
	}

	// sanitization of links
	function accelerate_links_sanitize() {
		return false;
	}

}

add_action( 'customize_register', 'accelerate_customize_register' );

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 *
 * @since Accelerate 1.3.3
 */
function accelerate_customize_preview_js() {
	wp_enqueue_script( 'accelerate-customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), false, true );
}

add_action( 'customize_preview_init', 'accelerate_customize_preview_js' );

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function accelerate_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function accelerate_customize_partial_blogdescription() {
	bloginfo( 'description' );
}


/*****************************************************************************************/

/*
 * Custom Scripts
 */
add_action( 'customize_controls_print_footer_scripts', 'accelerate_customizer_custom_scripts' );

function accelerate_customizer_custom_scripts() { ?>
	<style>
		/* Theme Instructions Panel CSS */
		li#accordion-section-accelerate_upsell_section h3.accordion-section-title {
			background-color: #77CC6D !important;
			border-left-color: #55a54c !important;
		}

		#accordion-section-accelerate_upsell_section h3 a:after {
			content: '\f345';
			color: #fff;
			position: absolute;
			top: 12px;
			right: 10px;
			z-index: 1;
			font: 400 20px/1 dashicons;
			speak: none;
			display: block;
			-webkit-font-smoothing: antialiased;
			-moz-osx-font-smoothing: grayscale;
			text-decoration: none!important;
		}

		li#accordion-section-accelerate_upsell_section h3.accordion-section-title a {
			display: block;
			color: #fff !important;
			text-decoration: none;
		}

		li#accordion-section-accelerate_upsell_section h3.accordion-section-title a:focus {
			box-shadow: none;
		}

		li#accordion-section-accelerate_upsell_section h3.accordion-section-title:hover {
			background-color: #4fbb42 !important;
			color: #fff !important;
		}

		li#accordion-section-accelerate_upsell_section h3.accordion-section-title:after {
			color: #fff !important;
		}

		/* Upsell button CSS */
		.customize-control-accelerate-important-links a {
			/* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#8fc800+0,8fc800+100;Green+Flat+%232 */
			background: #008EC2;
			color: #fff;
			display: block;
			margin: 15px 0 0;
			padding: 5px 0;
			text-align: center;
			font-weight: 600;
		}

		.customize-control-accelerate-important-links a {
			padding: 8px 0;
		}

		.customize-control-accelerate-important-links a:hover {
			color: #ffffff;
			/* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#006e2e+0,006e2e+100;Green+Flat+%233 */
			background: #2380BA;
		}
	</style>

	<script>
		( function ( $, api ) {
			api.sectionConstructor['accelerate-upsell-section'] = api.Section.extend( {

				// No events for this type of section.
				attachEvents : function () {
				},

				// Always make the section active.
				isContextuallyActive : function () {
					return true;
				}
			} );
		} )( jQuery, wp.customize );

	</script>
	<?php
}

?>
