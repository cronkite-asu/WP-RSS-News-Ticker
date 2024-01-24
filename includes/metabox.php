<?php
namespace Rssnewsticker;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * A WordPress meta box that appears in the editor.
 */
class MetaBox
{
		/**
		 * The ID of the meta box.
		 *
		 * @var string
		 */
		private $id;

		/**
		 * The title of the meta box.
		 *
		 * @var string
		 */
		private $title;

		/**
		 * Screens where this meta box will appear.
		 *
		 * @var string[]
		 */
		private $screens;

		/**
		 * Screen context where the meta box should display.
		 *
		 * @var string
		 */
		private $context;

		/**
		 * The display priority of the meta box.
		 *
		 * @var string
		 */
		private $priority;

		/**
		 * Constructor.
		 *
		 * @param string	 $id
		 * @param string	 $title
		 * @param string[] $screens
		 * @param string	 $context
		 * @param string	 $priority
		 */
		public function __construct($id, $title, $screens = array(), $context = 'advanced', $priority = 'default')
		{
				if (is_string($screens)) {
						$screens = (array) $screens;
				}

				$this->id = $id;
				$this->title = $title;
				$this->screens = $screens;
				$this->context = $context;
				$this->priority = $priority;

		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post',			array( $this, 'save'				 ) );
		}

		/**
		 * Get the callable that will the content of the meta box.
		 *
		 * @return callable
		 */
		public function get_callback()
		{
				return array($this, 'render');
		}

		/**
		 * Get the screen context where the meta box should display.
		 *
		 * @return string
		 */
		public function get_context()
		{
				return $this->context;
		}

		/**
		 * Get the ID of the meta box.
		 *
		 * @return string
		 */
		public function get_id()
		{
				return $this->id;
		}

		/**
		 * Get the display priority of the meta box.
		 *
		 * @return string
		 */
		public function get_priority()
		{
				return $this->priority;
		}

		/**
		 * Get the screen(s) where the meta box will appear.
		 *
		 * @return array|string|WP_Screen
		 */
		public function get_screens()
		{
				return $this->screens;
		}

		/**
		 * Get the title of the meta box.
		 *
		 * @return string
		 */
		public function get_title()
		{
				return $this->title;
		}

	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box( $post_type ) {
		// Limit meta box to certain post types.
		$post_types = $this->screens;

		if ( in_array( $post_type, $post_types ) ) {
			add_meta_box(
				$this->id,
				$this->title,
				array( $this, 'render' ),
				$post_type,
				$this->context,
				$this->priority
			);
		}
	}
	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */

	public function save( $post_id ) {

		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST[$this->id . '_nonce'] ) ) {
			return $post_id;
		}

		$nonce = $_POST[$this->id . '_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, $this->id ) ) {
			return $post_id;
		}

		/*
		 * If this is an autosave, our form has not been submitted,
		 * so we don't want to do anything.
		 */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}

		/* OK, it's safe for us to save the data now. */

		// Sanitize the user input.
		$mydata = sanitize_text_field( $_POST[$this->id] );

		// Update the meta field.
		update_post_meta( $post_id, $this->id, $mydata );
	}

		/**
		 * Render the content of the meta box.
		 *
		 * @param WP_Post $post
		 */
		public function render($post)
		{

		// Add an nonce field so we can check for it later.
		wp_nonce_field( $this->id, $this->id . '_nonce' );

		// Use get_post_meta to retrieve an existing value from the database.
		$value = get_post_meta( $post->ID, $this->id, true );

		// Display the form, using the current value.
		?>
		<label for="myplugin_new_field">
			<?php _e( 'Description for this field', 'textdomain' ); ?>
		</label>
		<input type="text" id="<?php echo esc_attr( $this->id ); ?>" name="<?php echo esc_attr( $this->id ); ?>" value="<?php echo esc_attr( $value ); ?>" size="25" />
		<?php
		}
}
