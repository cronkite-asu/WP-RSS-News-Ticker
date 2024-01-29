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
	 * @var string
	 */
	protected $icon_url = '';

	/**
	 * @var int
	 */
	protected $position = null;

	/**
	 * Settings Fields.
	 * @var array
	 */
	protected $fields = [];

	/**
	 * Setting Hook Suffix.
	 * @var string
	 */
	protected $hook_suffix = '';

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'register_page' ] );
		add_action( 'admin_init', [ $this, 'register_fields' ] );
	}

	public function register_page() {
		if ( $this->parent_menu ) {
			$settings_page = add_submenu_page(
				$this->parent_menu,
				$this->page_title,
				$this->menu_title ?: $this->page_title,
				'manage_options',
				$this->id . '_settings_page',
				[ $this, 'render_page']
			);
		} else {
			$settings_page = add_menu_page(
				$this->page_title,
				$this->menu_title ?: $this->page_title,
				'manage_options',
				$this->id . '_settings_page',
				[ $this, 'render_page'],
				$this->icon_url,
				$this->position
			);
		}

		/* Do stuff in settings page, such as adding scripts, etc. */
		if ( !empty( $settings_page ) ) {
			$this->hook_suffix = $settings_page;

			/* Load the JavaScript needed for the settings screen. */
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
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

	/**
	 * Utility: Page Hook
	 * The Settings Page Hook, it's the same with global $hook_suffix.
	 * @since 0.1.0
	 */
	public function get_hook_suffix(){
		return $this->hook_suffix;
	}

	public function add_field( $field ) {
		$this->fields = array_merge( $this->fields, $field );
	}

	/**
	 * Load Script Needed For Meta Box
	 * @since 0.1.0
	 */
	public function enqueue_scripts( $hook_suffix ) {
		return;
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
				case 'textarea':
					$input[ $field['name'] ] = sanitize_textarea_field( $input[ $field['name'] ] );
					break;
				case 'select':
					$input[ $field['name'] ] = $this->sanitize_select_field( $input[ $field['name'] ] );
					break;
				case 'checkbox':
					$input[ $field['name'] ] = $this->sanitize_checkbox_field( $input[ $field['name'] ] );
					break;
				case 'array':
					$input[ $field['name'] ] = $this->sanitize_array_field( $input[ $field['name'] ] );
					break;
			}
		}

		do_action( $this->id . '_settings_sanitized', $input, $fields, $_POST, $this );

		return $input;
	}

	/**
	 * Sanitizes the checkbox field.
	 */
	protected function sanitize_checkbox_field( $value = '', $field_args = [] ) {
		return ( 'on' === $value ) ? 1 : 0;
	}

	/**
	 * Sanitizes the select field.
	 */
	protected function sanitize_select_field( $value = '', $field_args = [] ) {
		$choices = $field_args['choices'] ?? [];
		if ( array_key_exists( $value, $choices ) ) {
			return $value;
		}
	}

	/**
	 * Sanitizes the array field.
	 */
	protected function sanitize_array_field( $values = [], $field_args = [] ) {

		// Remove empty entries from array
		$values = array_filter($values);

		// Santize entries as text
		$values = map_deep( $values, 'sanitize_text_field' );
		return $values;
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
		$class = ! empty( $args['class'] ) ? $args['class'] : '';
		$default = ! empty( $args['default'] ) ? $args['default'] : '';
		?>
		<input type="text" id="<?php echo esc_attr( $args['name'] ); ?>-input" class="<?php echo esc_attr( $class ); ?>" name="<?php echo esc_attr( $this->get_option_key( $args['name'] ) ); ?>" value="<?php echo esc_attr( $this->get_option( $args['name'], $default ) ); ?>" />
		<?php
		if ( ! empty( $args['description'] ) ) {
			?>
			<p id="<?php echo esc_attr( $args['name'] ); ?>-description" class="description" name="<?php echo esc_attr( $this->get_option_key( $args['name'] ) ); ?>"><?php echo esc_html( $args['description'] ); ?></p>
			<?php
		}
	}

	/**
	 * Render Textarea input
	 *
	 * @param $args
	 * @return void
	 */
	public function render_textarea( $args ) {
		$class = ! empty( $args['class'] ) ? $args['class'] : '';
		$default = ! empty( $args['default'] ) ? $args['default'] : '';
		$rows = $args['rows'] ?? '4';
		$cols = $args['cols'] ?? '50';
		?>
		<textarea
			type="text"
			name="<?php echo esc_attr( $this->get_option_key( $args['name'] ) ); ?>"
			id="<?php echo esc_attr( $args['name'] ); ?>-textarea"
			rows="<?php echo esc_attr( $rows ); ?>"
			cols="<?php echo esc_attr( $cols ); ?>"
			class="<?php echo esc_attr( $class ); ?>"><?php echo esc_attr( $this->get_option( $args['name'], $default ) ); ?></textarea>

		<?php
		if ( ! empty( $args['description'] ) ) {
			?>
			<p id="<?php echo esc_attr( $args['name'] ); ?>-description" class="description" name="<?php echo esc_attr( $this->get_option_key( $args['name'] ) ); ?>"><?php echo esc_html( $args['description'] ); ?></p>
			<?php
		}
	}

	/**
	 * Render Number input
	 *
	 * @param $args
	 * @return void
	 */
	public function render_number( $args ) {
		$class = ! empty( $args['class'] ) ? $args['class'] : '';
		$default = ! empty( $args['default'] ) ? $args['default'] : '';
		$min = ! empty( $args['min'] ) ? $args['min'] : '';
		$max = ! empty( $args['max'] ) ? $args['max'] : '';
		?>
		<input type="number" id="<?php echo esc_attr( $args['name'] ); ?>-input" class="<?php echo esc_attr( $class ); ?>" name="<?php echo esc_attr( $this->get_option_key( $args['name'] ) ); ?>" value="<?php echo esc_attr( $this->get_option( $args['name'], $default ) ); ?>" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>" />
		<?php
		if ( ! empty( $args['description'] ) ) {
			?>
			<p id="<?php echo esc_attr( $args['name'] ); ?>-description" class="description" name="<?php echo esc_attr( $this->get_option_key( $args['name'] ) ); ?>"><?php echo esc_html( $args['description'] ); ?></p>
			<?php
		}
	}

	/**
	 * Render checkbox input
	 *
	 * @param $args
	 * @return void
	 */
	public function render_checkbox( $args ) {
		$class = ! empty( $args['class'] ) ? $args['class'] : '';
		$default = ! empty( $args['default'] ) ? $args['default'] : '';
		?>
		<input type="checkbox" id="<?php echo esc_attr( $args['name'] ); ?>-input" class="<?php echo esc_attr( $class ); ?>" name="<?php echo esc_attr( $this->get_option_key( $args['name'] ) ); ?>" <?php checked( $this->get_option( $args['name'], $default ), 1, true ); ?> />
		<?php
		if ( ! empty( $args['description'] ) ) {
			?>
			<p id="<?php echo esc_attr( $args['name'] ); ?>-description" class="description" name="<?php echo esc_attr( $this->get_option_key( $args['name'] ) ); ?>"><?php echo esc_html( $args['description'] ); ?></p>
			<?php
		}
	}

	/**
	 * Render select input
	 *
	 * @param $args
	 * @return void
	 */
	public function render_select( $args ) {
		$class = ! empty( $args['class'] ) ? $args['class'] : '';
		$default = ! empty( $args['default'] ) ? $args['default'] : '';
		$choices = ! empty( $args['choices'] ) ? $args['choices'] : [];
		?>
		<select id="<?php echo esc_attr( $args['name'] ); ?>-select" class="<?php echo esc_attr( $class ); ?>" name="<?php echo esc_attr( $this->get_option_key( $args['name'] ) ); ?>" value="<?php echo esc_attr( $this->get_option( $args['name'], $default ) ); ?>" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>" />
		<?php foreach ( $choices as $choice_v => $label ) { ?>
			<option value="<?php echo esc_attr( $choice_v ); ?>" <?php selected( $choice_v, $value, true ); ?>><?php echo esc_html( $label ); ?></option>
		<?php } ?>
		</select>
		<?php
		if ( ! empty( $args['description'] ) ) {
			?>
			<p id="<?php echo esc_attr( $args['name'] ); ?>-description" class="description" name="<?php echo esc_attr( $this->get_option_key( $args['name'] ) ); ?>"><?php echo esc_html( $args['description'] ); ?></p>
			<?php
		}
	}

}
