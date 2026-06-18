<?php
/**
 * Theme Functions
 *
 * @author Jegstudio
 * @package tecvisory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

defined( 'TECVISORY_VERSION' ) || define( 'TECVISORY_VERSION', '1.0.0' );
defined( 'TECVISORY_DIR' ) || define( 'TECVISORY_DIR', trailingslashit( get_template_directory() ) );

defined( 'GUTENVERSE_COMPANION_REQUIRED_VERSION' ) || define( 'GUTENVERSE_COMPANION_REQUIRED_VERSION', '2.1.2' );
defined( 'GUTENVERSE_FRAMEWORK_REQUIRED_VERSION' ) || define( 'GUTENVERSE_FRAMEWORK_REQUIRED_VERSION', '2.0.0' );

require get_parent_theme_file_path( 'inc/autoload.php' );

Tecvisory\Init::instance();
