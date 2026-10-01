<?php

function wpdp_render_popup( $agree_text, $decline_text, $decline_url, $expire ) {
	$agreeText   = $agree_text;
	$deplineText = $decline_text;
	$deplineUrl  = $decline_url;

	$layout    = (string) rwmb_meta( 'wpdp_image_layout' );
	$positions = array( 'left', 'right', 'top', 'bottom' );
	$image_url = '';
	$image_alt = '';

	if ( in_array( $layout, $positions, true ) ) {
		$image = rwmb_meta( 'wpdp_layout_image', array( 'size' => 'full' ) );
		if ( isset( $image[0] ) && is_array( $image[0] ) ) {
			$image = $image[0];
		}
		if ( is_array( $image ) ) {
			if ( ! empty( $image['full_url'] ) ) {
				$image_url = $image['full_url'];
			} elseif ( ! empty( $image['url'] ) ) {
				$image_url = $image['url'];
			}
			if ( ! empty( $image['alt'] ) ) {
				$image_alt = $image['alt'];
			}
		}
	}

	$classes = array( 'wpdp-white-popup', 'mfp-hide' );
	$has_image = ( '' !== $image_url );
	if ( $has_image ) {
		$classes[] = 'wpdp-has-image';
		$classes[] = 'wpdp-image-' . $layout;
	}

	echo '<div id="wp-disclaimer-popup" class="' . esc_attr( implode( ' ', $classes ) ) . '">';

	if ( $has_image ) {
		$aria = ( '' !== $image_alt )
			? ' role="img" aria-label="' . esc_attr( $image_alt ) . '"'
			: ' aria-hidden="true"';
		echo '<div class="wpdp-layout">';
		echo '<div class="wpdp-image" style="' . esc_attr( 'background-image:url(' . esc_url( $image_url ) . ')' ) . '"' . $aria . '></div>';
		echo '<div class="wpdp-content">';
	}

	include plugin_dir_path( __FILE__ ) . 'template/modal-base.php';

	if ( $has_image ) {
		echo '</div></div>';
	}

	echo '</div>';
}

function wp_disclaimer_popup_function() {

	$enable = rwmb_meta( 'wpdp_enable_disclaimer', array( 'object_type' => 'setting' ), 'wpdp_settings' );
	$postID = rwmb_meta( 'wpdp_post', array( 'object_type' => 'setting' ), 'wpdp_settings' );
	$expire = rwmb_meta( 'wpdp_cookie_expiration', array( 'object_type' => 'setting' ), 'wpdp_settings' );
	$agreeText = rwmb_meta( 'wpdp_agree_text', array( 'object_type' => 'setting' ), 'wpdp_settings' );
	$deplineText = rwmb_meta( 'wpdp_decline_text', array( 'object_type' => 'setting' ), 'wpdp_settings' );
	$deplineUrl = rwmb_meta( 'wpdp_decline_url', array( 'object_type' => 'setting' ), 'wpdp_settings' );

	$alto = rwmb_meta( 'wpdp_alig', array( 'object_type' => 'setting' ), 'wpdp_settings' );

	?>
	<script>var alignTop = "<?php echo $alto; ?>";</script>
	<?php


	if ( $enable ) {

		global $post;
		$disable_single = rwmb_get_value( 'wpdp_disable_single', '', $post->ID ); // check for single disabling popup

		// WP_Query arguments
		$args = array(
			'post_type' => array( 'wp-disclaimer-popup' ),
			'p'	=>	$postID,
		);
	
		// The Query
		$query = new WP_Query( $args );
	
		// The Loop
		if ( $query->have_posts() && !$disable_single ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				wpdp_render_popup( $agreeText, $deplineText, $deplineUrl, $expire );
			}
		} else {
			// no posts found
		}
	
		// Restore original Post Data
		wp_reset_postdata();

	} else {

		global $post;

		$enable_single = rwmb_get_value( 'wpdp_enable_single', '', $post->ID );
		$popup_single = rwmb_get_value( 'wpdp_disclaimer_select', '', $post->ID );


		if ( $enable_single ) {

			// WP_Query arguments
			$args = array(
				'post_type' => array( 'wp-disclaimer-popup' ),
				'p'	=>	$popup_single,
			);
		
			// The Query
			$query = new WP_Query( $args );

			// The Loop
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					wpdp_render_popup( $agreeText, $deplineText, $deplineUrl, $expire );
				}
			} else {
				// no posts found
			}
		
			// Restore original Post Data
			wp_reset_postdata();

		}
		

	}

   
}
add_action( 'wp_footer', 'wp_disclaimer_popup_function' );	
