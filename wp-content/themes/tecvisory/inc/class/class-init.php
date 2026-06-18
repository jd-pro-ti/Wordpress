<?php
/**
 * Init Configuration
 *
 * @author Jegstudio
 * @package tecvisory
 */

namespace Tecvisory;

use WP_Query;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Init Class
 *
 * @package tecvisory
 */
class Init {

	/**
	 * Instance variable
	 *
	 * @var $instance
	 */
	private static $instance;

	/**
	 * Class instance.
	 *
	 * @return Init
	 */
	public static function instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Class constructor.
	 */
	private function __construct() {
		$this->init_instance();
		$this->load_hooks();
	}

	/**
	 * Load initial hooks.
	 */
	private function load_hooks() {
		add_action( 'after_setup_theme', array( $this, 'setup_theme' ) );
		add_action( 'init', array( $this, 'register_block_patterns' ), 9 );
		add_action( 'admin_enqueue_scripts', array( $this, 'dashboard_scripts' ) );

		add_action( 'wp_ajax_tecvisory_set_admin_notice_viewed', array( $this, 'notice_closed' ) );

		add_action( 'after_switch_theme', array( $this, 'update_global_styles_after_theme_switch' ) );
		add_filter( 'gutenverse_block_config', array( $this, 'default_font' ), 10 );
		add_filter( 'gutenverse_font_header', array( $this, 'default_header_font' ) );
		add_filter( 'gutenverse_global_css', array( $this, 'global_header_style' ) );

		add_filter( 'gutenverse_themes_template', array( $this, 'add_template' ), 10, 2 );
		add_filter( 'gutenverse_themes_override_mechanism', '__return_true' );

		add_filter( 'gutenverse_themes_support_section_global_style', '__return_true' );
		add_filter( 'gutenverse_show_theme_list', '__return_false' );
		add_filter( 'gutenverse_companion_essential_assets_directory', function () { return TECVISORY_DIR . 'assets'; });
		add_filter( 'gutenverse_companion_essential_assets_url', function () { return trailingslashit( get_template_directory_uri() ) . 'assets'; } );
		add_filter( 'gutenverse_companion_essential_mode_on', '__return_true' );
		add_filter( 'gutenverse_companion_essential_modular', '__return_true' );
		add_filter( 'gutenverse_jegtheme_theme_type', function () { return 'normal'; } );
	}

	/**
	 * Setup theme.
	 */
	public function setup_theme() {
		load_theme_textdomain( 'tecvisory', get_template_directory() . '/languages' );
	}

	/**
	 * Add Template to Editor.
	 *
	 * @param array $template_files Path to Template File.
	 * @param array $template_type Template Type.
	 *
	 * @return array
	 */
	public function add_template( $template_files, $template_type ) {
		if ( 'wp_template' === $template_type ) {
			$new_templates = array(
				'blank-canvas','full-width',
			);

			foreach ( $new_templates as $template ) {
				$template_files[] = array(
					'slug'  => $template,
					'path'  => TECVISORY_DIR . "templates/{$template}.html",
					'theme' => get_template(),
					'type'  => 'wp_template',
					'title' => ucfirst( str_replace( '-', ' ', $template ) ),
				);
			}
		}

		return $template_files;
	}

	/**
	 * Initialize Instance.
	 */
	public function init_instance() {
		new Asset_Enqueue();
		new Plugin_Notice();
	}

	/**
	 * Update Global Styles After Theme Switch
	 */
	public function update_global_styles_after_theme_switch() {
		// Get the path to the current theme's theme.json file
		$theme_json_path = get_template_directory() . '/theme.json';
		$theme_slug      = get_option( 'stylesheet' ); // Get the current theme's slug
		$args            = array(
			'post_type'      => 'wp_global_styles',
			'post_status'    => 'publish',
			'name'           => 'wp-global-styles-' . $theme_slug,
			'posts_per_page' => 1,
		);

		$global_styles_query = new WP_Query( $args );
		// Check if the theme.json file exists
		if ( file_exists( $theme_json_path ) && $global_styles_query->have_posts() ) {
			$global_styles_query->the_post();
			$global_styles_post_id = get_the_ID();
			// Step 2: Get the existing global styles (color palette)
			$global_styles_content = json_decode( get_post_field( 'post_content', $global_styles_post_id ), true );
			if ( isset( $global_styles_content['settings']['color']['palette']['theme'] ) ) {
				$existing_colors = $global_styles_content['settings']['color']['palette']['theme'];
			} else {
				$existing_colors = array();
			}

			// Step 3: Extract slugs from the existing colors
			$existing_slugs = array_column( $existing_colors, 'slug' );
			// Step 4:Read the contents of the theme.json file

			$theme_json_content = file_get_contents( $theme_json_path );
			$theme_json_data    = json_decode( $theme_json_content, true );

			// Access the color palette from the theme.json file
			if ( isset( $theme_json_data['settings']['color']['palette'] ) ) {

				$theme_colors = $theme_json_data['settings']['color']['palette'];

				// Step 5: Loop through theme.json colors and add them if they don't exist
				foreach ( $theme_colors as $theme_color ) {
					if ( ! in_array( $theme_color['slug'], $existing_slugs ) ) {
						$existing_colors[] = $theme_color; // Add new color to the existing palette
					}
				}
				foreach ( $theme_colors as $theme_color ) {
					$theme_slug = $theme_color['slug'];

					// Step 6: Use in_array to check if the slug already exists in the global palette
					if ( ! in_array( $theme_slug, $existing_slugs ) ) {
						// If the slug does not exist, add the theme color to the global palette
						$global_colors[] = $theme_color;
					}
				}
				// Step 6: Update the global styles content with the new colors
				$global_styles_content['settings']['color']['palette']['theme'] = $existing_colors;

				// Step 7: Save the updated global styles back to the post
				wp_update_post(
					array(
						'ID'           => $global_styles_post_id,
						'post_content' => wp_json_encode( $global_styles_content ),
					)
				);

			}
			wp_reset_postdata(); // Reset the query
		}
	}

	/**
	 * Notice Closed
	 */
	public function notice_closed() {
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'tecvisory_admin_notice' ) ) {
			update_user_meta( get_current_user_id(), 'gutenverse_install_notice', 'true' );
		}
		die;
	}

	/**
	 * Generate Global Font
	 *
	 * @param string $value  Value of the option.
	 *
	 * @return string
	 */
	public function global_header_style( $value ) {
		$theme_name      = get_stylesheet();
		$global_variable = get_option( 'gutenverse-global-variable-font-' . $theme_name );

		if ( empty( $global_variable ) && function_exists( 'gutenverse_global_font_style_generator' ) ) {
			$font_variable = $this->default_font_variable();
			$value        .= \gutenverse_global_font_style_generator( $font_variable );
		}

		return $value;
	}

	/**
	 * Header Font.
	 *
	 * @param mixed $value  Value of the option.
	 *
	 * @return mixed Value of the option.
	 */
	public function default_header_font( $value ) {
		if ( ! $value ) {
			$value = array(
				array(
					'value'  => 'Alfa Slab One',
					'type'   => 'google',
					'weight' => 'bold',
				),
			);
		}

		return $value;
	}

	/**
	 * Alter Default Font.
	 *
	 * @param array $config Array of Config.
	 *
	 * @return array
	 */
	public function default_font( $config ) {
		if ( empty( $config['globalVariable']['fonts'] ) ) {
			$config['globalVariable']['fonts'] = $this->default_font_variable();

			return $config;
		}

		if ( ! empty( $config['globalVariable']['fonts'] ) ) {
			// Handle existing fonts.
			$theme_name   = get_stylesheet();
			$initial_font = get_option( 'gutenverse-font-init-' . $theme_name );

			if ( ! $initial_font ) {
				$result = array();
				$array1 = $config['globalVariable']['fonts'];
				$array2 = $this->default_font_variable();
				foreach ( $array1 as $item ) {
					$result[ $item['id'] ] = $item;
				}
				foreach ( $array2 as $item ) {
					$result[ $item['id'] ] = $item;
				}
				$fonts = array();
				foreach ( $result as $key => $font ) {
					$fonts[] = $font;
				}
				$config['globalVariable']['fonts'] = $fonts;

				update_option( 'gutenverse-font-init-' . $theme_name, true );
			}
		}

		return $config;
	}

	/**
	 * Default Font Variable.
	 *
	 * @return array
	 */
	public function default_font_variable() {
		return array(
            array (
  'id' => 'gv-font-primary',
  'name' => 'Primary',
  'font' => 
  array (
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '96',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
        'point' => '64',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '48',
      ),
    ),
    'weight' => '600',
    'spacing' => 
    array (
      'Desktop' => '-0.02',
      'Tablet' => '',
      'Mobile' => '',
    ),
    'font' => 
    array (
      'label' => 'DM Sans',
      'value' => 'DM Sans',
      'type' => 'google',
    ),
  ),
),array (
  'id' => 'gv-font-primary-alt',
  'name' => 'Primary Alt',
  'font' => 
  array (
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1.1',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '56',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
        'point' => '48',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '41',
      ),
    ),
    'font' => 
    array (
      'label' => 'DM Sans',
      'value' => 'DM Sans',
      'type' => 'google',
    ),
    'weight' => '600',
    'spacing' => 
    array (
      'Desktop' => '-0.02',
      'Tablet' => '',
      'Mobile' => '',
    ),
  ),
),array (
  'id' => 'gv-font-secondary',
  'name' => 'Secondary',
  'font' => 
  array (
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1.1',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '48',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
        'point' => '40',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '32',
      ),
    ),
    'spacing' => 
    array (
      'Desktop' => '-0.02',
      'Tablet' => '',
      'Mobile' => '',
    ),
    'weight' => '600',
    'font' => 
    array (
      'label' => 'DM Sans',
      'value' => 'DM Sans',
      'type' => 'google',
    ),
  ),
),array (
  'id' => 'gv-font-feature',
  'name' => 'Feature',
  'font' => 
  array (
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1.2',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
      ),
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '32',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '24',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
        'point' => '28',
      ),
    ),
    'font' => 
    array (
      'label' => 'DM Sans',
      'value' => 'DM Sans',
      'type' => 'google',
    ),
    'weight' => '600',
  ),
),array (
  'id' => 'gv-font-feature-secondary',
  'name' => 'Feature Secondary',
  'font' => 
  array (
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1.2',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
      ),
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '24',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '20',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
        'point' => '22',
      ),
    ),
    'font' => 
    array (
      'label' => 'DM Sans',
      'value' => 'DM Sans',
      'type' => 'google',
    ),
    'weight' => '600',
  ),
),array (
  'id' => 'gv-font-meta',
  'name' => 'Meta',
  'font' => 
  array (
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1.3',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
      ),
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '18',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '16',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
        'point' => '17',
      ),
    ),
    'font' => 
    array (
      'label' => 'DM Sans',
      'value' => 'DM Sans',
      'type' => 'google',
    ),
    'weight' => '600',
  ),
),array (
  'id' => 'gv-font-text',
  'name' => 'Text',
  'font' => 
  array (
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1.5',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
      ),
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '16',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '14',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
        'point' => '15',
      ),
    ),
    'weight' => '400',
    'font' => 
    array (
      'label' => 'DM Sans',
      'value' => 'DM Sans',
      'type' => 'google',
    ),
  ),
),array (
  'id' => 'gv-font-text-hero',
  'name' => 'Text Hero',
  'font' => 
  array (
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1.5',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
      ),
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '18',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '16',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
        'point' => '17',
      ),
    ),
    'font' => 
    array (
      'label' => 'DM Sans',
      'value' => 'DM Sans',
      'type' => 'google',
    ),
    'weight' => '400',
  ),
),array (
  'id' => 'gv-font-text-small',
  'name' => 'Text Small',
  'font' => 
  array (
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1.5',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
      ),
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '14',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '13',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
        'point' => '13',
      ),
    ),
    'font' => 
    array (
      'label' => 'DM Sans',
      'value' => 'DM Sans',
      'type' => 'google',
    ),
    'weight' => '400',
  ),
),array (
  'id' => 'gv-font-subheading',
  'name' => 'Subheading',
  'font' => 
  array (
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
      ),
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '14',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
        'point' => '13',
      ),
    ),
    'weight' => '600',
    'font' => 
    array (
      'label' => 'DM Sans',
      'value' => 'DM Sans',
      'type' => 'google',
    ),
  ),
),array (
  'id' => 'gv-font-button-primary',
  'name' => 'Button Primary',
  'font' => 
  array (
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
      ),
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '18',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '16',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
        'point' => '17',
      ),
    ),
    'font' => 
    array (
      'label' => 'DM Sans',
      'value' => 'DM Sans',
      'type' => 'google',
    ),
    'weight' => '600',
  ),
),array (
  'id' => 'gv-font-button-secondary',
  'name' => 'Button Secondary',
  'font' => 
  array (
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'em',
        'point' => '1',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
      ),
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
        'point' => '16',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
        'point' => '14',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
        'point' => '15',
      ),
    ),
    'font' => 
    array (
      'label' => 'DM Sans',
      'value' => 'DM Sans',
      'type' => 'google',
    ),
    'weight' => '600',
  ),
),array (
  'id' => 'gv-font-form-label',
  'name' => 'Form Label',
  'font' => 
  array (
    'lineHeight' => 
    array (
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
      'Desktop' => 
      array (
        'unit' => 'px',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
      ),
    ),
    'size' => 
    array (
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
      'Desktop' => 
      array (
        'unit' => 'px',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
      ),
    ),
  ),
),array (
  'id' => 'gv-font-heading-404',
  'name' => 'Heading 404',
  'font' => 
  array (
    'lineHeight' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
      ),
    ),
    'size' => 
    array (
      'Desktop' => 
      array (
        'unit' => 'px',
      ),
      'Mobile' => 
      array (
        'unit' => 'px',
      ),
      'Tablet' => 
      array (
        'unit' => 'px',
      ),
    ),
  ),
),
		);
	}

	/**
	 * Register Block Pattern.
	 */
	public function register_block_patterns() {
		new Block_Patterns();
	}

	/**
	 * Enqueue scripts and styles.
	 */
	public function dashboard_scripts() {
		if ( is_admin() ) {
			// enqueue css.
			

			wp_enqueue_script('wp-api-fetch');

			wp_localize_script( 'wp-api-fetch', 'GutenThemeConfig', $this->theme_config() );
		}
	}

	/**
	 * Check if plugin is installed.
	 *
	 * @param string $plugin_slug plugin slug.
	 * 
	 * @return boolean
	 */
	public function is_installed( $plugin_slug ) {
		$all_plugins = get_plugins();
		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_dir = dirname( $plugin_file );

			if ( $plugin_dir === $plugin_slug ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Register static data to be used in theme's js file
	 */
	public function theme_config() {
		global $pagenow;
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$active_plugins = get_option( 'active_plugins' );
		$plugins = array();
		$installed_plugins = get_plugins();
		$installed_plugin_versions = array();
		foreach ( $active_plugins as $active ) {
			$plugin_name = explode( '/', $active )[0];
			$plugins[]   = $plugin_name;
			$installed_plugin_versions[ $plugin_name ] = isset( $installed_plugins[ $active ] ) ? $installed_plugins[ $active ]['Version'] : '1.0.0';
		}

		$config = array(
			'home_url'      => home_url(),
			'activeTheme'   => get_option( 'stylesheet' ),
			'active_plugins'=> $active_plugins,
			'version'       => TECVISORY_VERSION,
			'images'        => get_template_directory_uri() . '/assets/img/',
			'title'         => esc_html__( 'Tecvisory', 'tecvisory' ),
			'description'   => esc_html__( 'Tecvisory is crafted for the modern digital era. With a sophisticated dark-mode design at its core, this theme delivers a visually striking presence that commands attention - the moment visitors land on your page.
Every pixel is intentional. Every layout, purposeful. Whether you\'re a solo tech consultant, a growing startup, or an established digital agency, this theme adapts seamlessly to your brand and scales with your ambition.', 'tecvisory' ),
			'pluginTitle'   => esc_html__( 'Plugin Requirement', 'tecvisory' ),
			'pluginDesc'    => esc_html__( 'This theme require some plugins. Please make sure all the plugin below are installed and activated.', 'tecvisory' ),
			'note'          => '',
			'note2'         => '',
			'demo'          => '',
			'demoUrl'       => esc_url( 'https://gutenverse.com/demo?name=tecvisory' ),
			'install'       => '',
			'installText'   => esc_html__( 'Install Gutenverse Plugin', 'tecvisory' ),
			'activateText'  => esc_html__( 'Activate Gutenverse Plugin', 'tecvisory' ),
			'doneText'      => esc_html__( 'Gutenverse Plugin Installed', 'tecvisory' ),
			'dashboardPage' => admin_url( 'themes.php?page=tecvisory-dashboard' ),
			'logo'          => trailingslashit( get_template_directory_uri() ) . 'assets/img/black-logo.png',
			'slug'          => 'tecvisory',
			'upgradePro'    => esc_url( 'https://gutenverse.com/pricing' ),
			'supportLink'   => esc_url( 'https://support.jegtheme.com/forums/forum/fse-themes/' ),
			'libraryApi'    => esc_url( 'https://gutenverse.com/wp-json/gutenverse-server/v1' ),
			'docsLink'      => esc_url( 'https://gutenverse.com/docs' ),
			'pages'         => array(
				
			),
			'plugins'      => array(
				array(
					'slug'       		=> 'gutenverse',
					'title'      		=> esc_html__( 'Gutenverse', 'tecvisory' ),
					'short_desc' 		=> esc_html__( 'GUTENVERSE – GUTENBERG BLOCKS AND WEBSITE BUILDER FOR SITE EDITOR, TEMPLATE LIBRARY, POPUP BUILDER, ADVANCED ANIMATION EFFECTS, COMPLETE FEATURE ECOSYSTEM, 45+ FREE USER-FRIENDLY BLOCKS', 'tecvisory' ),
					'active'    		=> in_array( 'gutenverse', $plugins, true ),
					'installed'  		=> $this->is_installed( 'gutenverse' ),
					'req_version'    	=> '3.4.6',
					'installed_version' => isset( $installed_plugins['gutenverse/gutenverse.php']['Version'] ) ? $installed_plugins['gutenverse/gutenverse.php']['Version'] : '',
					'icons'      		=> array (
  '1x' => 'https://ps.w.org/gutenverse/assets/icon-128x128.gif?rev=3132408',
  '2x' => 'https://ps.w.org/gutenverse/assets/icon-256x256.gif?rev=3132408',
),
					'download_url'      => '',
				),
				array(
					'slug'       		=> 'gutenverse-companion',
					'title'      		=> esc_html__( 'Gutenverse Companion', 'tecvisory' ),
					'short_desc' 		=> esc_html__( 'A companion plugin designed specifically to enhance and extend the functionality of Gutenverse base themes. This plugin integrates seamlessly with the base themes, providing additional features, customization options, and advanced tools to optimize the overall user experience and streamline the development process.', 'tecvisory' ),
					'active'    		=> in_array( 'gutenverse-companion', $plugins, true ),
					'installed'  		=> $this->is_installed( 'gutenverse-companion' ),
					'req_version'    	=> '2.1.2',
					'installed_version' => isset( $installed_plugins['gutenverse-companion/gutenverse-companion.php']['Version'] ) ? $installed_plugins['gutenverse-companion/gutenverse-companion.php']['Version'] : '',
					'icons'      		=> array (
  '1x' => 'https://ps.w.org/gutenverse-companion/assets/icon-128x128.png?rev=3162415',
),
					'download_url'      => '',
				),
				array(
					'slug'       		=> 'gutenverse-form',
					'title'      		=> esc_html__( 'Gutenverse Form', 'tecvisory' ),
					'short_desc' 		=> esc_html__( 'GUTENVERSE FORM – FORM BUILDER FOR GUTENBERG BLOCK EDITOR, MULTI-STEP FORMS, CONDITIONAL LOGIC, PAYMENT, CALCULATION, 15+ FREE USER-FRIENDLY FORM BLOCKS', 'tecvisory' ),
					'active'    		=> in_array( 'gutenverse-form', $plugins, true ),
					'installed'  		=> $this->is_installed( 'gutenverse-form' ),
					'req_version'    	=> '2.5.3',
					'installed_version' => isset( $installed_plugins['gutenverse-form/gutenverse-form.php']['Version'] ) ? $installed_plugins['gutenverse-form/gutenverse-form.php']['Version'] : '',
					'icons'      		=> array (
  '1x' => 'https://ps.w.org/gutenverse-form/assets/icon-128x128.png?rev=3135966',
),
					'download_url'      => '',
				)
			),
			'assign'       => array(
				array(
						'title' => 'Home',
						'page'  => 'Home',
						'demo'  => 'https://fse.jegtheme.com/tecvisory',
						'slug'  => 'full-width',
						'thumb' => trailingslashit( get_template_directory_uri() ) . 'assets/img/ss-tecvisory-home-cover.webp',
					),
				array(
						'title' => 'Services',
						'page'  => 'Services',
						'demo'  => 'https://fse.jegtheme.com/tecvisory/services',
						'slug'  => 'full-width',
						'thumb' => trailingslashit( get_template_directory_uri() ) . 'assets/img/ss-tecvisory-services-cover.webp',
					),
				array(
						'title' => 'Projects',
						'page'  => 'Projects',
						'demo'  => 'https://fse.jegtheme.com/tecvisory/projects',
						'slug'  => 'full-width',
						'thumb' => trailingslashit( get_template_directory_uri() ) . 'assets/img/ss-tecvisory-projects-cover.webp',
					),
				array(
						'title' => 'Single Project',
						'page'  => 'Single Project',
						'demo'  => 'https://fse.jegtheme.com/tecvisory/single-project',
						'slug'  => 'full-width',
						'thumb' => trailingslashit( get_template_directory_uri() ) . 'assets/img/ss-tecvisory-single-project-cover.webp',
					),
				array(
						'title' => 'About',
						'page'  => 'About',
						'demo'  => 'https://fse.jegtheme.com/tecvisory/about',
						'slug'  => 'full-width',
						'thumb' => trailingslashit( get_template_directory_uri() ) . 'assets/img/ss-tecvisory-about-cover.webp',
					),
				array(
						'title' => 'Contact',
						'page'  => 'Contact',
						'demo'  => 'https://fse.jegtheme.com/tecvisory/contact',
						'slug'  => 'full-width',
						'thumb' => trailingslashit( get_template_directory_uri() ) . 'assets/img/ss-tecvisory-contact-cover.webp',
					),
				array(
						'title' => 'Pricing',
						'page'  => 'Pricing',
						'demo'  => 'https://fse.jegtheme.com/tecvisory/pricing',
						'slug'  => 'full-width',
						'thumb' => trailingslashit( get_template_directory_uri() ) . 'assets/img/ss-tecvisory-pricing-cover.webp',
					),
				array(
						'title' => 'FAQ',
						'page'  => 'FAQ',
						'demo'  => 'https://fse.jegtheme.com/tecvisory/faq',
						'slug'  => 'full-width',
						'thumb' => trailingslashit( get_template_directory_uri() ) . 'assets/img/ss-tecvisory-faq-cover.webp',
					),
				array(
						'title' => 'Blog',
						'page'  => 'Blog',
						'demo'  => 'https://fse.jegtheme.com/tecvisory/blog',
						'slug'  => 'full-width',
						'thumb' => trailingslashit( get_template_directory_uri() ) . 'assets/img/ss-tecvisory-blog-cover.webp',
					)
			),
			'dashboardData'=> array(
				
			),
			'isThemeforest' => true,
			'is_exclusive' => true,
			'theme_type' => apply_filters( 'gutenverse_jegtheme_theme_type', 'normal' ),
		);

		if ( 'themes.php' === $pagenow && isset( $_GET['page'] ) && 'tecvisory-dashboard' === $_GET['page'] ) {
			$admin_config = array(
				
			);
			$config = array_merge( $config, $admin_config );
		}

		
				$active_slug      = get_stylesheet();
				$inserted_dummies = get_option( 'gutenverse_' . $active_slug . '_dummy_inserted', array() );
				$arr_options      = array(
					'menus_imported'         => ! empty( $inserted_content['menus'] ) ? true : false,
					'dummy_content_imported' => ! empty( $inserted_dummies ) ? true : false,
				);
				$config['imported_options'] = $arr_options;
		

		return $config;
	}
	
}
