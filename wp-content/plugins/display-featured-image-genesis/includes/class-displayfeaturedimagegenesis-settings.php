<?php
/**
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      http://robincornett.com
 * @copyright 2014-2016 Robin Cornett Creative, LLC
 */

class Display_Featured_Image_Genesis_Settings extends Display_Featured_Image_Genesis_Helper {

	/**
	 * The common plugin class.
	 * @var $commmon Display_Featured_Image_Genesis_Common
	 */
	protected $common;

	/**
	 * The plugin admin page.
	 * @var $page string
	 */
	protected $page = 'displayfeaturedimagegenesis';

	/**
	 * The plugin setting.
	 * @var $setting string
	 */
	protected $setting;

	/**
	 * Public post types on the site.
	 * @var $post_types array
	 */
	protected $post_types;

	/**
	 * The plugin settings fields.
	 * @var $fields array
	 */
	protected $fields;

	/**
	 * add a submenu page under Appearance
	 * @return submenu Display Featured image settings page
	 * @since  1.4.0
	 */
	public function do_submenu_page() {

		$this->common     = new Display_Featured_Image_Genesis_Common();
		$this->setting    = $this->get_display_setting();
		$this->post_types = $this->get_content_types();

		add_theme_page(
			__( 'Display Featured Image for Genesis', 'display-featured-image-genesis' ),
			__( 'Display Featured Image for Genesis', 'display-featured-image-genesis' ),
			'manage_options',
			$this->page,
			array( $this, 'do_settings_form' )
		);

		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'load-appearance_page_displayfeaturedimagegenesis', array( $this, 'help' ) );

		$sections     = $this->register_sections();
		$this->fields = $this->register_fields();
		$this->add_sections( $sections );
		$this->add_fields( $this->fields, $sections );
	}

	/**
	 * create settings form
	 * @return form Display Featured Image for Genesis settings
	 *
	 * @since  1.4.0
	 */
	public function do_settings_form() {
		if ( $this->terms_need_updating() ) {
			$this->update_delete_term_meta();
		}
		$page_title = get_admin_page_title();
		echo '<div class="wrap">';
			printf( '<h1>%s</h1>', esc_attr( $page_title ) );
			if ( $this->terms_need_updating() ) {
				$this->term_meta_notice();
			}
			$active_tab = $this->get_active_tab();
			echo $this->do_tabs( $active_tab );
			echo '<form action="options.php" method="post">';
				settings_fields( 'displayfeaturedimagegenesis' );
				do_settings_sections( 'displayfeaturedimagegenesis_' . $active_tab );
				wp_nonce_field( 'displayfeaturedimagegenesis_save-settings', 'displayfeaturedimagegenesis_nonce', false );
				submit_button();
				settings_errors();
			echo '</form>';
		echo '</div>';
	}

	/**
	 * Output tabs.
	 * @return string
	 * @since 2.5.0
	 */
	protected function do_tabs( $active_tab ) {
		$tabs = array(
			'main' => array( 'id' => 'main', 'tab' => __( 'Main', 'display-featured-image-genesis' ) ),
			'cpt'  => array( 'id' => 'cpt', 'tab' => __( 'Content Types', 'display-featured-image-genesis' ) ),
		);
		$output  = '<div class="nav-tab-wrapper">';
		$output .= sprintf( '<h2 id="settings-tabs" class="screen-reader-text">%s</h2>', __( 'Settings Tabs', 'display-featured-image-genesis' ) );
		$output .= '<ul>';
		foreach ( $tabs as $tab ) {
			$class   = $active_tab === $tab['id'] ? ' nav-tab-active' : '';
			$output .= sprintf( '<li><a href="themes.php?page=%s&tab=%s" class="nav-tab%s">%s</a></li>', $this->page, $tab['id'], $class, $tab['tab'] );
		}
		$output .= '</ul>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Set which tab is considered active.
	 * @return string
	 * @since 2.5.0
	 */
	protected function get_active_tab() {
		return isset( $_GET['tab'] ) ? $_GET['tab'] : 'main';
	}

	/**
	 * Settings for options screen
	 * @return settings for backstretch image options
	 *
	 * @since 1.1.0
	 */
	public function register_settings() {
		register_setting( 'displayfeaturedimagegenesis', 'displayfeaturedimagegenesis', array( $this, 'do_validation_things' ) );
	}

	/**
	 * Retrieve plugin setting.
	 * @return array All plugin settings.
	 *
	 * @since 2.3.0
	 */
	public function get_display_setting() {
		$defaults = apply_filters( 'displayfeaturedimagegenesis_defaults', array(
			'less_header'   => 0,
			'default'       => '',
			'exclude_front' => 0,
			'keep_titles'   => 0,
			'move_excerpts' => 0,
			'is_paged'      => 0,
			'feed_image'    => 0,
			'thumbnails'    => 0,
			'post_types'    => array(),
			'skip'          => array(),
			'fallback'      => array(),
		) );

		$setting = get_option( 'displayfeaturedimagegenesis', $defaults );
		return wp_parse_args( $setting, $defaults );

	}

	/**
	 * Register plugin settings page sections
	 *
	 * @since 2.3.0
	 */
	protected function register_sections() {
		return array(
			'main' => array(
				'id'    => 'main',
				'title' => __( 'Optional Sitewide Settings', 'display-featured-image-genesis' ),
			),
			'cpt' => array(
				'id'    => 'cpt',
				'title' => __( 'Featured Images for Custom Content Types', 'display-featured-image-genesis' ),
			),
		);
	}

	/**
	 * Register plugin settings fields
	 * @return array           all settings fields
	 *
	 * @since 2.3.0
	 */
	protected function register_fields() {

		$fields = array(
			array(
				'id'       => 'less_header',
				'title'    => __( 'Height' , 'display-featured-image-genesis' ),
				'callback' => 'do_number',
				'section'  => 'main',
				'args'     => array( 'setting' => 'less_header', 'label' => __( 'pixels to remove', 'display-featured-image-genesis' ), 'min' => 0, 'max' => 400 ),
			),
			array(
				'id'       => 'default',
				'title'    => __( 'Default Featured Image', 'display-featured-image-genesis' ),
				'callback' => 'set_default_image',
				'section'  => 'main',
			),
			array(
				'id'       => 'exclude_front',
				'title'    => __( 'Skip Front Page', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'args'     => array( 'setting' => 'exclude_front', 'label' => __( 'Do not show the Featured Image on the Front Page of the site.', 'display-featured-image-genesis' ) ),
			),
			array(
				'id'       => 'keep_titles',
				'title'    => __( 'Do Not Move Titles', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'args'     => array( 'setting' => 'keep_titles', 'label' => __( 'Do not move the titles to overlay the backstretch Featured Image.', 'display-featured-image-genesis' ) ),
			),
			array(
				'id'       => 'move_excerpts',
				'title'    => __( 'Move Excerpts/Archive Descriptions', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'args'     => array( 'setting' => 'move_excerpts', 'label' => __( 'Move excerpts (if used) on single pages and move archive/taxonomy descriptions to overlay the Featured Image.', 'display-featured-image-genesis' ) ),
			),
			array(
				'id'       => 'is_paged',
				'title'    => __( 'Show Featured Image on Subsequent Blog Pages', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'args'     => array( 'setting' => 'is_paged', 'label' => __( 'Show featured image on pages 2+ of blogs and archives.', 'display-featured-image-genesis' ) ),
			),
			array(
				'id'       => 'feed_image',
				'title'    => __( 'Add Featured Image to Feed?', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'args'     => array( 'setting' => 'feed_image', 'label' => __( 'Optionally, add the featured image to your RSS feed.', 'display-featured-image-genesis' ) ),
			),
			array(
				'id'       => 'thumbnails',
				'title'    => __( 'Archive Thumbnails', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox',
				'section'  => 'main',
				'args'     => array( 'setting' => 'thumbnails', 'label' => __( 'Use term/post type fallback images for content archives?', 'display-featured-image-genesis' ) ),
			),
			array(
				'id'       => 'post_types][search',
				'title'    => __( 'Search Results', 'display-featured-image-genesis' ),
				'callback' => 'set_cpt_image',
				'section'  => 'cpt',
				'args'     => array( 'post_type' => 'search' ),
			),
			array(
				'id'       => 'post_types][fourohfour',
				'title'    => __( '404 Page', 'display-featured-image-genesis' ),
				'callback' => 'set_cpt_image',
				'section'  => 'cpt',
				'args'     => array( 'post_type' => 'fourohfour' ),
			),
			array(
				'id'       => 'skip',
				'title'    => __( 'Skip Content Types', 'display-featured-image-genesis' ),
				'callback' => 'do_checkbox_array',
				'section'  => 'cpt',
				'args'     => array( 'setting' => 'skip' ),
			),
		);

		if ( $this->post_types ) {

			foreach ( $this->post_types as $post ) {
				$object = get_post_type_object( $post );
				$fields[] = array(
					'id'       => 'post_types][' . esc_attr( $object->name ),
					'title'    => esc_attr( $object->label ),
					'callback' => 'set_cpt_image',
					'section'  => 'cpt',
					'args'     => array( 'post_type' => $object ),
				);
			}
		}
		return $fields;
	}

	/**
	 * Section description
	 * @return string description
	 *
	 * @since 1.1.0
	 */
	public function main_section_description() {
		$description = __( 'The Display Featured Image for Genesis plugin has just a few optional settings. Check the Help tab for more information. ', 'display-featured-image-genesis' );
		$this->print_section_description( $description );
	}

	/**
	 * Section description
	 * @return string description
	 *
	 * @since 1.1.0
	 */
	public function cpt_section_description() {
		$description = __( 'Optional: set a custom image for search results and 404 (no results found) pages.', 'display-featured-image-genesis' );
		if ( $this->post_types ) {
			$description .= __( ' Additionally, since you have custom post types with archives, you might like to set a featured image for each of them.', 'display-featured-image-genesis' );
		}
		$this->print_section_description( $description );
	}

	/**
	 * Description for less_header setting.
	 * @return string description
	 *
	 * @since 2.3.0
	 */
	protected function less_header_description() {
		return __( 'Changing this number will reduce the backstretch image height by this number of pixels. Default is zero.', 'display-featured-image-genesis' );
	}

	/**
	 * Default image uploader
	 *
	 * @return  image
	 *
	 * @since  1.2.1
	 */
	public function set_default_image() {

		$id   = $this->setting['default'] ? $this->setting['default'] : '';
		$name = 'displayfeaturedimagegenesis[default]';
		if ( ! empty( $id ) ) {
			echo wp_kses_post( $this->render_image_preview( $id, 'default' ) );
		}
		$this->render_buttons( $id, $name );
		$this->do_description( 'default_image' );

	}

	/**
	 * Description for default image setting
	 * @return string
	 *
	 * @since 2.3.0
	 */
	protected function default_image_description() {
		$large = $this->common->minimum_backstretch_width();
		return sprintf(
			esc_html__( 'If you would like to use a default image for the featured image, upload it here. Must be at least %1$s pixels wide.', 'display-featured-image-genesis' ),
			absint( $large + 1 )
		);
	}

	/**
	 * Custom Post Type image uploader
	 *
	 * @return  image
	 *
	 * @since  2.0.0
	 */
	public function set_cpt_image( $args ) {

		$post_type = is_object( $args['post_type'] ) ? $args['post_type']->name : $args['post_type'];
		if ( empty( $this->setting['post_type'][ $post_type ] ) ) {
			$this->setting['post_type'][ $post_type ] = $id = '';
		}

		if ( is_object( $args['post_type'] ) ) {
			$fallback_args = array(
				'setting'      => "fallback][{$post_type}",
				'label'        => sprintf( __( 'Always use a fallback image for %s.', 'display-featured-image-genesis' ), esc_attr( $args['post_type']->label ) ),
				'setting_name' => 'fallback',
				'name'         => $post_type,
			);
			echo '<p>';
			$this->do_checkbox( $fallback_args );
			echo '</p>';
		}
		$id   = $this->setting['post_type'][ $post_type ];
		$name = 'displayfeaturedimagegenesis[post_type][' . esc_attr( $post_type ) . ']';
		if ( $id ) {
			echo wp_kses_post( $this->render_image_preview( $id, $post_type ) );
		}

		$this->render_buttons( $id, $name );

		if ( empty( $id ) || ! is_object( $args['post_type'] ) ) {
			return;
		}
		$description = sprintf( __( 'View your <a href="%1$s" target="_blank">%2$s</a> archive.', 'display-featured-image-genesis' ),
			esc_url( get_post_type_archive_link( $post_type ) ),
			esc_attr( $args['post_type']->label )
		);
		printf( '<p class="description">%s</p>', wp_kses_post( $description ) );
	}

	/**
	 * validate all inputs
	 * @param  string $new_value various settings
	 * @return string            number or URL
	 *
	 * @since  1.4.0
	 */
	public function do_validation_things( $new_value ) {

		$action = 'displayfeaturedimagegenesis_save-settings';
		$nonce  = 'displayfeaturedimagegenesis_nonce';
		// If the user doesn't have permission to save, then display an error message
		if ( ! $this->user_can_save( $action, $nonce ) ) {
			wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'display-featured-image-genesis' ) );
		}

		check_admin_referer( 'displayfeaturedimagegenesis_save-settings', 'displayfeaturedimagegenesis_nonce' );
		$new_value = array_merge( $this->setting, $new_value );

		// validate all checkbox fields
		foreach ( $this->fields as $field ) {
			if ( 'do_checkbox' === $field['callback'] ) {
				$new_value[ $field['id'] ] = $this->one_zero( $new_value[ $field['id'] ] );
			} elseif ( 'do_number' === $field['callback'] ) {
				$new_value[ $field['id'] ] = $this->check_value( $new_value[ $field['id'] ], $this->setting[ $field['id'] ], $field['args']['min'], $field['args']['max'] );
			}
		}

		// extra variables to pass through to image validation
		$size_to_check = $this->common->minimum_backstretch_width();

		// validate default image
		$new_value['default'] = $this->validate_image( $new_value['default'], $this->setting['default'], __( 'Default', 'display-featured-image-genesis' ), $size_to_check );

		// search/404
		$size_to_check = get_option( 'medium_size_w' );
		$custom_pages  = array(
			array(
				'id'    => 'search',
				'label' => __( 'Search Results', 'display-featured-image-genesis' ),
			),
			array(
				'id'    => 'fourohfour',
				'label' => __( '404 Page', 'display-featured-image-genesis' ),
			),
		);
		foreach ( $custom_pages as $page ) {
			$setting_to_check                       = isset( $this->setting['post_type'][ $page['id'] ] ) ? $this->setting['post_type'][ $page['id'] ] : '';
			$new_value['post_type'][ $page ['id'] ] = $this->validate_image( $new_value['post_type'][ $page['id'] ], $setting_to_check, $page['label'], $size_to_check );
		}

		foreach ( $this->post_types as $post_type ) {

			$object    = get_post_type_object( $post_type );
			$old_value = isset( $this->setting['post_type'][ $object->name ] ) ? $this->setting['post_type'][ $object->name ] : '';
			$label     = $object->label;

			$new_value['post_type'][ $post_type ] = $this->validate_image( $new_value['post_type'][ $post_type ], $old_value, $label, $size_to_check );
			$new_value['fallback'][ $post_type ]  = $this->one_zero( $new_value['fallback'][ $post_type ] );
		}
		$post_types = $this->get_content_types_built_in();
		foreach ( $post_types as $post_type ) {
			$new_value['skip'][ $post_type ] = $this->one_zero( $new_value['skip'][ $post_type ] );
		}

		return $new_value;
	}

	/**
	 * Check the numeric value against the allowed range. If it's within the range, return it; otherwise, return the old value.
	 * @param $new_value int new submitted value
	 * @param $old_value int old setting value
	 * @param $min int minimum value
	 * @param $max int maximum value
	 *
	 * @return int
	 */
	protected function check_value( $new_value, $old_value, $min, $max ) {
		if ( $new_value >= $min && $new_value <= $max ) {
			return (int) $new_value;
		}
		return (int) $old_value;
	}

	/**
	 * Help tab for media screen
	 * @return help tab with verbose information for plugin
	 *
	 * @since 1.1.0
	 */
	public function help() {
		$screen = get_current_screen();
		$large  = $this->common->minimum_backstretch_width();

		$height_help  = '<h3>' . __( 'Height', 'display-featured-image-genesis' ) . '</h3>';
		$height_help .= '<p>' . __( 'Depending on how your header/nav are set up, or if you just do not want your backstretch image to extend to the bottom of the user screen, you may want to change this number. It will raise the bottom line of the backstretch image, making it shorter.', 'display-featured-image-genesis' ) . '</p>';
		$height_help .= '<p>' . __( 'The plugin determines the size of your backstretch image based on the size of the user\'s browser window. Changing the "Height" setting tells the plugin to subtract that number of pixels from the measured height of the user\'s window, regardless of the size of that window.', 'display-featured-image-genesis' ) . '</p>';
		$height_help .= '<p>' . __( 'If you need to control the size of the backstretch Featured Image output with more attention to the user\'s screen size, you will want to consider a CSS approach instead. Check the readme for an example.', 'display-featured-image-genesis' ) . '</p>';

		$default_help  = '<h3>' . __( 'Default Featured Image', 'display-featured-image-genesis' ) . '</h3>';
		$default_help .= '<p>' . __( 'You may set a large image to be used sitewide if a featured image is not available. This image will show on posts, pages, and archives.', 'display-featured-image-genesis' ) . '</p>';
		$default_help .= '<p>' . sprintf(
			__( 'Supported file types are: jpg, jpeg, png, and gif. The image must be at least %1$s pixels wide.', 'display-featured-image-genesis' ),
			absint( $large + 1 )
		) . '</p>';

		$skipfront_help  = '<h3>' . __( 'Skip Front Page', 'display-featured-image-genesis' ) . '</h3>';
		$skipfront_help .= '<p>' . __( 'If you set a Default Featured Image, it will show on every post/page of your site. This may not be desirable on child themes with a front page constructed with widgets, so you can select this option to prevent the Featured Image from showing on the front page. Checking this will prevent the Featured Image from showing on the Front Page, even if you have set an image for that page individually.', 'display-featured-image-genesis' ) . '</p>';
		$skipfront_help .= '<p>' . sprintf(
			__( 'If you want to prevent entire groups of posts from not using the Featured Image, you will want to <a href="%s" target="_blank">add a filter</a> to your theme functions.php file.', 'display-featured-image-genesis' ),
			esc_url( 'https://github.com/robincornett/display-featured-image-genesis#how-do-i-stop-the-featured-image-action-from-showing-on-my-custom-post-types' )
		) . '</p>';

		$keeptitles_help  = '<h3>' . __( 'Do Not Move Titles', 'display-featured-image-genesis' ) . '</h3>';
		$keeptitles_help .= '<p>' . __( 'This setting applies to the backstretch Featured Image only. It allows you to keep the post/page titles in their original location, instead of overlaying the new image.', 'display-featured-image-genesis' ) . '</p>';

		$excerpts_help  = '<h3>' . __( 'Move Excerpts/Archive Descriptions', 'display-featured-image-genesis' ) . '</h3>';
		$excerpts_help .= '<p>' . __( 'By default, archive descriptions (set on the Genesis Archive Settings pages) show below the Default Featured Image, while the archive title displays on top of the image. If you check this box, all headlines, descriptions, and optional excerpts will display in a box overlaying the Featured Image.', 'display-featured-image-genesis' ) . '</p>';

		$paged_help  = '<h3>' . __( 'Show Featured Image on Subsequent Blog Pages', 'display-featured-image-genesis' ) . '</h3>';
		$paged_help .= '<p>' . __( 'Featured Images do not normally show on the second (and following) page of term/blog/post archives. Check this setting to ensure that they do.', 'display-featured-image-genesis' ) . '</p>';

		$feed_help  = '<h3>' . __( 'Add Featured Image to Feed?', 'display-featured-image-genesis' ) . '</h3>';
		$feed_help .= '<p>' . __( 'This plugin does not add the Featured Image to your content, so normally you will not see your Featured Image in the feed. If you select this option, however, the Featured Image (if it is set) will be added to each entry in your RSS feed.', 'display-featured-image-genesis' ) . '</p>';
		$feed_help .= '<p>' . __( 'If your RSS feed is set to Full Text, the Featured Image will be added to the entry content. If it is set to Summary, the Featured Image will be added to the excerpt instead.', 'display-featured-image-genesis' ) . '</p>';

		$archive_help  = '<h3>' . __( 'Archive Thumbnails', 'display-featured-image-genesis' ) . '</h3>';
		$archive_help .= '<p>' . __( 'This setting will set a fallback image for all content types in your archives. If there is no featured image, and no images uploaded to the post/page, the plugin will use the featured image for the term, or post type, as the thumbnail.', 'display-featured-image-genesis' ) . '</p>';
		$archive_help .= '<p>' . __( 'The thumbnail will adhere to the settings from the Genesis settings page.', 'display-featured-image-genesis' ) . '</p>';

		$special_help  = '<h3>' . __( 'Featured Images for Special Pages', 'display-featured-image-genesis' ) . '</h3>';
		$special_help .= '<p>' . __( 'You can now set a featured image for search results and 404 (no results found) pages.', 'display-featured-image-genesis' ) . '</p>';

		$cpt_help  = '<h3>' . __( 'Featured Images for Custom Content Types', 'display-featured-image-genesis' ) . '</h3>';
		$cpt_help .= '<p>' . __( 'Some plugins and/or developers extend the power of WordPress by using Custom Post Types to create special kinds of content.', 'display-featured-image-genesis' ) . '</p>';
		$cpt_help .= '<p>' . __( 'Since you have custom post types with archives, you might like to set a featured image for each of them.', 'display-featured-image-genesis' ) . '</p>';
		$cpt_help .= '<p>' . __( 'Featured Images for archives can be smaller than the Default Featured Image, but still need to be larger than your site\'s "medium" image size.', 'display-featured-image-genesis' ) . '</p>';

		$skip_help  = '<h3>' . __( 'Skip Content Types', 'display-featured-image-genesis' ) . '</h3>';
		$skip_help .= '<p>' . __( 'Tell WordPress which content types should never have the featured image added.', 'display-featured-image-genesis' ) . '</p>';

		$fallback_help  = '<h3>' . __( 'Fallback Images', 'display-featured-image-genesis' ) . '</h3>';
		$fallback_help .= '<p>' . __( 'Instead of using the content type\'s Featured Image on singular posts, use one of the fallback images. This may be assigned to a term within a taxonomy, or be the content type featured image, or be the sitewide default image.', 'display-featured-image-genesis' ) . '</p>';
		$fallback_help .= '<p>' . __( 'If no fallback image exists, no featured image will display, as this will shortcut the check for the post\'s featured image.' , 'display-featured-image-genesis' ) . '</p>';

		$help_tabs = array(
			array(
				'id'      => 'displayfeaturedimage_less_header-help',
				'title'   => __( 'Height', 'display-featured-image-genesis' ),
				'content' => $height_help,
			),
			array(
				'id'      => 'displayfeaturedimage_default-help',
				'title'   => __( 'Default Featured Image', 'display-featured-image-genesis' ),
				'content' => $default_help,
			),
			array(
				'id'      => 'displayfeaturedimage_exclude_front-help',
				'title'   => __( 'Skip Front Page', 'display-featured-image-genesis' ),
				'content' => $skipfront_help,
			),
			array(
				'id'      => 'displayfeaturedimage_keep_titles-help',
				'title'   => __( 'Do Not Move Titles', 'display-featured-image-genesis' ),
				'content' => $keeptitles_help,
			),
			array(
				'id'      => 'displayfeaturedimage_excerpts-help',
				'title'   => __( 'Move Excerpts', 'display-featured-image-genesis' ),
				'content' => $excerpts_help,
			),
			array(
				'id'      => 'displayfeaturedimage_paged-help',
				'title'   => __( 'Subsequent Pages', 'display-featured-image-genesis' ),
				'content' => $paged_help,
			),
			array(
				'id'      => 'displayfeaturedimage_feed-help',
				'title'   => __( 'RSS Feed', 'display-featured-image-genesis' ),
				'content' => $feed_help,
			),
			array(
				'id'      => 'displayfeaturedimage_archive-help',
				'title'   => __( 'Archive Thumbnails', 'display-featured-image-genesis' ),
				'content' => $archive_help,
			),
		);
		$active_tab = $this->get_active_tab();
		if ( 'cpt' === $active_tab ) {
			$help_tabs = array(
				array(
					'id'      => 'displayfeaturedimage_special-help',
					'title'   => __( 'Special Pages', 'display-featured-image-genesis' ),
					'content' => $special_help,
				),
				array(
					'id'      => 'displayfeaturedimage_skip-help',
					'title'   => __( 'Skip Content Types', 'display-featured-image-genesis' ),
					'content' => $skip_help,
				),
				array(
					'id'      => 'displayfeaturedimage_fallback-help',
					'title'   => __( 'Fallback Images', 'display-featured-image-genesis' ),
					'content' => $fallback_help,
				),
			);
			if ( $this->get_content_types() ) {
				$help_tabs[] = array(
					'id'      => 'displayfeaturedimage_cpt-help',
					'title'   => __( 'Custom Content Types', 'display-featured-image-genesis' ),
					'content' => $cpt_help,
				);
			}
		}
		foreach ( $help_tabs as $tab ) {
			$screen->add_help_tab( $tab );
		}
	}

	/**
	 * For 4.4, output a notice explaining that old term options can be updated to term_meta.
	 * Options are to update all terms or to ignore, and do by hand.
	 * @since 2.4.0
	 */
	protected function term_meta_notice() {
		$screen = get_current_screen();
		if ( 'appearance_page_displayfeaturedimagegenesis' !== $screen->id ) {
			return;
		}
		$terms = $this->get_affected_terms();
		if ( empty( $terms ) ) {
			return;
		}
		$message  = sprintf( '<p>%s</p>', __( 'WordPress 4.4 introduces term metadata for categories, tags, and other taxonomies. This is your opportunity to optionally update all impacted terms on your site to use the new metadata.', 'display-featured-image-genesis' ) );
		$message .= sprintf( '<p>%s</p>', __( 'This <strong>will modify</strong> your database (potentially many entries at once), so if you\'d rather do it yourself, you can. Here\'s a list of the affected terms:', 'display-featured-image-genesis' ) );
		$message .= '<ul style="margin-left:24px;">';
		foreach ( $terms as $term ) {
			$message .= edit_term_link( $term->name, '<li>', '</li>', $term, false );
		}
		$message .= '</ul>';
		$message .= sprintf( '<p>%s</p>', __( 'To get rid of this notice, you can 1) update your terms by hand; 2) click the update button (please check your terms afterward); or 3) click the dismiss button.', 'display-featured-image-genesis' ) );
		$faq      = sprintf( __( 'For more information, please visit the plugin\'s <a href="%s" target="_blank">Frequently Asked Questions</a> on WordPress.org.', 'display-featured-image-genesis' ), esc_url( 'https://wordpress.org/plugins/display-featured-image-genesis/faq/' ) );
		$message .= sprintf( '<p>%s</p>', $faq );
		echo '<div class="updated">' . wp_kses_post( $message );
		echo '<form action="" method="post">';
		wp_nonce_field( 'displayfeaturedimagegenesis_metanonce', 'displayfeaturedimagegenesis_metanonce', false );
		$buttons = array(
			array(
				'value' => __( 'Update My Terms', 'display-featured-image-genesis' ),
				'name'  => 'displayfeaturedimagegenesis_termmeta',
				'class' => 'button-primary',
			),
			array(
				'value' => __( 'Dismiss (I\'ve got this!)', 'display-featured-image-genesis' ),
				'name'  => 'displayfeaturedimagegenesis_termmetadismiss',
				'class' => 'button-secondary',
			),
		);
		echo '<p>';
		foreach ( $buttons as $button ) {
			printf( '<input type="submit" class="%s" name="%s" value="%s" style="margin-right:12px;" />',
				esc_attr( $button['class'] ),
				esc_attr( $button['name'] ),
				esc_attr( $button['value'] )
			);
		}
		echo '</p>';
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Update and/or delete term_meta and wp_options
	 * @since 2.4.0
	 */
	protected function update_delete_term_meta() {

		if ( isset( $_POST['displayfeaturedimagegenesis_termmeta'] ) ) {
			if ( ! check_admin_referer( 'displayfeaturedimagegenesis_metanonce', 'displayfeaturedimagegenesis_metanonce' ) ) {
				return;
			}
			$terms = $this->get_affected_terms();
			foreach ( $terms as $term ) {
				$term_id = $term->term_id;
				$option  = get_option( "displayfeaturedimagegenesis_{$term_id}" );
				if ( false !== $option ) {
					$image_id = (int) displayfeaturedimagegenesis_check_image_id( $option['term_image'] );
					update_term_meta( $term_id, 'displayfeaturedimagegenesis', $image_id );
					delete_option( "displayfeaturedimagegenesis_{$term_id}" );
				}
			}
		}

		if ( isset( $_POST['displayfeaturedimagegenesis_termmeta'] ) || isset( $_POST['displayfeaturedimagegenesis_termmetadismiss'] ) ) {
			if ( ! check_admin_referer( 'displayfeaturedimagegenesis_metanonce', 'displayfeaturedimagegenesis_metanonce' ) ) {
				return;
			}
			update_option( 'displayfeaturedimagegenesis_updatedterms', true );
		}
	}

	/**
	 * Get IDs of terms with featured images
	 * @param  array  $term_ids empty array
	 * @return array           all terms with featured images
	 * @since 2.4.0
	 */
	protected function get_affected_terms( $affected_terms = array() ) {
		$args = array(
			'public'  => true,
			'show_ui' => true,
		);
		$taxonomies = get_taxonomies( $args, 'objects' );

		foreach ( $taxonomies as $tax ) {
			$args   = array(
				'orderby'    => 'name',
				'order'      => 'ASC',
				'hide_empty' => false,
			);
			$terms  = get_terms( $tax->name, $args );
			foreach ( $terms as $term ) {
				$term_id = $term->term_id;
				$option  = get_option( "displayfeaturedimagegenesis_{$term_id}", false );
				if ( false !== $option ) {
					$affected_terms[] = $term;
				}
			}
		}
		return $affected_terms;
	}

	/**
	 * Check whether terms need to be updated
	 * @return boolean true if on 4.4 and wp_options for terms exist; false otherwise
	 *
	 * @since 2.4.0
	 */
	protected function terms_need_updating() {
		$updated = get_option( 'displayfeaturedimagegenesis_updatedterms', false );
		if ( ! $updated && function_exists( 'get_term_meta' ) ) {
			return true;
		}
		return false;
	}
}
