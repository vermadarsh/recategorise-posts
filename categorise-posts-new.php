<?php

/**
 * Add custom command at wp-cli init.
 */
function lwpcli_initialize() {
	WP_CLI::add_command( 'categorize_posts', 'wpcliRecategorizePosts' );
}
add_action( 'cli_init', 'lwpcli_initialize' );

/**
 * Callback class when the custom command shall run.
 */
if ( ! class_exists( 'wpcliRecategorizePosts' ) ) {
	class wpcliRecategorizePosts {

		public function __construct() {
			$posts = array();
		}

		/**
		 * Fetch the posts.
		 *
		 * @return int[]|WP_Post[]
		 */
		public function fetch_posts() {
			$posts = get_posts(
				array(
					'post_type'      => 'post',
					'posts_per_page' => - 1,
					'fields'         => 'ids'
				)
			);
			if ( empty( $posts ) ) {
				WP_CLI::error( esc_html__( 'There isn\'t any post created yet!', 'learn-wpcli' ) );
			} else {
				return $posts;
			}
		}

		/**
		 * Categorize the posts.
		 */
		public function categorize_all_posts() {
			$this->posts = $this->fetch_posts();
/**
 * Add custom command at wp-cli init.
 */
function lwpcli_initialize() {
	WP_CLI::add_command( 'categorize_posts', 'wpcliRecategorizePosts' );
}
add_action( 'cli_init', 'lwpcli_initialize' );

/**
 * Callback class when the custom command shall run.
 */
if ( ! class_exists( 'wpcliRecategorizePosts' ) ) {
	class wpcliRecategorizePosts {

		public function __construct() {
			$posts = array();
		}

		/**
		 * Fetch the posts.
		 *
		 * @return int[]|WP_Post[]
		 */
		public function fetch_posts() {
			$posts = get_posts(
				array(
					'post_type'      => 'post',
					'posts_per_page' => - 1,
					'fields'         => 'ids'
				)
			);
			if ( empty( $posts ) ) {
				WP_CLI::error( esc_html__( 'There isn\'t any post created yet!', 'learn-wpcli' ) );
			} else {
				return $posts;
			}
		}

		/**
		 * Categorize the posts.
		 */
		public function categorize_all_posts() {
			$this->posts = $this->fetch_posts();
			if ( ! empty( $this->posts ) && is_array( $this->posts ) ) {
				foreach ( $this->posts as $post_id ) {
					$categories = get_the_category( $post_id );
					$has_default_category = $this->check_default_category( $categories );
					if( ! $has_default_category && empty( $categories ) ) {
						wp_set_object_terms( $post_id, $this->get_default_category(), 'category' );
					} else {
						wp_remove_object_terms( $post_id, $this->get_default_category(), 'category' );
					}
				}
				WP_CLI::success( esc_html__( 'All the posts are recategorized..!', 'learn-wpcli' ) );
			} else {
				WP_CLI::error( 'There is some error in accessing posts.' );
			}
		}

		/**
		 * Check if the post contains the default category.
		 *
		 * @param $categories
		 *
		 * @return bool
		 */
		public function check_default_category( $categories ) {

			if( empty( $categories ) ) {
				return false;
			} else {
				if( is_array( $categories ) ) {
					foreach($categories as $category) {
						$term_id = $category->term_id;
						if( $term_id === $this->get_default_category() ) {
							return true;
						}
					}
					return false;
				}
			}

		}

		/**
		 * Get the default category.
		 *
		 * @return int
		 */
		public function get_default_category() {
			return (int) get_option('default_category');
		}

	}
}