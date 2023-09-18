<?php
namespace App;

/**
 * Frontend Pages Handler
 */
class Frontend_SUPERFRETE {

	public function __construct() {
		add_shortcode( 'vue-app', array( $this, 'render_frontend' ) );
	}

	/**
	 * Render frontend app
	 *
	 * @param  array  $atts
	 * @param  string $content
	 *
	 * @return string
	 */
	public function render_frontend( $atts, $content = '' ) {
		wp_enqueue_style( 'superfrete-frontend' );
		wp_enqueue_script( 'superfrete-frontend' );

		$content .= '<div id="vue-frontend-app"></div>';

		return $content;
	}
}
