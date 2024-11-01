<?php
/**
 * Class fill cart setting.
 *
 * Section in the administrator part to manage the plugin.
 *
 * @author   Manuel Muñoz Rodríguez <mmr010496@gmail.com>
 * @category WordPress
 * @package  Plugin
 * @version  0.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings
 */
class FWC_Settings {
	/**
	 * Settings
	 *
	 * @var array
	 */
	private $fwc_settings;

	/**
	 * Settings
	 *
	 * @var array
	 */
	private $fwc_settings_statistics;

	/**
	 * Construct of class
	 */
	public function __construct() {
		add_action( 'wp_ajax_fwc_update_option_number', array( $this, 'fwc_update_option_number' ) );
		add_action( 'wp_ajax_nopriv_fwc_update_option_number', array( $this, 'fwc_update_option_number' ) );
		add_action( 'wp_ajax_fwc_filter_users', array( $this, 'fwc_filter_users' ) );
		add_action( 'wp_ajax_nopriv_fwc_filter_users', array( $this, 'fwc_filter_users' ) );
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( FWC_PLUGIN ), array( $this, 'plugin_action_links' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_files' ) );
	}

	/**
	 * Adds plugin page.
	 *
	 * @return void
	 */
	public function add_plugin_page() {
		add_submenu_page(
			'woocommerce',
			esc_html__( 'Fill Cart', 'fill-woo-cart-automatically' ),
			esc_html__( 'Fill Cart', 'fill-woo-cart-automatically' ),
			'manage_options',
			'fwc',
			array( $this, 'create_admin_page' ),
			99
		);
	}

	/**
	 * Create admin page.
	 *
	 * @return void
	 */
	public function create_admin_page() {
		$this->fwc_settings            = get_option( 'fwc_settings' );
		$this->fwc_settings_statistics = get_option( 'fwc_settings_statistics' );

		$tab = 'fwc';
		if ( isset( $_GET['tab'] ) ) {
			$tab = sanitize_text_field( $_GET['tab'] );
		}

		?>
		<div class="wrap">
			<h2><?php echo esc_html__( 'Configurations to fill carts', 'fill-woo-cart-automatically' ) ?></h2>
			<p></p>
			<?php 
			settings_errors();
			?>

			<h2 class="nav-tab-wrapper">
				<a href="?page=<?php echo esc_html( $_GET['page'] ); ?>" class="nav-tab <?php
				if ( $tab === 'fwc' ) {
					?>
					nav-tab-active
					<?php
				}
				?>"><?php echo esc_html__( 'Options', 'fill-woo-cart-automatically' ); ?></a>
				<a href="?page=<?php echo esc_html( $_GET['page'] ); ?>&tab=statistics" class="nav-tab <?php
				if ( $tab === 'statistics' ) {
					?>
					nav-tab-active
					<?php
				}
				?>"><?php echo esc_html__( 'Statistics', 'fill-woo-cart-automatically' ); ?></a>
			</h2>

			<?php	if ( 'fwc' === $tab ) { ?>
				<form method="post" action="options.php">
					<?php
					wp_enqueue_style( 'fwc-admin' );
					wp_enqueue_script( 'fwc-select2-multiple' );
					wp_enqueue_script( 'fwc-select-multiple' );
					settings_fields( 'fwc_settings' );
					do_settings_sections( 'fwc-general-settings' );
					submit_button();
					?>
				</form>
			<?php }
			if ( 'statistics' === $tab ) { ?>
				<form method="post" action="options.php">
					<?php
					settings_fields( 'fwc_settings_statistics' );
					do_settings_sections( 'fwc-statistics-settings' );
					submit_button();
					?>
				</form>

			<?php
				if ( 'on' === $this->fwc_settings_statistics['fwc_save_statistics'] ) {
					wp_enqueue_style( 'fwc-stadistics' );
					$this->fwc_show_statictis();
				}
			}
			?>
		</div>
		<?php
	}

	/**
	 * Init for page
	 *
	 * @return void
	 */
	public function page_init() {
		// General Settings.
		register_setting(
			'fwc_settings',
			'fwc_settings',
			array( $this, 'sanitize_fields' )
		);

		register_setting(
			'fwc_settings_statistics',
			'fwc_settings_statistics',
			array( $this, 'sanitize_fields' )
		);

		// INIT CONFIGURATIONS.
		add_settings_section(
			'fwc_setting_section',
			__( 'Basic settings', 'fill-woo-cart-automatically' ),
			array( $this, 'fwc_section_init' ),
			'fwc-general-settings'
		);

		add_settings_field(
			'fwc_setting_products',
			__( 'Products that will appear in the cart', 'fill-woo-cart-automatically' ),
			array( $this, 'fwc_setting_products_callback' ),
			'fwc-general-settings',
			'fwc_setting_section',
		);

		add_settings_field(
			'fwc_setting_fill_cart',
			__( 'Autocomplete user cart through cookie', 'fill-woo-cart-automatically' ),
			array( $this, 'fwc_setting_fill_cart_callback' ),
			'fwc-general-settings',
			'fwc_setting_section',
		);

		add_settings_field(
			'fwc_setting_fill_cart_time',
			__( 'Automatically fill cart after X time (in seconds)', 'fill-woo-cart-automatically' ),
			array( $this, 'fwc_setting_fill_cart_time_callback' ),
			'fwc-general-settings',
			'fwc_setting_section',
		);

		add_settings_field(
			'fwc_setting_fill_cart_link_check',
			__( 'Fill cart using a link', 'fill-woo-cart-automatically' ),
			array( $this, 'fwc_setting_fill_cart_link_check_callback' ),
			'fwc-general-settings',
			'fwc_setting_section',
		);

		add_settings_field(
			'fwc_setting_fill_cart_link',
			__( 'Fill cart via link', 'fill-woo-cart-automatically' ),
			array( $this, 'fwc_setting_fill_cart_link_callback' ),
			'fwc-general-settings',
			'fwc_setting_section',
		);

		// STATISTICS CONFIGURATIONS.
		add_settings_section(
			'fwc_statistics_section',
			__( 'Statistics settings', 'fill-woo-cart-automatically' ),
			array( $this, 'fwc_section_statistics' ),
			'fwc-statistics-settings'
		);

		add_settings_field(
			'fwc_save_statistics',
			__( 'Save user statistics', 'fill-woo-cart-automatically' ),
			array( $this, 'fwc_save_statistics_callback' ),
			'fwc-statistics-settings',
			'fwc_statistics_section',
		);
	}

	/**
	 * Sanitize fields before saves in DB
	 *
	 * @param array $input Input fields.
	 * @return array
	 */
	public function sanitize_fields( $input ) {
		$sanitary_values = array();
		$settings_keys   = array(
			'fwc_setting_products',
			'fwc_setting_fill_cart',
			'fwc_setting_fill_cart_time',
			'fwc_setting_fill_cart_link_check',
			'fwc_setting_fill_cart_link',
			'fwc_save_statistics',
		);

		foreach ( $settings_keys as $key ) {
			if ( isset( $input[ $key ] ) ) {
				if ( 'fwc_setting_products' === $key ) {
					$productos = array();
					foreach ( $input[ $key ] as $num => $value ) {
						$value       = sanitize_text_field( $value );
						$productos[ $num ] = $value;
					}
					$sanitary_values[ $key ] = $productos;
				} else {
					$sanitary_values[ $key ] = sanitize_text_field( $input[ $key ] );
				}
			}
		}

		return $sanitary_values;
	}

	/**
	 * Info section.
	 *
	 * @return void
	 */
	public function fwc_section_init() {
		echo '<p>' . esc_html__( 'Here you can configure how the cart of your store will be filled.', 'fill-woo-cart-automatically' ) . '</p>';
	}

	/**
	 * Info section.
	 *
	 * @return void
	 */
	public function fwc_section_statistics() {
		echo '<p>' . esc_html__( 'Here you can see information about user interaction.', 'fill-woo-cart-automatically' ) . '</p>';
	}

	/**
	 * Call back for fwc_setting_products
	 *
	 * @return void
	 */
	public function fwc_setting_products_callback() {
		if ( function_exists( 'wc_get_products' ) ) {
			$products = wc_get_products(
				array(
					'limit'        => -1,
					'status'       => 'publish',
					'stock_status' => 'instock',
				),
			);
			echo '<select name="fwc_settings[fwc_setting_products][]" multiple class="fwc-multiselect">';
			foreach ( $products as $prod_inf ) {
				if ( $prod_inf->is_type( 'variable' ) ) {
					foreach ( $prod_inf->get_available_variations() as $key => $variation ) {
						$name = $prod_inf->get_name() . ' ';
						if ( 1 == $variation['is_purchasable'] ) {
							foreach ( $variation['attributes'] as $key => $attribute ) {
								if ( $key !== array_key_last( $variation['attributes'] ) ) {
									$name .= $attribute . ' - ';
								} else {
									$name .= $attribute;
								}
							}
							echo '<option value="' . esc_attr( $variation['variation_id'] ) . '"';
							if ( null !== $this->fwc_settings['fwc_setting_products'] ) {
								if ( in_array( $variation['variation_id'], $this->fwc_settings['fwc_setting_products'] ) ) {
									echo 'selected';
								}
							}
							echo '>' . esc_html( $name ) . '</option>';
						}
					}
				} else {
					echo '<option value="' . esc_attr( $prod_inf->get_id() ) . '"';
					if ( null !== $this->fwc_settings['fwc_setting_products'] ) {
						if ( in_array( $prod_inf->get_id(), $this->fwc_settings['fwc_setting_products'] ) ) {
							echo 'selected';
						}
					}
					echo '>' . esc_html( $prod_inf->get_name() ) . '</option>';
				}
			}
			echo '</select>';
		}
	}

	/**
	 * Call back for fwc_setting_fill_cart
	 *
	 * @return void
	 */
	public function fwc_setting_fill_cart_callback() {
		echo '<input type="checkbox" name="fwc_settings[fwc_setting_fill_cart]" id="fwc_setting_fill_cart" ';
		if ( 'on' === $this->fwc_settings['fwc_setting_fill_cart'] ) {
			echo 'checked';
		}
		echo ' />';
	}

	/**
	 * Call back for fwc_save_statistics
	 *
	 * @return void
	 */
	public function fwc_save_statistics_callback() {
		echo '<input type="checkbox" name="fwc_settings_statistics[fwc_save_statistics]" id="fwc_save_statistics" ';
		if ( 'on' === $this->fwc_settings_statistics['fwc_save_statistics'] ) {
			echo 'checked';
		}
		echo ' />';
	}

	/**
	 * Call back for fwc_setting_fill_cart_time
	 *
	 * @return void
	 */
	public function fwc_setting_fill_cart_time_callback() {
		echo '<input type="number" min="0" name="fwc_settings[fwc_setting_fill_cart_time]" id="fwc_setting_fill_cart_time" value="' . esc_attr( $this->fwc_settings['fwc_setting_fill_cart_time'] ) . '" />';
		echo '<p>' . esc_html__( 'If you leave this field at 0, it will be given a value of 10 years in seconds', 'fill-woo-cart-automatically' ) . '</p>';
	}

	/**
	 * Call back for fwc_setting_fill_cart_link_check
	 *
	 * @return void
	 */
	public function fwc_setting_fill_cart_link_check_callback() {
		echo '<input type="checkbox" name="fwc_settings[fwc_setting_fill_cart_link_check]" id="fwc_setting_fill_cart_link_check" ';
		if ( 'on' === $this->fwc_settings['fwc_setting_fill_cart_link_check'] ) {
			echo 'checked';
		}
		echo ' />';
	}

	/**
	 * Call back for fwc_setting_fill_cart_link
	 *
	 * @return void
	 */
	public function fwc_setting_fill_cart_link_callback() {
		if ( is_multisite() ) {
			$link_add_cart = get_site_url( get_current_blog_id() ) . '/?fwc_fill_cart=on';
		} else {
			$link_add_cart = get_site_url() . '/?fwc_fill_cart=on';
		}
		if ( ! isset( $this->fwc_settings['fwc_setting_products'] ) || empty( $this->fwc_settings['fwc_setting_products'] ) ) {
			$link_add_cart = esc_html__( 'You have not chosen products', 'fill-woo-cart-automatically' );	
		}
		echo '<input style="width:500px;" type="text" name="fwc_settings[fwc_setting_fill_cart_link]" id="fwc_setting_fill_cart_link" value="' . esc_attr( $link_add_cart ) . '" disabled />';
		echo '<p>' . esc_html__( 'If you want to customize the fill cart url you just have to add at the end of the url ', 'fill-woo-cart-automatically' ) . esc_html( '?fwc_fill_cart=on' ) . '</p>';
		echo '<p>' . esc_html__( 'For example: https://www.example.com/sample-page/?fwc_fill_cart=on', 'fill-woo-cart-automatically' ) . '</p>';
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param mixed $links Plugin Action links.
	 *
	 * @return array
	 */
	public static function plugin_action_links( $links ) {
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=fwc' ) . '" aria-label="' . esc_attr__( 'See settings to fill the cart', 'fill-woo-cart-automatically' ) . '">' . esc_html__( 'Settings', 'fill-woo-cart-automatically' ) . '</a>',
		);
		return array_merge( $action_links, $links );
	}

	/**
	 * Load css and js files.
	 *
	 * @return void
	 */
	public function load_files() {
		wp_register_style(
			'fwc-admin',
			FWC_PLUGIN_URL . 'admin/assets/admin-style.css',
			array(),
			FWC_VERSION
		);
		wp_register_style(
			'fwc-stadistics',
			FWC_PLUGIN_URL . 'admin/assets/stadistics-style.css',
			array(),
			FWC_VERSION
		);
		wp_register_script(
			'fwc-select-multiple',
			FWC_PLUGIN_URL . 'admin/assets/select2.min.js',
			array(),
			FWC_VERSION,
			true
		);
		wp_register_script(
			'fwc-select2-multiple',
			FWC_PLUGIN_URL . 'admin/assets/multiselect.js',
			array(),
			FWC_VERSION,
			true
		);
		wp_enqueue_script( 
			'fwc-option-number',
			FWC_PLUGIN_URL . 'admin/assets/fwc-option-number.js',
			array(),
			FWC_VERSION,
			true
		);
		wp_localize_script(
			'fwc-option-number',
			'fwcRestart',
			array(
				'url'   => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'fwc_update_option_number_nonce' ),
				'text'  => esc_html__( 'Are you sure you want to clear the data log?', 'fill-woo-cart-automatically' ),
			)
		);
		wp_localize_script(
			'fwc-option-number',
			'fwcFilter',
			array(
				'url'   => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'fwc_filter_users_nonce' ),
			)
		);
	}

	/**
	 * Show statictis
	 *
	 * @return void
	 */
	public function fwc_show_statictis() {
		$option_count = get_option( 'fwc_count_times' );
		echo '<p class="fwc-title">' . esc_html__( 'Times the cart has been filled', 'fill-woo-cart-automatically' ) . '</p>';
		if ( false !== $option_count && 0 !== (int) $option_count ) {
			echo '<p class="fwc-count-times">' . esc_html( $option_count ) . esc_html__( ' times', 'fill-woo-cart-automatically' ) . '</p>';
			echo '<button class="fwc-count-button secondary-button button">' . esc_html__( 'Restart data', 'fill-woo-cart-automatically' ) . '</button>';
		} else {
			echo '<p class="fwc-count-times">' . esc_html__( 'Has not been used yet', 'fill-woo-cart-automatically' ) . '</p>';
		}
		echo '<div class="fwc-users">';
		$users = get_option( 'fwc_count_users' );
		if ( false !== $users && ! empty( $users ) ) {
			echo '<div class="filter-container">';
			echo '<p>' . esc_html__( 'Filter users', 'fill-woo-cart-automatically' ) . '</p>';
			echo '<select class="filter" >';
			echo '<option value="all">' . esc_html__( 'All users', 'fill-woo-cart-automatically' ) . '</option>';
			echo '<option value="registered">' . esc_html__( 'Registered users', 'fill-woo-cart-automatically' ) . '</option>';
			echo '<option value="anonymous">' . esc_html__( 'Anonymous', 'fill-woo-cart-automatically' ) . '</option>';
			echo '</select></div>';
			echo '<div class="fwc-information">';
			echo $this->fwc_show_users( $users );
			echo '</div>';
		}
		echo '</div>';
	}

	/**
	 * Ajax function to load info.
	 *
	 * @return void
	 */
	public function fwc_update_option_number() {
		check_ajax_referer( 'fwc_update_option_number_nonce', 'nonce' );
		if ( true ) {
			$html = esc_html__( 'Has not been used yet', 'fill-woo-cart-automatically' );
			update_option( 'fwc_count_times', 0 );
			update_option( 'fwc_count_users', array() );
			wp_send_json_success( $html );
		} else {
			wp_send_json_error( array( 'error' => 'Error' ) );
		}
	}

	/**
	 * Ajax function to load info
	 *
	 * @return void
	 */
	public function fwc_filter_users() {
		$filter = isset( $_POST['filter'] ) ? esc_attr( $_POST['filter'] ) : '';
		check_ajax_referer( 'fwc_filter_users_nonce', 'nonce' );
		if ( true ) {
			$users = get_option( 'fwc_count_users' );
			$html  = $this->fwc_show_users( $users, $filter );
			wp_send_json_success( $html );
		} else {
			wp_send_json_error( array( 'error' => 'Error' ) );
		}
	}

	/**
	 * Show users
	 *
	 * @param array  $users array users.
	 * @param string $filter filter users.
	 * @return string
	 */
	public function fwc_show_users( $users, $filter = 'all' ) {
		$html = '<div class="row">';
		$html .= '<p class="title">' . esc_html__( 'User name', 'fill-woo-cart-automatically' ) . '</p>';
		$html .= '<p class="title">' . esc_html__( 'Date', 'fill-woo-cart-automatically' ) . '</p>';
		$html .= '<p class="title">' . esc_html__( 'User IP', 'fill-woo-cart-automatically' ) . '</p>';
		$html .= '<p class="title">' . esc_html__( 'Browser information', 'fill-woo-cart-automatically' ) . '</p>';
		$html .= '</div>';
		foreach ( $users as $key => $user_info ) {
			if ( 'all' === $filter ) {
				if ( $key === array_key_last( $users ) ) {
					$html .= '<div class="row last">';
				} else {
					$html .= '<div class="row">';
				}
				if ( isset( $user_info['id'] ) ) {
					$usuario = get_userdata( $user_info['id'] );
					$html .= '<p><a href="' . esc_url( get_edit_user_link( $user_info['id'] ) ) . '">' . esc_html( $user_info['id'] ) . ' ' .  esc_html( $usuario->user_login ) . '</a></p>';
				} else {
					$html .= '<p>' . esc_html__( 'Anonymous', 'fill-woo-cart-automatically' ) . '</p>';
				}
				$html .= '<p>' . esc_html( $user_info['date'] ) . '</p>';
				$html .= '<p>' . esc_html( $user_info['ip'] ) . '</p>';
				$html .= '<p>' . esc_html( $user_info['pc'] ) . '</p>';
				$html .= '</div>';
			} elseif ( 'registered' === $filter && isset( $user_info['id'] ) ) {
				if ( $key === array_key_last( $users ) ) {
					$html .= '<div class="row last">';
				} else {
					$html .= '<div class="row">';
				}
				$usuario = get_userdata( $user_info['id'] );
				$html   .= '<p><a href="' . esc_url( get_edit_user_link( $user_info['id'] ) ) . '">' . esc_html( $user_info['id'] ) . ' ' .  esc_html( $usuario->user_login ) . '</a></p>';
				$html   .= '<p>' . esc_html( $user_info['date'] ) . '</p>';
				$html   .= '<p>' . esc_html( $user_info['ip'] ) . '</p>';
				$html   .= '<p>' . esc_html( $user_info['pc'] ) . '</p>';
				$html   .= '</div>';
			} elseif ( ! isset( $user_info['id'] ) ) {
				if ( $key === array_key_last( $users ) ) {
					$html .= '<div class="row last">';
				} else {
					$html .= '<div class="row">';
				}
				if ( ! isset( $user_info['id'] ) ) {
					$html .= '<p>' . esc_html__( 'Anonymous', 'fill-woo-cart-automatically' ) . '</p>';
				}
				$html .= '<p>' . esc_html( $user_info['date'] ) . '</p>';
				$html .= '<p>' . esc_html( $user_info['ip'] ) . '</p>';
				$html .= '<p>' . esc_html( $user_info['pc'] ) . '</p>';
				$html .= '</div>';
			}
		}
		return $html;
	}
}
if ( is_admin() ) {
	$fwc = new FWC_Settings();
}
