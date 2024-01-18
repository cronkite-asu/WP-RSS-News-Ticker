<?php
namespace Rssnewsticker;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

abstract class Settings {

	/**
	 * Setting ID. Prefixes all options.
	 * @var string
	 */
	protected $id = '';

	/**
	 * @var string
	 */
	protected $page_title = '';

	/**
	 * @var string
	 */
	protected $menu_title = '';

	/**
	 * @var string
	 */
	protected $parent_menu = '';

	/**
	 * Settings Fields.
	 * @var array
	 */
	protected $fields = [];

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'register_page' ] );
		add_action( 'admin_init', [ $this, 'register_fields' ] );
	}

	public function register_page() {
		if ( $this->parent_menu ) {
			add_submenu_page(
				$this->parent_menu,
				$this->page_title,
				$this->menu_title ?: $this->page_title,
				'manage_options',
				$this->id . '_settings_page',
				[ $this, 'render_page']
			);
		} else {
			add_menu_page(
				$this->page_title,
				$this->menu_title ?: $this->page_title,
				'manage_options',
				$this->id . '_settings_page',
				[ $this, 'render_page']
			);
		}
	}

	public function render_page() {
		?>
		<div class="wrap">
			<div id="icon-options-general" class="icon32"><br></div>
			<h2><?php echo $this->page_title; ?></h2>

			<form action="options.php" method="post">

				<?php

				do_action( $this->id . '_settings_before_options' );

				// Prepare form to handle current page.
				settings_fields( $this->id . '_settings_page' );

				// Render fields and sections.
				do_settings_sections( $this->id . '_settings_page' );

				do_action( 'pmpro_' . $this->id . '_settings_before_submit_button' );

				submit_button( __( 'Save Settings', 'your_textdomain' ) );

				do_action( $this->id . '_settings_after_submit_button' );
				?>

			</form>
			<?php do_action( $this->id . '_settings_after_form' ); ?>
		</div>
		<?php
	}

	public function get_settings_id() {
		return $this->id;
	}

	public function add_field( $field ) {
		$this->fields = array_merge( $this->fields, $field );
	}


	/**
	 * Get Settings Fields.
	 *
	 * @return mixed|null
	 */
	public function get_fields() {
		return apply_filters( $this->id . '_get_fields', $this->fields );
	}

	/**
	 * Register Fields
	 *
	 * @return void
	 */
	public function register_fields() {
		$fields = $this->get_fields();

		register_setting(
			$this->id . '_settings_page',
			$this->id . '_options',
			[
				'sanitize_callback' => [ $this, 'sanitize' ]
			]
		);

		foreach ( $fields as $field ) {
			if ( 'section' === $field['type'] ) {
				add_settings_section(
					$this->id . '_' . $field['name'] . '_section',
					$field['title'],
					[ $this, 'render_section' ],
					$this->id . '_settings_page',
					$field
				);
			} else {
				add_settings_field(
					$this->id . '_' . $field['name'],
					$field['title'],
					[ $this, 'render_field' ],
					$this->id . '_settings_page',
					$this->id . '_' . $field['section'] . '_section',
					$field
				);
			}
		}
	}
	public function sanitize( $input ) {
		$fields = $this->get_fields();

		foreach ( $fields as $field ) {
			// No need to sanitize sections.
			if ( 'section' === $field['type'] ) {
				continue;
			}

			if ( ! isset( $input[ $field['name'] ] ) ) {
				continue;
			}

			switch ( $field['type'] ) {
				case 'text':
					$input[ $field['name'] ] = sanitize_text_field( $input[ $field['name'] ] );
					break;
			}
		}

		do_action( $this->id . '_settings_sanitized', $input, $fields, $_POST, $this );

		return $input;
	}


	/**
	 * Get option key. Useful for name attributes in forms.
	 *
	 * @param string $key Field Name.
	 * @return string
	 */
	public function get_option_key( $key ) {
		return $this->id . '_options[' . $key . ']';
	}

	/**
	 * Get Option
	 *
	 * @param string $id Field Name
	 * @param mixed	 $default Default value if we don't have anything saved.
	 * @return mixed|string
	 */
	public function get_option( $id, $default = '' ) {
		$options = get_option( $this->id . '_options' );

		return isset( $options[ $id ] ) ? $options[ $id ] : $default;
	}

	public function render_section( $args ) {
		if ( ! empty( $args['description'] ) ) {
			?>
			<p class="description"><?php echo wp_kses_post( $args['description'] ); ?></p>
			<?php
		}
	}

	/**
	 * Render Field
	 * @param array $args Field Arguments.
	 * @return void
	 */
	public function render_field( $args ) {
		$this->{'render_' . $args['type'] }( $args );
	}

	/**
	 * Render Text input
	 *
	 * @param $args
	 * @return void
	 */
	public function render_text( $args ) {
		$default = ! empty( $args['default'] ) ? $args['default'] : '';
		?>
		<input type="text" class="widefat" name="<?php echo esc_attr( $this->get_option_key( $args['name'] ) ); ?>" value="<?php echo esc_attr( $this->get_option( $args['name'], $default ) ); ?>" />
		<?php
		if ( ! empty( $args['description'] ) ) {
			?>
			<p class="description"><?php echo esc_html( $args['description'] ); ?></p>
			<?php
		}
	}

}
