<?php
/**
 * Plugin Name: Recategorise Posts - WP_CLI
 * Author: Adarsh Verma
 * Version: 1.0.0
 * Description: This plugin creates custom wp-cli commands.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Function declared to debug code.
 */
if ( ! function_exists( 'debug' ) ) {
	function debug( $params ) {
		echo '<pre>';
		var_dump( $params );
		echo '</pre>';
		return;
	}
}

/**
 * Add custom command at wp-cli init.
 */
function lwpcli_initialize() {
	WP_CLI::add_command( 'categorise_posts', 'wpcli_recategorise_posts' );
}

add_action( 'cli_init', 'lwpcli_initialize' );

/**
 * Callback class when the custom command shall run.
 */
if ( ! class_exists( 'wpcli_recategorise_posts' ) ) {
	class wpcli_recategorise_posts {

		public function __construct() {
			
		}

		/**
		 * Fetch the posts.
		 *
		 * @return int[]|WP_Post[]
		 */
		public function fetch_posts( $page, $posts_per_page ) {
			$posts = get_posts(
				array(
					'post_type'      => 'post',
					'posts_per_page' => $posts_per_page,
					'fields'         => 'ids',
					'paged'          => $page,
					'post_status'    => 'any'
				)
			);

			return $posts;
		}

		/**
		 * Categorize the posts.
		 */
		public function categorize_all_posts() {
			$total_posts_by_status = wp_count_posts( 'post' );
			$total_posts           = 0;
			if ( ! empty( $total_posts_by_status ) && is_object( $total_posts_by_status ) ) {
				foreach ( $total_posts_by_status as $post_by_status ) {
					$total_posts += (int) $post_by_status;
				}
			}
			if ( 0 === $total_posts ) {
				WP_CLI::error( esc_html__( 'There isn\'t any post created yet!', 'learn-wpcli' ) );
			} else {
				$posts_per_page = (int) get_option( 'posts_per_page' );
				$pages          = (int) ( $total_posts / $posts_per_page ) + 1;
				for ( $i = 1; $i <= $pages; $i ++ ) {
					$posts = $this->fetch_posts( $i, $posts_per_page );
					if ( ! empty( $posts ) && is_array( $posts ) ) {
						foreach ( $posts as $post_id ) {
							$categories           = get_the_category( $post_id );
							$has_default_category = $this->check_default_category( $categories );
							if ( ! $has_default_category && empty( $categories ) ) {
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

			if ( empty( $categories ) ) {
				return false;
			} else {
				if ( is_array( $categories ) ) {
					foreach ( $categories as $category ) {
						$term_id = $category->term_id;
						if ( $term_id === $this->get_default_category() ) {
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
			return (int) get_option( 'default_category' );
		}

	}
}