<?php
namespace Rssnewsticker;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class SettingsMetaBox extends Settings {


	public function __construct() {
		/* Pre Settings */
		add_action( $this->id . '_settings_before_options', [ $this, 'settings_before_options' ] );

		/* Add Meta Box */
		add_action( 'add_meta_boxes', [ $this, 'repeater_add_meta_box' ] );

		parent::__construct();
	}

	/**
	 * Load Script Needed For Meta Box
	 * @since 0.1.0
	 */
	public function enqueue_scripts( $hook_suffix ){
		$page_hook_id = $this->get_hook_suffix();
		if ( $hook_suffix == $page_hook_id ){
			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'wp-lists' );
			wp_enqueue_script( 'wp-util' );
			wp_enqueue_script( 'postbox' );
		}
		add_action( "admin_footer-{$page_hook_id}", [ $this, 'footer_scripts' ] );
		/* Set number of column available. */
		add_filter( 'screen_layout_columns', [ $this, 'screen_layout_column' ], 10, 2 );
	}

	/**
	 * Footer Script Needed for Meta Box:
	 * - Meta Box Toggle.
	 * - Spinner for Saving Option.
	 * - Reset Settings Confirmation
	 * @since 0.1.0
	 */
	public function footer_scripts(){
		$page_hook_id = $this->get_hook_suffix();
		$name = 'ticker_text';
	?>
	<script type="text/html" id="tmpl-repeater">
		<div class="field-group">
			<input type="text" name="<?php echo esc_attr( $this->get_option_key( $name ) ); ?>[]" value="" />
			<button type="button" class="button button-secondary field-data-remove">X</button>
		</div>
	</script>
	<script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready( function($) {
			'use strict';
			// toggle
			$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
			postboxes.add_postbox_toggles( '<?php echo $page_hook_id; ?>' );
			// display spinner
			$('<?php echo esc_attr( '#' . $this->id . '-form' ); ?>').submit( function(){
				$('#publishing-action .spinner').addClass('is-active');
			});
			// confirm before reset
			$('#delete-action .submitdelete').on('click', function() {
				return confirm('Are you sure want to do this?');
			});
			$(function(){
				$("#field_data_add").on( 'click', function(e){
					e.preventDefault();
					var template = wp.template('repeater'),
						html = template();
					$("#field_data").append( html );
				});
				$( document ).on( 'click', '.field-data-remove', function(e){
					e.preventDefault();
					$(this).parent().remove();
				});
			});
		});
		//]]>
	</script>
	<?php
	}

	/**
	 * Number of Column available in Settings Page.
	 * we can only set to 1 or 2 column.
	 * @since 0.1.0
	 */
	public function screen_layout_column( $columns, $screen ){
		$page_hook_id = $this->get_hook_suffix();
		if ( $screen == $page_hook_id ){
			$columns[$page_hook_id] = 2;
		}
		return $columns;
	}

	/**
	 * Settings Page Callback
	 * used in register_fields().
	 * @since 0.1.0
	 */
	public function settings_before_options(){

		/* global vars */
		global $hook_suffix;

		/* utility hook */
		do_action( 'settings_page_init' );

		/* enable add_meta_boxes function in this page. */
		do_action( 'add_meta_boxes', $hook_suffix );
		?>

		<?php settings_errors(); ?>

		<div class="<?php echo esc_attr( $this->id ); ?>">

				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>

				<div id="poststuff">

					<div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">

						<div id="postbox-container-1" class="postbox-container">

							<?php do_meta_boxes( $hook_suffix, 'side', null ); ?>
							<!-- #side-sortables -->

						</div><!-- #postbox-container-1 -->

						<div id="postbox-container-2" class="postbox-container">

							<?php do_meta_boxes( $hook_suffix, 'normal', null ); ?>
							<!-- #normal-sortables -->

							<?php do_meta_boxes( $hook_suffix, 'advanced', null ); ?>
							<!-- #advanced-sortables -->

						</div><!-- #postbox-container-2 -->

					</div><!-- #post-body -->

					<br class="clear">

				</div><!-- #poststuff -->

		</div><!-- .<?php echo esc_attr( $this->id ); ?> -->
		<?php
	}

	/* === SUBMIT / SAVE META BOX === */


	/**
	 * Add Submit/Save Meta Box
	 * @since 0.1.0
	 * @uses submit_meta_box()
	 * @link http://codex.wordpress.org/Function_Reference/add_meta_box
	 */
	public function submit_add_meta_box(){

		$page_hook_id = $this->get_hook_suffix();

		add_meta_box(
			'submitdiv',			/* Meta Box ID */
			'Save Options',			/* Title */
			[ $this, 'submit_meta_box' ],	/* Function Callback */
			$page_hook_id,			/* Screen: Our Settings Page */
			'side',				/* Context */
			'high'				/* Priority */
		);
	}

	/**
	 * Submit Meta Box Callback
	 * @since 0.1.0
	 */
	public function submit_meta_box(){

		/* Reset URL */
		$reset_url = add_query_arg( array(
				'page' => $this->id . '_settings_page',
				'action' => 'reset_settings',
				'_wpnonce' => wp_create_nonce( $this->id . '-reset', __FILE__ ),
			),
			admin_url( 'admin.php' )
		);

	?>
	<div id="submitpost" class="submitbox">

		<div id="major-publishing-actions">

			<div id="delete-action">
				<a href="<?php echo esc_url( $reset_url ); ?>" class="submitdelete deletion">Reset Settings</a>
			</div><!-- #delete-action -->

			<div id="publishing-action">
				<span class="spinner"></span>
				<?php submit_button( esc_attr( 'Save' ), 'primary', 'submit', false );?>
			</div>

			<div class="clear"></div>

		</div><!-- #major-publishing-actions -->

	</div><!-- #submitpost -->

	<?php
	}


	/**
	 * Delete Options
	 * @since 0.1.0
	 */
	public function reset_settings(){

		/* Check Action */
		$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
		if( 'reset_settings' == $action ){

			/* Check User Capability */
			if( current_user_can( 'manage_options' ) ){

				/* nonce */
				$nonce = isset( $_REQUEST['_wpnonce'] ) ? $_REQUEST['_wpnonce'] : '';

				/* valid */
				if( wp_verify_nonce( $nonce, $this->id . '-reset' ) ){

					/**
					 * Get all registered Option Names in current Option Group
					 * ( thanks to @justintadlock )
					 * @since 0.1.1
					 * @link http://themehybrid.com/board/topics/how-to-get-all-option-name-in-option-group
					 */
					global $new_whitelist_options;
					$option_names = $new_whitelist_options[$this->id . '_settings_page'];

					/* Delete All Registered Option Names in the Group */
					foreach( $option_names as $option_name ){
						delete_option( $option_name );
					}

					/* Utility hook. */
					do_action( 'reset' );

					/* Add Update Notice */
					add_settings_error( $this->id . '_settings_page', "", "Settings reset to defaults.", 'updated' );
				}
				/* not valid */
				else{
					/* Add Error Notice */
					add_settings_error( $this->id . '_settings_page', "", "Failed to reset settings. Please try again.", 'error' );
				}
			}
			/* User Do Not Have Capability */
			else{
				/* Add Error Notice */
				add_settings_error( $this->id . '_settings_page', "", "Failed to reset settings. You do not capability to do this action.", 'error' );
			}
		}
	}

	/* === EXAMPLE BASIC META BOX === */


	/**
	 * Basic Meta Box
	 * @since 0.1.0
	 * @link http://codex.wordpress.org/Function_Reference/add_meta_box
	 */
	public function basic_add_meta_box(){

		$page_hook_id = $this->get_hook_suffix();

		add_meta_box(
			$this->id . '_option',		/* Meta Box ID */
			'Meta Box',			/* Title */
			[ $this, 'basic_meta_box' ],	/* Function Callback */
			$page_hook_id,			/* Screen: Our Settings Page */
			'normal',			/* Context */
			'default'			/* Priority */
		);
	}

	/**
	 * Submit Meta Box Callback
	 * @since 0.1.0
	 */
	public function basic_meta_box(){
	?>
	<?php /* Simple Text Input Example */ ?>
	<p>
		<label for="basic-text">Basic Text Input</label>
		<input id="basic-text" class="widefat" type="text" name=<?php echo esc_attr( $this->id . '_option' ); ?> value="<?php echo sanitize_text_field( get_option( $this->id . '_option', '' ) );?>">
	</p>
	<p class="howto">To display this option use PHP code <code>get_option( <?php echo esc_attr( $this->id . '_option' ); ?> );</code>.</p>
	<?php
	}

	/**
	 * Sanitize Basic Settings
	 * This function is defined in register_setting().
	 * @since 0.1.0
	 */
	public function basic_sanitize( $settings  ){
		$settings = sanitize_text_field( $settings );
		return $settings ;
	}

	/* === EXAMPLE REPEATER META BOX === */


	/**
	 * Repeater Meta Box
	 * @since 0.1.0
	 * @link http://codex.wordpress.org/Function_Reference/add_meta_box
	 */
	public function repeater_add_meta_box(){

		$page_hook_id = $this->get_hook_suffix();

		add_meta_box(
			$this->id . '_option',		/* Meta Box ID */
			'Repeater Meta Box',		/* Title */
			[ $this, 'repeater_meta_box' ],	/* Function Callback */
			$page_hook_id,			/* Screen: Our Settings Page */
			'normal',			/* Context */
			'default'			/* Priority */
		);
	}

	/**
	 * Submit Repeater Meta Box Callback
	 * @since 0.1.0
	 */
	public function repeater_meta_box(){

		$name = 'ticker_text';
		$class = ! empty( $args['class'] ) ? $args['class'] : '';
		$default = ! empty( $args['default'] ) ? $args['default'] : [""];
		$field_data = $this->get_option( $name, [""] );

		/* Repeater Text Input */
		?>

		<label for="field_data">
			<strong><?php _e( 'Field Name', 'yourtextdomain' ); ?></strong>
		</label>
		<div id="field_data">
			<?php foreach( $field_data as $i => $value ) { ?>
			<div class="field-group">
				<input type="text" id="<?php echo esc_attr( $name ); ?>-<?php echo $i; ?>-input" class="<?php echo esc_attr( $class ); ?>" name="<?php echo esc_attr( $this->get_option_key( $name ) ); ?>[<?php echo $i; ?>]" value="<?php echo $value; ?>" />
				<?php if ( $i != 0 ) { ?><button type="button" class="button button-secondary field-data-remove">X</button><?php } ?>
			</div>
			<?php } ?>
		</div>
		<button type="button" id="field_data_add" class="button button-primary">Add</button>

	<?php
	}

	/**
	 * Render Repeater input
	 *
	 * @param $args
	 * @return void
	 */
	public function render_array( $args ) {
		return;
	}
}
