<?php

abstract class Rssnewsticker_Admin_Settings {

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
	protected $capability = 'manage_options';

	/**
	 * @var string
	 */
	protected $slug = '_settings_page';

	/**
	 * @var string
	 */
	protected $icon_url = '';

	/**
	 * @var int
	 */
	protected $position = null;

	/**
	 * Submit button args.
	 * @var array
	 */
	protected $submit_args = [null, 'primary', 'submit', true, null];

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

	public function __construct( $plugin_name, $version ) {
		add_action( 'admin_menu', [ $this, 'register_page' ] );
		add_action( 'admin_init', [ $this, 'register_fields' ] );
	}

	public function register_page() {
		if ( $this->parent_menu ) {
			$settings_page = add_submenu_page(
				$this->parent_menu,			// $parent_slug
				$this->page_title,			// $page_title
				$this->menu_title ?: $this->page_title,	// $menu_title
				$this->capability,			// $capability
				$this->id . $this->slug,		// $menu_slug
				[ $this, 'render_page'],		// $callback
				$this->position				// $position
			);
		} else {
			$settings_page = add_menu_page(
				$this->page_title,			// $page_title
				$this->menu_title ?: $this->page_title,	// $menu_title
				$this->capability,			// $capability
				$this->id . $this->slug,		// $menu_slug
				[ $this, 'render_page'],		// $callback
				$this->icon_url,			// $icon_url
				$this->position				// $position
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

				do_action( $this->id . '_settings_before_submit_button' );

				submit_button(...$this->submit_args);

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
	 * @since 1.0.0
	 */
	public function get_hook_suffix(){
		return $this->hook_suffix;
	}

	public function add_field( $field ) {
		$this->fields = array_merge( $this->fields, $field );
	}

	/**
	 * Load Script Needed For Meta Box
	 * @since 1.0.0
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
			$this->id . '_settings_page',				// $option_group
			$this->get_option_name(),					// $option_name
			[ 'sanitize_callback' => [ $this, 'sanitize' ] ]	// $args
		);

		foreach ( $fields as $field ) {
			if ( 'section' === $field['type'] ) {
				add_settings_section(
					$this->id . '_' . $field['name'] . '_section',	// $id
					$field['title'],				// $title
					[ $this, 'render_section' ],			// $callback
					$this->id . '_settings_page',			// $page
					$field						// $args
				);
			} else {
				add_settings_field(
					$this->id . '_' . $field['name'],			// $id
					$field['title'],					// $title
					[ $this, 'render_field' ],				// $callback
					$this->id . '_settings_page',				// $page
					$this->id . '_' . $field['section'] . '_section',	// $section
					$field							// $args
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
					$input[ $field['name'] ] =  $this->sanitize_textarea_field( $input[ $field['name'] ] );
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
	 * Sanitizes the textarea field.
	 */
	protected function sanitize_textarea_field( $value = '' ) {
		return sanitize_textarea_field( $value );
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

		// Santize entries as text
		$values = map_deep( $values, 'sanitize_text_field' );

		// Remove empty entries from array
		$values = array_filter($values);

		return $values;
	}

	/**
	 * Get option name. Useful for referencing the option.
	 *
	 * @return string
	 */
	public function get_option_name( ) {
		return $this->id . '_options';
	}

	/**
	 * Get option key. Useful for name attributes in forms.
	 *
	 * @param string $key Field Name.
	 * @return string
	 */
	public function get_option_key( $key ) {
		return $this->get_option_name() . '[' . $key . ']';
	}

	/**
	 * Get Option
	 *
	 * @param string $id Field Name
	 * @param mixed	 $default Default value if we don't have anything saved.
	 * @return mixed|string
	 */
	public function get_option( $id, $default = '' ) {
		$options = get_option( $this->get_option_name() );

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
		$name = $args['name'];
		$class = ! empty( $args['class'] ) ? $args['class'] : '';
		$default = ! empty( $args['default'] ) ? $args['default'] : '';
		$autocomplete = $args['autocomplete'] ?? '';
		$maxlength = $args['maxlength'] ?? '';
		$minlength = $args['minlength'] ?? '';
		$pattern = $args['pattern'] ?? '';
		$placeholder = $args['placeholder'] ?? '';
		$required = $args['required'] ?? false;
		$tooltip = $args['tooltip'] ?? '';
		?>
		<input
			type="text" id="<?php echo esc_attr( $name ); ?>-input"
			class="<?php echo esc_attr( $class ); ?>"
			name="<?php echo esc_attr( $this->get_option_key( $name ) ); ?>"
			value="<?php echo esc_attr( $this->get_option( $name, $default ) ); ?>"
<?php if ( ! empty( $autocomplete ) ) { ?>
			autocomplete="<?php echo esc_attr( $autocomplete ); ?>"
<?php } ?>
<?php if ( ! empty( $maxlength ) ) { ?>
			maxlength="<?php echo esc_attr( $maxlength ); ?>"
<?php } ?>
<?php if ( ! empty( $minlength ) ) { ?>
			minlength="<?php echo esc_attr( $minlength ); ?>"
<?php } ?>
<?php if ( ! empty( $pattern ) ) { ?>
			pattern="<?php echo esc_attr( $pattern ); ?>"
<?php } ?>
<?php if ( ! empty( $placeholder ) ) { ?>
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
<?php } ?>
<?php if ( $required ) { ?>
			required
<?php } ?>
<?php if ( ! empty( $tooltip ) ) { ?>
			title="<?php echo esc_attr( $tooltip ); ?>"
<?php } ?>
		/>
		<?php
		if ( ! empty( $args['description'] ) ) {
			?>
			<p id="<?php echo esc_attr( $name ); ?>-description" class="description" name="<?php echo esc_attr( $this->get_option_key( $name ) ); ?>"><?php echo wp_kses_post( $args['description'] ); ?></p>
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
		$name = $args['name'];
		$class = ! empty( $args['class'] ) ? $args['class'] : '';
		$default = ! empty( $args['default'] ) ? $args['default'] : '';
		$autocomplete = $args['autocomplete'] ?? '';
		$rows = $args['rows'] ?? '25';
		$cols = $args['cols'] ?? '80';
		$required = $args['required'] ?? false;
		$tooltip = $args['tooltip'] ?? '';
		?>
		<textarea
			type="text"
			name="<?php echo esc_attr( $this->get_option_key( $name ) ); ?>"
			id="<?php echo esc_attr( $name ); ?>-textarea"
			rows="<?php echo esc_attr( $rows ); ?>"
			cols="<?php echo esc_attr( $cols ); ?>"
			class="<?php echo esc_attr( $class ); ?>"
<?php if ( ! empty( $autocomplete ) ) { ?>
			autocomplete="<?php echo esc_attr( $autocomplete ); ?>"
<?php } ?>
<?php if ( $required ) { ?>
			required
<?php } ?>
<?php if ( ! empty( $tooltip ) ) { ?>
			title="<?php echo esc_attr( $tooltip ); ?>"
<?php } ?>
			><?php
				echo esc_attr( $this->get_option( $name, $default ) );
			?></textarea>

		<?php
		if ( ! empty( $args['description'] ) ) {
			?>
			<p id="<?php echo esc_attr( $name ); ?>-description" class="description" name="<?php echo esc_attr( $this->get_option_key( $name ) ); ?>"><?php echo wp_kses_post( $args['description'] ); ?></p>
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
		$name = $args['name'];
		$class = ! empty( $args['class'] ) ? $args['class'] : '';
		$default = ! empty( $args['default'] ) ? $args['default'] : '';
		$autocomplete = $args['autocomplete'] ?? '';
		$min = ! empty( $args['min'] ) ? $args['min'] : '';
		$max = ! empty( $args['max'] ) ? $args['max'] : '';
		$placeholder = $args['placeholder'] ?? '';
		$required = $args['required'] ?? false;
		$tooltip = $args['tooltip'] ?? '';
		?>
		<input
			type="number"
			id="<?php echo esc_attr( $name ); ?>-input"
			class="<?php echo esc_attr( $class ); ?>"
			name="<?php echo esc_attr( $this->get_option_key( $name ) ); ?>"
			value="<?php echo esc_attr( $this->get_option( $name, $default ) ); ?>"
			min="<?php echo esc_attr( $min ); ?>"
			max="<?php echo esc_attr( $max ); ?>"
<?php if ( ! empty( $autocomplete ) ) { ?>
			autocomplete="<?php echo esc_attr( $autocomplete ); ?>"
<?php } ?>
<?php if ( ! empty( $placeholder ) ) { ?>
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
<?php } ?>
<?php if ( $required ) { ?>
			required
<?php } ?>
<?php if ( ! empty( $tooltip ) ) { ?>
			title="<?php echo esc_attr( $tooltip ); ?>"
<?php } ?>
		/>
		<?php
		if ( ! empty( $args['description'] ) ) {
			?>
			<p id="<?php echo esc_attr( $name ); ?>-description" class="description" name="<?php echo esc_attr( $this->get_option_key( $name ) ); ?>"><?php echo wp_kses_post( $args['description'] ); ?></p>
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
		$name = $args['name'];
		$class = ! empty( $args['class'] ) ? $args['class'] : '';
		$default = ! empty( $args['default'] ) ? $args['default'] : '';
		$required = $args['required'] ?? false;
		$tooltip = $args['tooltip'] ?? '';
		?>
		<input
			type="checkbox" id="<?php echo esc_attr( $name ); ?>-input"
			class="<?php echo esc_attr( $class ); ?>"
			name="<?php echo esc_attr( $this->get_option_key( $name ) ); ?>"
<?php if ( $required ) { ?>
			required
<?php } ?>
<?php if ( ! empty( $tooltip ) ) { ?>
			title="<?php echo esc_attr( $tooltip ); ?>"
<?php } ?>
			<?php checked( $this->get_option( $name, $default ), 1, true ); ?>
		/>
		<?php
		if ( ! empty( $args['description'] ) ) {
			?>
			<p id="<?php echo esc_attr( $name ); ?>-description" class="description" name="<?php echo esc_attr( $this->get_option_key( $name ) ); ?>"><?php echo wp_kses_post( $args['description'] ); ?></p>
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
		$name = $args['name'];
		$class = ! empty( $args['class'] ) ? $args['class'] : '';
		$default = ! empty( $args['default'] ) ? $args['default'] : '';
		$autocomplete = $args['autocomplete'] ?? '';
		$choices = ! empty( $args['choices'] ) ? $args['choices'] : [];
		$size = ! empty( $args['size'] ) ? $args['size'] : '';
		$choices = ! empty( $args['choices'] ) ? $args['choices'] : [];
		$required = $args['required'] ?? false;
		$tooltip = $args['tooltip'] ?? '';
		?>
		<select
			id="<?php echo esc_attr( $name ); ?>-select"
			class="<?php echo esc_attr( $class ); ?>"
			name="<?php echo esc_attr( $this->get_option_key( $name ) ); ?>"
			value="<?php echo esc_attr( $this->get_option( $name, $default ) ); ?>"
<?php if ( ! empty( $autocomplete ) ) { ?>
			autocomplete="<?php echo esc_attr( $autocomplete ); ?>"
<?php } ?>
<?php if ( ! empty( $args['size'] ) ) { ?>
			size="<?php echo esc_attr( $size ); ?>"
<?php } ?>
<?php if ( $args['multiple'] ) { ?>
			multiple
<?php } ?>
<?php if ( $required ) { ?>
			required
<?php } ?>
<?php if ( ! empty( $tooltip ) ) { ?>
			title="<?php echo esc_attr( $tooltip ); ?>"
<?php } ?>
		/>
		<?php foreach ( $choices as $choice_v => $label ) { ?>
			<option value="<?php echo esc_attr( $choice_v ); ?>" <?php selected( $choice_v, $value, true ); ?>><?php echo esc_html( $label ); ?></option>
		<?php } ?>
		</select>
		<?php
		if ( ! empty( $args['description'] ) ) {
			?>
			<p id="<?php echo esc_attr( $name ); ?>-description" class="description" name="<?php echo esc_attr( $this->get_option_key( $name ) ); ?>"><?php echo wp_kses_post( $args['description'] ); ?></p>
			<?php
		}
	}

}
