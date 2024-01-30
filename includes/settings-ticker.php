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
		$this->submit_args = [null, 'large', 'submit', false, null];

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
	 * @since 1.0.0
	 */
	public function enqueue_scripts( $hook_suffix ){
		$page_hook_id = $this->get_hook_suffix();
		if ( $hook_suffix == $page_hook_id ){
			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'wp-lists' );
			wp_enqueue_script( 'wp-util' );
			wp_enqueue_script( 'postbox' );
		}
		add_action( "admin_head-{$page_hook_id}", [ $this, 'head_scripts' ] );
	}

	/**
	 * Render Repeater input
	 *
	 * @param $args
	 * @return void
	 * @since 1.0.0
	 */
	public function render_array( $args ) {

		$page_hook_id = $this->get_hook_suffix();

		$class = ! empty( $args['class'] ) ? $args['class'] : '';
		$default = ! empty( $args['default'] ) ? $args['default'] : [""];
		$field_data = $this->get_option( $args['name'], $default );

		/* Repeater Text Input */
		if ( ! empty( $args['description'] ) ) {
		?>

		<label for="<?php echo esc_attr( $args['name'] ); ?>-field_data">
			<strong><?php echo esc_attr( $args['description'] ); ?></strong>
		</label>
		<?php } ?>

		<div id="<?php echo esc_attr( $args['name'] ); ?>-field_data">
			<?php foreach( $field_data as $i => $value ) { ?>
			<div class="field-group">
				<input type="text" class="<?php echo esc_attr( $class ); ?>" name="<?php echo esc_attr( $this->get_option_key( $args['name'] ) ); ?>[<?php echo $i; ?>]" value="<?php echo $value; ?>" />
				<?php if ( $i != 0 ) { ?><button type="button" class="button button-small field-data-remove">X</button><?php } ?>
			</div>
			<?php } ?>
		</div>
		<button type="button" class="button button-secondary field-data-add">Add</button>
	<?php
		add_action("admin_footer-{$page_hook_id}", function() use ( $args ) {
			call_user_func([ $this, 'footer_scripts' ], $args);
		});
	}

	/**
	 * Head Scripts:
	 * @since 1.0.0
	 */
	public function head_scripts(){
	?>

		<style type="text/css">
			.field-group {
				display: flex;
			}
		</style>
<?php
	}

	/**
	 * Footer Script:
	 * - Repeater Box template.
	 * - Add and remove repater boxes.
	 * @since 1.0.0
	 */
	public function footer_scripts( $args ){
		$name = $args['name'];
		$class = ! empty( $args['class'] ) ? $args['class'] : '';
	?>

		<script type="text/html" id="tmpl-<?php echo esc_attr( $name ); ?>-repeater">
			<div class="field-group">
				<input type="text" class="<?php echo esc_attr( $class ); ?>" name="<?php echo esc_attr( $this->get_option_key( $name ) ); ?>[]" value="" />
				<button type="button" class="button button-small field-data-remove">X</button>
			</div>
		</script>

		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function($) {
				'use strict';
				$(function(){
					var $fieldData = $('#<?php echo esc_attr( $name ); ?>-field_data');
					$fieldData.next(".field-data-add").on( 'click', function(e){
						e.preventDefault();
						var template = wp.template('<?php echo esc_attr( $name ); ?>-repeater'),
							html = template();
						$fieldData.append( html );
					});
					$fieldData.on( 'click', '.field-data-remove', function(e){
						e.preventDefault();
						$(this).parent().remove();
					});
				});
			});
			//]]>
		</script>
<?php
	}

}
