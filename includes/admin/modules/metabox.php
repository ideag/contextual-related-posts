<?php
/**
 * Contextual Related Posts Metabox interface.
 *
 * @package   Contextual_Related_Posts
 * @author    Ajay D'Souza
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2009-2019 Ajay D'Souza
 */

/**** If this file is called directly, abort. ****/
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Function to add meta box in Write screens of Post, Page and Custom Post Types.
 *
 * @since   1.9.1
 *
 * @param text   $post_type Post Type.
 * @param object $post Post object.
 */
function crp_add_meta_box( $post_type, $post ) {

	// If metaboxes are disabled, then exit.
	if ( ! crp_get_option( 'show_metabox' ) ) {
		return;
	}

	// If current user isn't an admin and we're restricting metaboxes to admins only, then exit.
	if ( ! current_user_can( 'manage_options' ) && crp_get_option( 'show_metabox_admins' ) ) {
		return;
	}

	$args       = array(
		'public' => true,
	);
	$post_types = get_post_types( $args );

	/**
	 * Filter post types on which the meta box is displayed
	 *
	 * @since   2.2.0
	 *
	 * @param array $post_types Array of post types.
	 * @param array $post_types Post object.
	 */
	$post_types = apply_filters( 'crp_meta_box_post_types', $post_types, $post );

	if ( in_array( $post_type, $post_types, true ) ) {

		add_meta_box(
			'crp_metabox',
			'Contextual Related Posts',
			'crp_call_meta_box',
			$post_type,
			'advanced',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'crp_add_meta_box', 10, 2 );


/**
 * Function to call the meta box.
 *
 * @since   1.9.1
 */
function crp_call_meta_box() {
	global $post;

	/**** Add an nonce field so we can check for it later. */
	wp_nonce_field( 'crp_meta_box', 'crp_meta_box_nonce' );

	// Get the thumbnail settings. The name of the meta key is defined in thumb_meta parameter of the CRP Settings array.
	$crp_thumb_meta = get_post_meta( $post->ID, crp_get_option( 'thumb_meta' ), true );
	$value          = ( $crp_thumb_meta ) ? $crp_thumb_meta : '';

	// Get related posts specific meta.
	$crp_post_meta = get_post_meta( $post->ID, 'crp_post_meta', true );

	// Disable display option.
	if ( isset( $crp_post_meta['crp_disable_here'] ) ) {
		$disable_here = $crp_post_meta['crp_disable_here'];
	} else {
		$disable_here = 0;
	}

	if ( isset( $crp_post_meta['exclude_this_post'] ) ) {
		$exclude_this_post = $crp_post_meta['exclude_this_post'];
	} else {
		$exclude_this_post = 0;
	}

	// Manual related.
	if ( isset( $crp_post_meta['manual_related'] ) ) {
		$manual_related = $crp_post_meta['manual_related'];
	} else {
		$manual_related = '';
	}
	$manual_related_array = explode( ',', $manual_related );

	// Keyword - word or phrase.
	if ( isset( $crp_post_meta['keyword'] ) ) {
		$keyword = $crp_post_meta['keyword'];
	} else {
		$keyword = '';
	}

	?>
	<p>
		<label for="crp_disable_here"><strong><?php esc_html_e( 'Disable Related Posts display:', 'contextual-related-posts' ); ?></strong></label>
		<input type="checkbox" id="crp_disable_here" name="crp_disable_here" <?php checked( 1, $disable_here, true ); ?> />
		<br />
		<em><?php esc_html_e( 'If this is checked, then Contextual Related Posts will not automatically insert the related posts at the end of post content.', 'contextual-related-posts' ); ?></em>
	</p>

	<p>
		<label for="crp_exclude_this_post"><strong><?php esc_html_e( 'Exclude this post from the related posts list:', 'contextual-related-posts' ); ?></strong></label>
		<input type="checkbox" id="crp_exclude_this_post" name="crp_exclude_this_post" <?php checked( 1, $exclude_this_post, true ); ?> />
		<br />
		<em><?php esc_html_e( 'If this is checked, then this post will be excluded from the popular posts list.', 'contextual-related-posts' ); ?></em>
	</p>

	<p>
		<label for="keyword"><strong><?php esc_html_e( 'Keyword:', 'contextual-related-posts' ); ?></strong></label>
		<textarea class="large-text" cols="50" rows="5" id="crp_keyword" name="crp_keyword"><?php echo esc_textarea( stripslashes( $keyword ) ); ?></textarea>
		<em><?php esc_html_e( 'Enter either a word or a phrase that will be used to find related posts. If entered, the plugin will continue to search the `post_title` and `post_content` fields but will use this keyword instead of the values of the title and content of this post.', 'contextual-related-posts' ); ?></em>
	</p>

	<p>
		<label for="manual_related"><strong><?php esc_html_e( 'Manual related posts:', 'contextual-related-posts' ); ?></strong></label>
		<input type="text" id="manual_related" name="manual_related" value="<?php echo esc_attr( $manual_related ); ?>" style="width:100%" />
		<em><?php esc_html_e( 'Comma separated list of post, page or custom post type IDs. e.g. 188,320,500. These will be given preference over the related posts generated by the plugin.', 'contextual-related-posts' ); ?></em>
		<em><?php esc_html_e( 'Once you enter the list above and save this page, the plugin will display the titles of the posts below for your reference. Only IDs corresponding to published posts or custom post types will be retained.', 'contextual-related-posts' ); ?></em>
	</p>

	<?php if ( ! empty( $manual_related ) ) { ?>

		<strong><?php esc_html_e( 'Manual related posts:', 'contextual-related-posts' ); ?></strong>
		<ol>
		<?php
		foreach ( $manual_related_array as $manual_related_post ) {

			echo '<li>';

			$title = get_the_title( $manual_related_post );
			echo '<a href="' . esc_url( get_permalink( $manual_related_post ) ) . '" target="_blank" title="' . esc_attr( $title ) . '" class="wherego_title">' . esc_attr( $title ) . '</a>. ';
			printf(
				/* translators: Post type name */
				esc_html__( 'This post type is: %s', 'contextual-related-posts' ),
				'<em>' . esc_html( get_post_type( $manual_related_post ) ) . '</em>'
			);

			echo '</li>';
		}
		?>
		</ol>
	<?php } ?>

	<p>
		<label for="crp_thumb_meta"><strong><?php esc_html_e( 'Location of thumbnail', 'contextual-related-posts' ); ?>:</strong></label>
		<input type="text" id="crp_thumb_meta" name="crp_thumb_meta" value="<?php echo esc_attr( $value ); ?>" style="width:100%" />
		<em><?php esc_html_e( "Enter the full URL to the image (JPG, PNG or GIF) you'd like to use. This image will be used for the post. It will be resized to the thumbnail size set under Settings &raquo; Related Posts &raquo; Output Options", 'contextual-related-posts' ); ?></em>
		<em><?php esc_html_e( 'The URL above is saved in the meta field:', 'contextual-related-posts' ); ?></em> <strong><?php echo esc_html( crp_get_option( 'thumb_meta' ) ); ?></strong>
	</p>

	<p>
		<?php if ( function_exists( 'tptn_add_viewed_count' ) ) { ?>
			<em style="color:red"><?php esc_html_e( "You have Top 10 WordPress Plugin installed. If you are trying to modify the thumbnail, then you'll need to make the same change in the Top 10 meta box on this page.", 'contextual-related-posts' ); ?></em>
		<?php } ?>
	</p>

	<?php
	if ( $crp_thumb_meta ) {
		echo '<img src="' . esc_attr( $value ) . '" style="max-width:100%" />';
	}
	?>

	<?php
	/**
	 * Action triggered when displaying Contextual Related Posts meta box
	 *
	 * @since   2.2
	 *
	 * @param   object  $post   Post object
	 */
	do_action( 'crp_call_meta_box', $post );
}


/**
 * Function to save the meta box.
 *
 * @since   1.9.1
 *
 * @param mixed $post_id Post ID.
 */
function crp_save_meta_box( $post_id ) {

	$crp_post_meta = array();

	// Bail if we're doing an auto save.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// If our nonce isn't there, or we can't verify it, bail.
	if ( ! isset( $_POST['crp_meta_box_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['crp_meta_box_nonce'] ), 'crp_meta_box' ) ) { // Input var okay.
		return;
	}

	// If our current user can't edit this post, bail.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Update the thumbnail URL.
	if ( isset( $_POST['crp_thumb_meta'] ) ) {
		$thumb_meta = empty( $_POST['crp_thumb_meta'] ) ? '' : sanitize_text_field( wp_unslash( $_POST['crp_thumb_meta'] ) ); // Input var okay.
	}

	if ( ! empty( $thumb_meta ) ) {
		update_post_meta( $post_id, crp_get_option( 'thumb_meta' ), $thumb_meta );
	} else {
		delete_post_meta( $post_id, crp_get_option( 'thumb_meta' ) );
	}

	// Disable posts.
	if ( isset( $_POST['crp_disable_here'] ) ) {
		$crp_post_meta['crp_disable_here'] = 1;
	} else {
		$crp_post_meta['crp_disable_here'] = 0;
	}

	if ( isset( $_POST['crp_exclude_this_post'] ) ) {
		$crp_post_meta['exclude_this_post'] = 1;
	} else {
		$crp_post_meta['exclude_this_post'] = 0;
	}

	if ( isset( $_POST['crp_keyword'] ) ) {
		$crp_post_meta['keyword'] = sanitize_text_field( wp_unslash( $_POST['crp_keyword'] ) );
	}

	// Save Manual related posts.
	if ( isset( $_POST['manual_related'] ) ) {

		$manual_related_array = array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_POST['manual_related'] ) ) ) );

		foreach ( $manual_related_array as $key => $value ) {
			if ( 'publish' !== get_post_status( $value ) ) {
				unset( $manual_related_array[ $key ] );
			}
		}
		$crp_post_meta['manual_related'] = implode( ',', $manual_related_array );
	}

	/**
	 * Filter the CRP Post meta variable which contains post-specific settings
	 *
	 * @since   2.2.0
	 *
	 * @param   array   $crp_post_meta  CRP post-specific settings
	 * @param   int $post_id    Post ID
	 */
	$crp_post_meta = apply_filters( 'crp_post_meta', $crp_post_meta, $post_id );

	$crp_post_meta_filtered = array_filter( $crp_post_meta );

	/**** Now we can start saving */
	if ( empty( $crp_post_meta_filtered ) ) {   // Checks if all the array items are 0 or empty.
		delete_post_meta( $post_id, 'crp_post_meta' );  // Delete the post meta if no options are set.
	} else {
		update_post_meta( $post_id, 'crp_post_meta', $crp_post_meta_filtered );
	}

	// Clear cache of current post.
	$default_meta_keys = crp_cache_get_keys();
	foreach ( $default_meta_keys as $meta_key ) {
		delete_post_meta( $post_id, $meta_key );
	}

	/**
	 * Action triggered when saving Contextual Related Posts meta box settings
	 *
	 * @since   2.2
	 *
	 * @param   int $post_id    Post ID
	 */
	do_action( 'crp_save_meta_box', $post_id );
}
add_action( 'save_post', 'crp_save_meta_box' );
add_action( 'edit_attachment', 'crp_save_meta_box' );

