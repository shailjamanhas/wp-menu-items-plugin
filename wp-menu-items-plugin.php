<?php
/*
Plugin Name: Menu Items Display
Description: Register a "Menu Item" CPT and provide a [menu_items] shortcode to display items in Bootstrap cards.
Version: 1.0
Author: Copilot
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Register CPT */
function mid_register_menu_item_cpt() {
	$labels = array(
		'name'               => 'Menu Items',
		'singular_name'      => 'Menu Item',
		'add_new_item'       => 'Add New Menu Item',
		'edit_item'          => 'Edit Menu Item',
		'new_item'           => 'New Menu Item',
		'view_item'          => 'View Menu Item',
	);
	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'show_in_rest'       => true,
		'supports'           => array( 'title', 'editor', 'thumbnail' ),
		'has_archive'        => true,
		'rewrite'            => array( 'slug' => 'menu' ),
	);
	register_post_type( 'menu_item', $args );
}
add_action( 'init', 'mid_register_menu_item_cpt' );

/* Add meta box for price and availability */
function mid_add_meta_boxes() {
	add_meta_box( 'mid_meta', 'Menu Details', 'mid_render_meta_box', 'menu_item', 'normal', 'default' );
}
add_action( 'add_meta_boxes', 'mid_add_meta_boxes' );

function mid_render_meta_box( $post ) {
	wp_nonce_field( 'mid_save_meta', 'mid_meta_nonce' );
	$price = get_post_meta( $post->ID, '_mid_price', true );
	$available = get_post_meta( $post->ID, '_mid_available', true );
	?>
	<p>
		<label for="mid_price">Price (e.g. 9.99):</label><br>
		<input type="text" id="mid_price" name="mid_price" value="<?php echo esc_attr( $price ); ?>" />
	</p>
	<p>
		<label>
			<input type="checkbox" name="mid_available" value="1" <?php checked( $available, '1' ); ?> />
			Available
		</label>
	</p>
	<?php
}

/* Save meta */
function mid_save_meta( $post_id ) {
	if ( ! isset( $_POST['mid_meta_nonce'] ) || ! wp_verify_nonce( $_POST['mid_meta_nonce'], 'mid_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( isset( $_POST['post_type'] ) && 'menu_item' === $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}
	// Price
	if ( isset( $_POST['mid_price'] ) ) {
		$price = floatval( str_replace( ',', '.', sanitize_text_field( wp_unslash( $_POST['mid_price'] ) ) ) );
		update_post_meta( $post_id, '_mid_price', $price );
	} else {
		delete_post_meta( $post_id, '_mid_price' );
	}
	// Availability
	$available = isset( $_POST['mid_available'] ) ? '1' : '';
	update_post_meta( $post_id, '_mid_available', $available );
}
add_action( 'save_post', 'mid_save_meta' );

/* Enqueue Bootstrap CSS for frontend display (optional: your theme may already have Bootstrap) */
function mid_enqueue_assets() {
	// Only enqueue on front-end where shortcode might be used
	if ( ! is_admin() ) {
		wp_register_style( 'mid-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css', array(), '5.3.2' );
		wp_enqueue_style( 'mid-bootstrap' );
	}
}
add_action( 'wp_enqueue_scripts', 'mid_enqueue_assets' );

/* Shortcode to output menu items */
function mid_menu_items_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'count' => 10,
	), $atts, 'menu_items' );

	$query = new WP_Query( array(
		'post_type'      => 'menu_item',
		'posts_per_page' => intval( $atts['count'] ),
		'post_status'    => 'publish',
	) );

	ob_start();

	if ( ! $query->have_posts() ) {
		?>
		<div class="alert alert-info">No menu items found.</div>
		<?php
		return ob_get_clean();
	}

	?>
	<div class="row">
	<?php
	while ( $query->have_posts() ) {
		$query->the_post();
		$post_id = get_the_ID();
		$title = get_the_title();
		$permalink = get_permalink();
		$description = get_the_excerpt() ? get_the_excerpt() : wp_strip_all_tags( get_the_content() );
		$description = mb_strimwidth( $description, 0, 120, '...' );
		$price = get_post_meta( $post_id, '_mid_price', true );
		$available = get_post_meta( $post_id, '_mid_available', true );
		?>
		<div class="col-md-6 mb-3">
			<div class="card h-100">
				<div class="card-body">
					<h5 class="card-title">
						<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
						<?php if ( '1' !== $available ) : ?>
							<span class="badge bg-secondary">Unavailable</span>
						<?php endif; ?>
					</h5>
					<p class="card-text"><?php echo esc_html( $description ); ?></p>
				</div>
				<div class="card-footer d-flex justify-content-between align-items-center">
					<strong><?php echo '$' . number_format( floatval( $price ), 2 ); ?></strong>
					<a href="<?php echo esc_url( $permalink ); ?>" class="btn btn-sm btn-primary">View</a>
				</div>
			</div>
		</div>
		<?php
	}
	wp_reset_postdata();
	?>
	</div>
	<?php

	return ob_get_clean();
}
add_shortcode( 'menu_items', 'mid_menu_items_shortcode' );

/* Flush rewrite rules on activation to register CPT permalinks */
function mid_activate_plugin() {
	mid_register_menu_item_cpt();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'mid_activate_plugin' );

function mid_deactivate_plugin() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'mid_deactivate_plugin' );
