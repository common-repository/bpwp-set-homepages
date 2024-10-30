<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Class for repost methods.
 *
 * @package Wp_Set_Homepages
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// If class is exist, then don't execute this.
if ( ! class_exists( 'BPWP_Set_Homepages_Admin' ) ) {

	/**
	 * Class for Activity Re-post.
	 */
	class BPWP_Set_Homepages_Admin {

		/**
		 * Constructor for class.
		 */
		public function __construct() {

			// Register setting.
			add_action( 'admin_init', array( $this, 'bpwpsh_register_homepage_setting' ) );

			// Add field to allowed options to save value in options data.
			add_filter( 'allowed_options', array( $this, 'bpwpsh_allowed_options' ) );

			// Enqueue custom scripts.
			add_action( 'admin_enqueue_scripts', array( $this, 'bpwpsh_enqueue_scripts' ) );

			// Custom AJAX.
			add_action( 'wp_ajax_bpwpsh_get_pages', array( $this, 'bpwpsh_get_pages_ajax_callback' ) );
		}

		/**
		 * Add setting to set homepage for logged-in users.
		 *
		 * @return void
		 */
		public function bpwpsh_register_homepage_setting() {

			$show_on_front = get_option( 'show_on_front' );
			$page_on_front = get_option( 'page_on_front' );

			// Bail, if anything goes wrong.
			if ( ( ! empty( $show_on_front ) && 'page' !== $show_on_front ) ||
				( empty( $page_on_front ) || '0' === $page_on_front ) ) {

				return;
			}

			// Add setting section to reading page.
			add_settings_section(
				'bpwpsh_user_role_setting_section',
				esc_html__( 'Homepage for Users roles', 'bpwp-set-homepages' ),
				'',
				'reading'
			);

			// Add field to reading page.
			add_settings_field(
				'front-static-pages-logged-in',
				esc_html__( 'Homepage for logged-in Users ( Default )', 'bpwp-set-homepages' ),
				array( $this, 'bpbpwpsh_setting_callback_function' ),
				'reading',
				'bpwpsh_user_role_setting_section',
				array( 'label_for' => 'front-static-pages-logged-in' )
			);

			// Get list of user roles that the current user is allowed to edit.
			$editable_roles = array_reverse( get_editable_roles() );

			if ( ! empty( $editable_roles ) && array_key_exists( 'administrator', $editable_roles ) ) {
				unset( $editable_roles['administrator'] );
			}

			if ( ! empty( $editable_roles ) ) {
				// Get stored values.
				$values = get_option( 'page_on_front_user_role' );

				// Loop through all roles.
				foreach ( $editable_roles as $role => $details ) {

					// Get role name.
					$name = translate_user_role( $details['name'] );

					// Add setting field.
					add_settings_field(
						"page_on_front_user_role[$role]",
						esc_html( $name ),
						array( $this, 'bpwpsh_user_role_setting_cb' ),
						'reading',
						'bpwpsh_user_role_setting_section',
						array(
							'label_for' => "page_on_front_user_role[$role]",
							'value'     => ! empty( $values[ $role ] ) ? (int) $values[ $role ] : 0,
							'role'      => $role,
						)
					);
				}
			}
		}

		/**
		 * Callback to display page selection.
		 *
		 * @return void
		 */
		public function bpbpwpsh_setting_callback_function() {
			?>
			<select
				name="page_on_front_logged_in"
				class="regular-text blpwpsh-selector"
			>
			<?php
			// Get stored value.
			$value   = get_option( 'page_on_front_logged_in' );
			$page_id = ! empty( $value ) ? (int) $value : 0;

			if ( ! empty( $page_id ) ) {
				// Page Title.
				$page_title = ! empty( get_the_title( $page_id ) ) ? esc_html( get_the_title( $page_id ) ) : esc_html__( 'Page Not Found', 'bpwp-set-homepages' );

				printf(
					'<option value="%1$s" selected>%2$s</option>',
					esc_attr( $page_id ),
					esc_html( $page_title )
				);
			}
			?>
			</select>
			<p class="description"><?php esc_html_e( 'Redirect logged-in users to this page when they try to access homepage.', 'bpwp-set-homepages' ); ?></p>
			<?php
		}

		/**
		 * Callback to display page selection.
		 *
		 * @param array $args Extra arguments that get passed to the callback function.
		 * @return void
		 */
		public function bpwpsh_user_role_setting_cb( $args ) {

			$role  = ! empty( $args['role'] ) ? $args['role'] : '';
			$value = ! empty( $args['value'] ) ? $args['value'] : '';
			?>
			<select
				name="page_on_front_user_role[<?php echo esc_attr( $role ); ?>]"
				class="regular-text blpwpsh-selector"
				place
			>
			<?php
			// Get stored value.
			$page_id = ! empty( $value ) ? (int) $value : 0;

			if ( ! empty( $page_id ) ) {
				// Page Title.
				$page_title = ! empty( get_the_title( $page_id ) ) ? esc_html( get_the_title( $page_id ) ) : esc_html__( 'Page Not Found', 'bpwp-set-homepages' );

				printf(
					'<option value="%1$s" selected>%2$s</option>',
					esc_attr( $page_id ),
					esc_html( $page_title )
				);
			}
			?>
			</select>
			<?php
		}

		/**
		 * Add new field to allowed option.
		 * By adding this field to allowed option, WP handles saving data to options.
		 *
		 * @param array $allowed_options The allowed options list.
		 * @return array
		 */
		public function bpwpsh_allowed_options( $allowed_options ) {

			// Add new option to allowed list.
			if ( isset( $allowed_options['reading'] ) ) {
				$allowed_options['reading'][] = 'page_on_front_logged_in';
				$allowed_options['reading'][] = 'page_on_front_user_role';
			}

			return $allowed_options;
		}

		/**
		 * Enqueue custom admin scripts.
		 *
		 * @param string $hook_suffix The current admin page.
		 */
		public function bpwpsh_enqueue_scripts( $hook_suffix ) {

			if ( 'options-reading.php' !== $hook_suffix ) {
				return;
			}

			$plugin_asset = 'blpwpsh-admin';

			if ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) {
				// If Script debug disabled then include minified files.
				$plugin_asset .= '.min';
			}

			// Plugin Related Style and Scripts.
			wp_enqueue_script(
				'blpwpsh-admin',
				trailingslashit( BPWPSH_URL ) . 'app/admin/assets/js/' . $plugin_asset . '.js',
				array( 'jquery', 'blpwp-sh-select2' ),
				BPWPSH_VERSION,
				true
			);

			/**
			 * Credits: Select2( https://select2.org/ )
			 */
			wp_enqueue_style(
				'blpwp-sh-select2',
				trailingslashit( BPWPSH_URL ) . 'assets/css/select2.min.css',
				array(),
				BPWPSH_VERSION
			);
			wp_enqueue_script(
				'blpwp-sh-select2',
				trailingslashit( BPWPSH_URL ) . 'assets/js/select2.min.js',
				array( 'jquery' ),
				BPWPSH_VERSION,
				true
			);
		}

		/**
		 * Get pages ajax callback.
		 */
		public function bpwpsh_get_pages_ajax_callback() {
			$return = array();

			$search_key  = filter_input( INPUT_GET, 'search_key', FILTER_DEFAULT );
			$search_type = filter_input( INPUT_GET, 'search_type', FILTER_DEFAULT );

			switch ( $search_type ) {
				case 'memberpress':
					$allowed_post_types = apply_filters(
						'blpv_allowed_post_types_memberpress',
						array(
							'memberpressproduct',
						)
					);
					break;
				case 'learndash':
					$allowed_post_types = apply_filters(
						'blpv_allowed_post_types_learndash',
						array(
							'sfwd-courses',
						)
					);
					break;
				default:
					$allowed_post_types = apply_filters(
						'blp_br_allowed_post_types',
						array( 'page' )
					);
					break;
			}

			$search_results = new WP_Query(
				array(
					's'                   => $search_key,
					'post_status'         => 'publish',
					'post_type'           => $allowed_post_types,
					'ignore_sticky_posts' => 1,
					'posts_per_page'      => 50,
				)
			);

			if ( $search_results->have_posts() ) :
				while ( $search_results->have_posts() ) :
					$search_results->the_post();
					// Shorten the title a little.
					$title    = ( mb_strlen( get_the_title() ) > 50 )
						? mb_substr( get_the_title(), 0, 49 ) . '...'
						: get_the_title();
					$return[] = array(
						get_the_ID(),
						$title,
					);
				endwhile;
			endif;
			echo wp_json_encode( $return );
			die;
		}
	}
}

new BPWP_Set_Homepages_Admin();
