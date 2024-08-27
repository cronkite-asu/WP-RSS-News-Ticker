<?php

class Rssnewsticker_Admin_Settings_Page extends Rssnewsticker_Admin_Settings {

	public function __construct( $plugin_name, $version ) {
		$this->id = $plugin_name;
		$this->page_title = 'RSS Ticker';
		$this->parent_menu = 'options-general.php';
		$this->menu_title = 'RSS Ticker';

		$this->define_fields();
		add_action( $this->id . '_settings_sanitized', [ $this, 'sanitize_callback' ], 10, 4 );
		add_action( 'add_option_' . $this->get_option_name(), [ $this, 'add_option_callback' ], 10, 1);
		add_action( 'update_option_' . $this->get_option_name(), [ $this, 'update_option_callback' ], 10, 2);

		parent::__construct( $plugin_name, $version );
	}

	protected function define_fields() {
		$this->fields['feed_config_section'] = [
			'name' => 'feed_config_section',
			'title' => 'RSS Feed Configuration',
			'description' => 'Settings for the RSS feed.',
			'type' => 'section'
		];

		$this->fields['feed_name'] = [
			'name' => 'feed_name',
			'title' => 'Feed Name',
			'description' => 'Keep this name simple as it is used to form the feed URL. The feed will be available at ' . site_url('/feed/'),
			'type' => 'text',
			'maxlength' => 32,
			'pattern' => '^[a-zA-Z0-9_]*$',
			'default' => 'ticker',
			'section' => 'feed_config_section',
			'required' => true,
			'tooltip' => 'Use the letters A – Z (both uppercase and lowercase), the numbers 0 – 9, and the underscore "_"',
		];

		$this->fields['ap_config_section'] = [
			'name' => 'ap_config_section',
			'title' => 'Associated Press Media API',
			'description' => 'Settings for <a href="https://api.ap.org/media/v/docs/index.html#t=Getting_Started_API.htm" target="_blank" rel="noopener noreferrer" >AP Media API</a>. Requires an <a href="https://newsroom.ap.org/mediaapi/" target="_blank" rel="noopener noreferrer" >AP Newsroom</a> account.',
			'type' => 'section'
		];

		$this->fields['ap_enable'] = [
			'name' => 'ap_enable',
			'title' => 'Enable AP Feed',
			'type' => 'checkbox',
			'section' => 'ap_config_section'
		];

		$this->fields['ap_productid'] = [
			'name' => 'ap_productid',
			'title' => 'AP product ID',
			'description' => 'To find available product ids: log into AP Newsroom, go to AP Media API and they listed under <a href="https://newsroom.ap.org/mediaapi/entitlements" target="_blank" rel="noopener noreferrer" >Entitlements</a>.',
			'type' => 'text',
			'maxlength' => 8,
			'pattern' => '^[0-9]*$',
			'class' => 'ap-input',
			'section' => 'ap_config_section',
			'tooltip' => 'This field only allows the numbers 0 – 9',
		];

		$this->fields['ap_api_key'] = [
			'name' => 'ap_api_key',
			'title' => 'API key',
			'description' => 'To find your access key: log into AP Newsroom, go to <a href="https://newsroom.ap.org/mediaapi/" target="_blank" rel="noopener noreferrer" >AP Media API</a> and click <b>See my API keys</b>.',
			'type' => 'text',
			'maxlength' => 32,
			'pattern' => '^[a-z0-9]*$',
			'class' => 'ap-input regular-text',
			'section' => 'ap_config_section',
			'autocomplete' => 'off',
			'tooltip' => 'This field only allows a – z (lowercase), and the numbers 0 – 9',
		];

		$this->fields['ap_page_size'] = [
			'name' => 'ap_page_size',
			'title' => 'Page Size',
			'description' => 'Number of news stories to retrieve.',
			'type' => 'number',
			'class' => 'ap-input tiny-text',
			'default' => 5,
			'max' => 10,
			'section' => 'ap_config_section'
		];

		$this->fields['ap_pre_feed'] = [
			'name' => 'ap_pre_feed',
			'title' => 'Intro Text',
			'description' => 'Text to display before the AP headlines.',
			'type' => 'text',
			'class' => 'ap-input large-text',
			'default' => 'The latest headlines from the Associated Press',
			'section' => 'ap_config_section'
		];
	}

	/**
	 * Load Scripts
	 * @since 1.0.0
	 */
	public function enqueue_scripts( $hook_suffix ){
		$page_hook_id = $this->get_hook_suffix();
		add_action( "admin_footer-{$page_hook_id}", [ $this, 'footer_scripts' ] );
	}

	/**
	 * Footer Scripts:
	 * - Update url text on input.
	 * - Validate url path.
	 * @since 1.0.0
	 */
	public function footer_scripts(){
	?>
	<script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready( function($) {
			var $feedNameInput = $('#feed_name-input');
			var $apEnableInput = $('#ap_enable-input');
			var $apProductIDInput = $('#ap_productid-input');
			var $apAPIKeyInput = $('#ap_api_key-input');
			var $apPreFeedInput = $('#ap_pre_feed-input');

			updateAPInputs($apEnableInput.is(':checked'),"ap-input");
			updateLastText('feed_name-description',$feedNameInput.val());

			$apEnableInput.on("change", function() {

				$enabled=$(this).is(':checked')
				updateAPInputs($enabled, "ap-input");
			});

			$feedNameInput.on("change keyup paste", function() {

				var regEx = /^[a-zA-Z0-9_]{0,27}$/;

				if (regEx.test($(this).val())) {
					updateLastText('feed_name-description',$(this).val());
				}
			});
		});

		function updateLastText($id, $text) {
			const $node = document.getElementById($id);

			if (JSON.stringify($node) == "null") {
				return;
			}

			const $newtext = document.createTextNode($text);

			if ($node.childNodes.length > 1) {
				$node.replaceChild($newtext,$node.lastChild);
			} else {
				$node.appendChild($newtext);
			}
		}

		function updateAPInputs($enabled, $searchclass) {
			const $list = document.getElementsByClassName($searchclass);
			for (var $node of $list) {
				// set each disabled with reverse of $enabled var
				$node.readOnly = !$enabled;
			}
		}

		//]]>
	</script>
<?php
	}

	/**
	 * Sanitizes the feed name field.
	 */
	public function sanitize_callback( $input, $fields, $post, $obj ) {
		if ( ! wp_http_validate_url( site_url('/feed/') . $input['feed_name'] ) ) {
			wp_die( "Invalid Feed Name" );
		}
	}

	/**
	 * Update options callback.
	 */
	public function add_option_callback( $value ) {
		delete_option( 'rewrite_rules' );
	}

	/**
	 * Update options callback.
	 */
	public function update_option_callback( $old_value, $value ) {
		if ( $old_value['feed_name'] != $value['feed_name'] ) {
			delete_option( 'rewrite_rules' );
		}
	}
}
