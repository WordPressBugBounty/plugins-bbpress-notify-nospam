<?php
/**
 * Datalayer for the Dry Run tool.
 *
 * Provides helpers to query topics and replies for the dry-run UI.
 *
 * @package bbPress_Notify_Nospam
 * @author  vinnyalves
 */

defined( 'ABSPATH' ) || die( 'No direct access allowed' );

// phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital,PEAR.NamingConventions.ValidClassName.Invalid
/**
 * DAO for dry-run queries.
 *
 * Kept with historical class name for backward compatibility.
 *
 * @since 1.0.0
 */
class bbPress_Notify_noSpam_DAL_Dry_Run_Dao extends bbPress_Notify_noSpam {


	/**
	 * Constructor.
	 *
	 * Intentionally empty to avoid parent initialization during unit tests.
	 *
	 * @return void
	 */
	public function __construct() {
		// NOOP. We don't want PHP to call the parent automatically.
	}

	/**
	 * Query the database for topics given a user-provided search string.
	 *
	 * @param array $args Query args compatible with get_posts().
	 *
	 * @return array Associative array of post_id => formatted title.
	 */
	public function get_topics( $args = array() ) {
		global $wpdb;

		$post_type = $this->get_topic_post_type();

		$defaults = array(
			's'                   => '',
			'posts_per_page'      => -1,
			'paged'               => 1,
			'ignore_sticky_posts' => false,
			'post_status'         => apply_filters( 'bbpnns_dry_run_post_status', array( 'publish' ) ),
		);

		// Let people change the args.
		$args = apply_filters( 'bbpnns/dal/dry_run_dao/get_topics', $args );

		// Then normalize supported values.
		$args = shortcode_atts( $defaults, $args );

		// Force post_type to be topics.
		$args['post_type'] = $post_type;

		return $this->_get_posts( $args, $want_parents = true );
	}

	/**
	 * Query the database for replies given a user-provided search string.
	 *
	 * @param array $args Query args compatible with get_posts().
	 *
	 * @return array Associative array of post_id => formatted title.
	 */
	public function get_replies( $args = array() ) {
		global $wpdb;

		$post_type = $this->get_reply_post_type();

		$defaults = array(
			's'                   => '',
			'posts_per_page'      => -1,
			'paged'               => 1,
			'ignore_sticky_posts' => false,
			'post_status'         => apply_filters( 'bbpnns_dry_run_post_status', array( 'publish' ) ),
		);

		// Let people change the args.
		$args = apply_filters( 'bbpnns/dal/dry_run_dao/get_replies', $args );

		// Then normalize supported values.
		$args = shortcode_atts( $defaults, $args );

		// Force post_type to be replies.
		$args['post_type'] = $post_type;

		return $this->_get_posts( $args, $want_parents = true );
	}

	/**
	 * Execute the query and format the returned posts for the UI.
	 *
	 * Normalizes whitespace-only search strings to behave like an empty
	 * search (return all posts). Optionally prepends parent titles.
	 *
	 * @see https://developer.wordpress.org/reference/functions/get_posts/
	 *
	 * @param array $args         Query args passed to `get_posts()`.
	 * @param bool  $want_parents Whether to include parent titles (forum/topic).
	 *
	 * @return array Associative array of post_id => formatted title.
	 */
	private function _get_posts( $args, $want_parents = false ) {
		// Normalize search string: treat whitespace-only searches as empty (return all posts).
		if ( isset( $args['s'] ) ) {
			$args['s'] = is_string( $args['s'] ) ? trim( $args['s'] ) : $args['s'];
			if ( '' === $args['s'] ) {
				// Empty search should not restrict results.
				unset( $args['s'] );
			}
		}

		// Run the query.
		$posts = get_posts( $args );

		$full_posts = array();
		$results    = array();
		foreach ( (array) $posts as $post ) {
			$title = $post->post_title ? $post->post_title : __( 'No title', 'bbpress-notify-nospam' );
			// translators: 1: post title, 2: post type, 3: post ID.
			$results[ $post->ID ]    = sprintf( __( '%1$s, %2$s ID %3$d', 'bbpress-notify-nospam' ), $title, ucfirst( $post->post_type ), $post->ID );
			$full_posts[ $post->ID ] = $post;
		}

		if ( true === $want_parents ) {
			$parents = array();
			foreach ( $results as $id => $title ) {
				$post      = $full_posts[ $id ];
				$parent_id = $post->post_parent;

				if ( ! isset( $parents[ $parent_id ] ) ) {
					$parents[ $parent_id ] = $this->get_topic_post_type() === $post->post_type ? bbp_get_forum_title( $parent_id ) : bbp_get_topic_title( $parent_id );
				}

				$results[ $id ] = sprintf( '%s > %s', $parents[ $parent_id ], $results[ $id ] );
			}
		}

		return $results;
	}
}

// phpcs:enable PEAR.NamingConventions.ValidClassName.StartWithCapital,PEAR.NamingConventions.ValidClassName.Invalid

/*
 * End of file dry_run_dao.class.php
 */
/* Location: bbpress-notify-nospam/includes/dal/dry_run_dao.class.php */
