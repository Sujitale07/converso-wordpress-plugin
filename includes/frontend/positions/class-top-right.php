<?php

namespace Connectapre\Frontend\Positions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TopRight{

    public function __construct(){
        add_action("wp_enqueue_scripts", [$this, "enqueue_scripts"]);
    }

    public function enqueue_scripts(){
        wp_enqueue_style("connectapre-top-right-css",CONNECTAPRE_PLUGIN_URL . "assets/frontend/css/top-right.css", [], 1, false);
    }

    public function render( $children, $href = "#", $tab = false ) {
		$allowed_html = wp_kses_allowed_html( 'post' );
		$allowed_html['svg']  = array(
			'xmlns'       => true,
			'viewbox'     => true,
			'width'       => true,
			'height'      => true,
			'fill'        => true,
			'class'       => true,
			'role'        => true,
			'aria-hidden' => true,
			'focusable'   => true,
		);
		$allowed_html['path'] = array(
			'd'    => true,
			'fill' => true,
		);
		$allowed_html['g']    = array(
			'fill' => true,
		);
		$allowed_html['defs'] = array();
		$allowed_html['div']  = array(
			'class' => true,
			'id'    => true,
		);

		ob_start();
		?>
		<div id="connectapre-wp-button" class="connectapre-top-right">
			<a href="<?php echo esc_url( $href ); ?>" <?php echo $tab ? "target='_blank'" : ''; ?>>
				<?php echo wp_kses( $children, $allowed_html ); ?>
			</a>
		</div>
		<?php
		return ob_get_clean();
	}    
}

