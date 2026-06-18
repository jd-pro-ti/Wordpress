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
 * Plugin Notice Class
 *
 * @package tecvisory
 */
class Plugin_Notice {

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
	public function __construct() {
		$this->load_hooks();
	}

	/**
	 * Load initial hooks.
	 */
	private function load_hooks() {
		add_action( 'admin_notices', array( $this, 'notice_install_plugin' ) );
	}

	/**
	 * Show notification to install Gutenverse Plugin.
	 */
	public function notice_install_plugin() {
		// Skip if gutenverse block activated.
		if ( defined( 'GUTENVERSE' ) && defined( 'GUTENVERSE_COMPANION' ) ) {
			return;
		}

		$active_plugins = get_option( 'active_plugins' );
		$plugins        = array();
		foreach ( $active_plugins as $active ) {
			$plugins[] = explode( '/', $active )[0];
		}
		$all_plugin          = get_plugins();
		$plugins_required    = array(
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
        );
		$actions             = array();
		$count_plugin_active = 0;
		foreach ( $plugins_required as $plugin ) {
			$slug   = $plugin['slug'];
			$path   = "$slug/$slug.php";
			$active = in_array( $path, $active_plugins, false );

			if ( isset( $all_plugin[ $path ] ) ) {
				if ( $active ) {
					$actions[ $slug ] = 'active';
					++$count_plugin_active;
				} else {
					$actions[ $slug ] = 'inactive';
				}
			} else {
				$actions[ $slug ] = '';
			}
		}

		$count_plugin_requiored = count( $plugins_required );
		if ( $count_plugin_active === $count_plugin_requiored ) {
			return;
		}

		wp_register_style( 'tecvisory-theme-notice', false );
		wp_enqueue_style( 'tecvisory-theme-notice' );
		ob_start();
		?>
		
            .install-gutenverse-plugin-notice {
                border: 1px solid #E6E6EF;
                position: relative;
                overflow: hidden;
                padding: 0 !important;
                margin-bottom: 30px !important;
                background: url( <?php echo esc_url( get_template_directory_uri() . '/assets/img/background-banner.png' ); ?> );
                background-size: cover;
                background-position: center;
            }

            .install-gutenverse-plugin-notice .gutenverse-notice-content {
                display: flex;
                align-items: center;
                position: relative;
            }

            .gutenverse-notice-text, .gutenverse-notice-image {
                width: 50%;
            }

            .gutenverse-notice-text {
                padding: 40px 0 40px 40px;
                position: relative;
                z-index: 2;
            }

            .install-gutenverse-plugin-notice img {
                max-height: 100%;
                display: flex;
                position: absolute;
                top: 0;
                right: 0;
                bottom: 0;
            }

            .install-gutenverse-plugin-notice:after {
                content: "";
                position: absolute;
                left: 0;
                top: 0;
                height: 100%;
                width: 5px;
                display: block;
                background: linear-gradient(to bottom, #68E4F4, #4569FF, #F045FF);
            }

            .install-gutenverse-plugin-notice .notice-dismiss {
                top: 20px;
                right: 20px;
                padding: 0;
                background: white;
                border-radius: 6px;
            }

            .install-gutenverse-plugin-notice .notice-dismiss:before {
                content: "\f335";
                font-size: 17px;
                width: 25px;
                height: 25px;
                line-height: 25px;
                border: 1px solid #E6E6EF;
                border-radius: 3px;
            }

            .install-gutenverse-plugin-notice h3 {
                margin-top: 5px;
                margin-bottom: 15px;
                font-weight: 600;
                font-size: 25px;
                line-height: 1.4em;
            }

            .install-gutenverse-plugin-notice h3 span {
                font-weight: 700;
                background-clip: text !important;
                -webkit-text-fill-color: transparent;
                background: linear-gradient(80deg, rgba(208, 77, 255, 1) 0%,rgba(69, 105, 255, 1) 48.8%,rgba(104, 228, 244, 1) 100%);
            }

            .install-gutenverse-plugin-notice p {
                font-size: 13px;
                font-weight: 400;
                margin: 5px 100px 20px 0 !important;
            }

            .install-gutenverse-plugin-notice .gutenverse-bottom {
                display: flex;
                align-items: center;
                margin-top: 30px;
            }

            .install-gutenverse-plugin-notice a {
                text-decoration: none;
                margin-right: 20px;
            }

            .install-gutenverse-plugin-notice a.gutenverse-button {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", serif;
                text-decoration: none;
                cursor: pointer;
                font-size: 12px;
                line-height: 18px;
                border-radius: 5px;
                background: #3B57F7;
                color: #fff;
                padding: 10px 15px;
                font-weight: 500;
                background: linear-gradient(to left, #68E4F4, #4569FF, #F045FF);
                transition: transform 0.5s ease, color 0.5s ease;
				width: 100px;
				text-align: center;
            }

			.install-gutenverse-plugin-notice .gutenverse-bottom .installing-indicator {
				margin: 0 !important;
				max-width: 50%;
			}

            .install-gutenverse-plugin-notice a.gutenverse-button:hover {
                color: hsla(0, 0%, 100%, .749);
                transform: scale(.94);
            }

            #gutenverse-install-plugin.loader:after {
                display: block;
                content: url(<?php echo esc_url( get_template_directory_uri() . '/assets/img/icon-loading.svg' ); ?>);
				border-radius: 50%;
				width: 100%;
				height: 100%;
                -webkit-animation: spin 2s linear infinite;
                animation: spin 2s linear infinite;
            }

            @-webkit-keyframes spin {
                0% {
                    -webkit-transform: rotate(0deg);
                }
                100% {
                    -webkit-transform: rotate(360deg);
                }
            }

            @keyframes spin {
                0% {
                    transform: rotate(0deg);
                }
                100% {
                    transform: rotate(360deg);
                }
            }

            @media screen and (max-width: 1024px) {
                .gutenverse-notice-text {
                    width: 100%;
                }

                .gutenverse-notice-image {
                    display: none;
                }
            }
        
		<?php

		$custom_style = ob_get_clean();

		wp_add_inline_style( 'tecvisory-theme-notice', $custom_style );
		wp_register_script(
			'tecvisory-theme-notice',
			'',
			array( 'wp-api-fetch' ),
			'1.0.0',
			true
		);

		wp_enqueue_script( 'tecvisory-theme-notice' );

		ob_start();
		
		?>
		
        var promises = [];
        var actions = <?php echo wp_json_encode( $actions ); ?>;
        let site_url = '<?php echo admin_url(); ?>';

        function sequenceInstall (plugin, index, total) {
            return new Promise((resolve, reject) => {
				const buttonWrapper = document.querySelector('.notice.install-gutenverse-plugin-notice .gutenverse-bottom');
				const checkIndicator = buttonWrapper.querySelector('.installing-indicator');
				if (checkIndicator) {
					checkIndicator.remove();
				}
				if (buttonWrapper) {
					const p = document.createElement('p');
					p.className = 'installing-indicator';
					p.textContent = index + 1 + '/' + total + ' Installing ' + plugin.title;
					buttonWrapper.appendChild(p);
				}
				if (!plugin) return resolve();

				const slug = plugin.slug;
				const path = `${slug}/${slug}`;

				let request;

				switch (actions[slug]) {
					case 'active':
						// Already active – nothing to do
						return resolve();

					case 'inactive':
						request = wp.apiFetch({
							path: `wp/v2/plugins/plugin?plugin=${path}`,
							method: 'POST',
							data: {
								status: 'active'
							}
						});
						break;

					default:
						request = wp.apiFetch({
							path: 'wp/v2/plugins',
							method: 'POST',
							data: {
								slug: slug,
								status: 'active'
							}
						});
						break;
				}

				request
					.then(() => 
						setTimeout(() => {
							resolve()
						}, 500)
					)
					.catch((error) => {
						console.error(`Failed to install/activate ${slug}:`, error);
						resolve(); // Continue with the sequence even if one fails
					});
			});
        };

        document.addEventListener('DOMContentLoaded', function () {
			const button = document.getElementById('gutenverse-install-plugin');

			if (!button) return;

			button.addEventListener('click', function (e) {
				const hasFinishClass = button.classList.contains('finished');
				const hasLoaderClass = button.classList.contains('loader');

				if (!hasFinishClass) {
					e.preventDefault(); // stop navigation
				}

				if (!hasLoaderClass && !hasFinishClass) {
					const plugins = <?php echo wp_json_encode( $plugins_required ); ?>;
					button.classList.add('loader');
					button.textContent = '';

					let sequence = Promise.resolve();

					plugins.forEach((plugin, index) => {
						sequence = sequence.then(() => sequenceInstall(plugin, index, plugins.length));
					});

					sequence.then(() => {
						window.location.href = site_url + '/themes.php?page=theme-wizard'
					});
				}
			});
		});
        
		<?php
		$custom_script = ob_get_clean();
		wp_add_inline_script( 'tecvisory-theme-notice', $custom_script );

		?>
		<div class="notice install-gutenverse-plugin-notice">
            <div class="gutenverse-notice-inner">
                <div class="gutenverse-notice-content">
                    <div class="gutenverse-notice-text">
                        <h3><?php esc_html_e( 'Complete setup to activate all ', 'tecvisory' ); ?> <span>Tecvisory features!</span></h3> 
                        <p><?php esc_html_e( 'Complete the setup to unlock all features. The prepare wizard will install mandatory plugins and redirect you to the wizard page, to help you maximize your Tecvisory theme.' , 'tecvisory' ); ?></p>
                        <div class="gutenverse-bottom">
                            <a class="gutenverse-button" id="gutenverse-install-plugin" href="#">
                                <?php echo esc_html( __( 'Continue Setup', 'tecvisory' ) ); ?>
                            </a>
                        </div>
                    </div>
                    <div class="gutenverse-notice-image">
                        <img src="<?php echo esc_url( trailingslashit( get_template_directory_uri() ) ) . '/assets/img/banner-install-gutenverse.png'; ?>"/>
                    </div>
                </div>
            </div>
        </div>
		<?php
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
			$plugin_dir = dirname($plugin_file);

			if ($plugin_dir === $plugin_slug) {
				return true;
			}
		}

		return false;
	}
}
