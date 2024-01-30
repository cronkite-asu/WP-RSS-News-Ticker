<?php
namespace Rssnewsticker;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class SettingsPage extends Settings {

	public function __construct() {
		$this->id = 'rssnewsticker';
		$this->page_title = 'Ticker Feed';
		$this->parent_menu = 'options-general.php';
		$this->menu_title = 'Ticker';

		$this->define_fields();
add_action( $this->id . '_settings_sanitized', [ $this, 'sanitize_callback' ], 10, 4 );

		parent::__construct();
	}

	protected function define_fields() {
		$this->fields['feed_config_section'] = [
			'name' => 'feed_config_section',
			'title' => 'Feed Configuration',
			'description' => 'Settings for the RSS feed.',
			'type' => 'section'
		];

		$this->fields['feed_name'] = [
			'name' => 'feed_name',
			'title' => 'Feed Name',
			'description' => 'Keep this name simple as it is used to forms your this feed URL. The feed will be available at ' . site_url('/feed/'),
			'type' => 'text',
			'default' => 'ticker',
			'section' => 'feed_config_section'
		];

		$this->fields['ap_config_section'] = [
			'name' => 'ap_config_section',
			'title' => 'AP Configuration',
			'description' => 'Settings for AP News feed.',
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
			'description' => 'AP product ID.',
			'type' => 'text',
			'class' => 'ap-input',
			'section' => 'ap_config_section'
		];

		$this->fields['ap_api_key'] = [
			'name' => 'ap_api_key',
			'title' => 'API key',
			'description' => 'Key to send for API auth.',
			'type' => 'text',
			'class' => 'ap-input regular-text',
			'section' => 'ap_config_section'
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
		add_action( "admin_head-{$page_hook_id}", [ $this, 'head_scripts' ] );
		add_action( "admin_footer-{$page_hook_id}", [ $this, 'footer_scripts' ] );
	}

	/**
	 * Head Scripts:
	 * @since 1.0.0
	 */
	public function head_scripts(){
	?>

		<style type="text/css">
			input[type=text] + p.errorMessage {
				display: inline;
				margin-left: 5px;
			}

			.errorField {
				border: 1px solid red;
			}

			.errorMessage {
				color: red;
			}
		</style>
<?php
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

				var regEx = /^[a-zA-Z0-9._-]{0,63}$/;

				if (!regEx.test($(this).val())) {
					outputErrorMessage($(this),
					'<p class="errorMessage">Enter a valid path.</p>');
				} else {
					$(this).removeClass('errorField');
					$(this).next(".errorMessage").remove();
					updateLastText('feed_name-description',$(this).val());
				}
			});

			$apProductIDInput.on("change keyup paste", function() {

				if (isNaN($(this).val())) {
					outputErrorMessage($(this),
					'<p class="errorMessage">Enter a valid product ID.</p>');
				} else {
					$(this).removeClass('errorField');
					$(this).next(".errorMessage").remove();
				}
			});

			$apAPIKeyInput.on("change keyup paste", function() {

				var regEx = /^[a-z0-9._-]{0,28}$/;

				if (!regEx.test($(this).val())) {
					outputErrorMessage($(this),
					'<p class="errorMessage">Enter a valid API key.</p>');
				} else {
					$(this).removeClass('errorField');
					$(this).next(".errorMessage").remove();
				}
			});

			$apPreFeedInput.on("change keyup paste", function() {

				if ($.trim( $(this).val() ) == '') {
					outputErrorMessage($(this),
					'<p class="errorMessage">Please fill in text.</p>');
				} else {
					$(this).removeClass('errorField');
					$(this).next(".errorMessage").remove();
				}
			});

			// listen for any input and update the submit button
			$( "body" ).on( "change keyup paste", "input", function( event ) {
				updateButton('submit', "errorMessage");
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

		function outputErrorMessage($inputField, $errorMessage) {
			// If there is a previous message
			if ($inputField.next().hasClass( "errorMessage" )) {
				// Remove the previous message
				$inputField.next(".errorMessage").remove();
			} // end if

			// Show the error message
			$inputField.after($errorMessage);
			// 'Highlight' the field
			$inputField.addClass('errorField');
			$inputField.focus();
		} // end function outputErrorMessage($inputField, $errorMessage)

		function updateAPInputs($enabled, $searchclass) {
			const $list = document.getElementsByClassName($searchclass);
			for (var $node of $list) {
				// set each disabled with reverse of $enabled var
				$node.readOnly = !$enabled;
			}
		}

		function updateButton($id, $searchclass) {
			const $button = document.getElementById($id);
			if (document.getElementsByClassName($searchclass).length > 0) {
				$button.disabled = true;
			} else {
				$button.disabled = false;
			}
		}

		//]]>
	</script>
<?php
	}

	/**
	 * Sanitizes the checkbox field.
	 */
	public function sanitize_callback( $input, $fields, $post, $obj ) {
		if ( ! wp_http_validate_url( site_url('/feed/') . $input['feed_name'] ) ) {
			wp_die( "Invalid Feed Name" );
		}
	}
}
