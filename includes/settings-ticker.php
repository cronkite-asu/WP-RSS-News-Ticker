<?php
namespace Rssnewsticker;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class SettingsTicker extends Settings {

	public function __construct() {
		$this->id = 'cronkiteticker';
		$this->page_title = 'Cronkite Ticker';
		$this->menu_title = 'Cronkite Ticker';
		$this->icon_url = 'dashicons-rss';
		$this->position = 30;

		$this->define_fields();

		parent::__construct();
	}

	protected function define_fields() {
		$this->fields['ticker_config_section'] = [
			'name' => 'ticker_config_section',
			'title' => 'Ticker Configuration',
			'description' => 'Settings for the RSS Ticker feed.',
			'type' => 'section'
		];

		$this->fields['ticker_text'] = [
			'name' => 'ticker_text',
			'title' => 'Ticker Text',
			'description' => 'Custom text to display on the news ticker.',
			'type' => 'array',
			'section' => 'ticker_config_section',
			'class' => 'widefat',
			'default' => [ 'Welcome to the Walter Cronkite School of Journalism and Mass Communication' ],
		];
	}

	/**
	 * Load Script Needed For Repeater input
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
	}

	/**
	 * Footer Script Needed for Repeater input:
	 * - Meta Box Toggle.
	 * - Spinner for Saving Option.
	 * - Reset Settings Confirmation
	 * @since 0.1.0
	 */
	public function footer_scripts(){
		$page_hook_id = $this->get_hook_suffix();
		$name = 'ticker_text';
	?>

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
	 * Render Repeater input
	 *
	 * @param $args
	 * @return void
	 * @since 0.1.0
	 */
	public function render_array( $args ) {

		$class = ! empty( $args['class'] ) ? $args['class'] : '';
		$default = ! empty( $args['default'] ) ? $args['default'] : [""];
		$field_data = $this->get_option( $args['name'], $default );

		error_log($this->get_option_key( $args['name'] ));

		/* Repeater Text Input */
		?>

		<label for="field_data">
			<strong><?php echo esc_attr( $args['title'] ); ?></strong>
		</label>
		<div id="field_data">
			<?php foreach( $field_data as $i => $value ) { ?>
			<div class="field-group">
				<input type="text" id="<?php echo esc_attr( $args['name'] ); ?>-<?php echo $i; ?>-input" class="<?php echo esc_attr( $class ); ?>" name="<?php echo esc_attr( $this->get_option_key( $args['name'] ) ); ?>[<?php echo $i; ?>]" value="<?php echo $value; ?>" />
				<?php if ( $i != 0 ) { ?><button type="button" class="button button-secondary field-data-remove">X</button><?php } ?>
			</div>
			<?php } ?>
		</div>
		<button type="button" id="field_data_add" class="button button-primary">Add</button>

		<script type="text/html" id="tmpl-repeater">
			<div class="field-group">
				<input type="text" class="<?php echo esc_attr( $class ); ?>" name="<?php echo esc_attr( $this->get_option_key( $args['name'] ) ); ?>[]" value="" />
				<button type="button" class="button button-secondary field-data-remove">X</button>
			</div>
		</script>
	<?php
	}

}
