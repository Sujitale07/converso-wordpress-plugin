<?php

namespace Connectapre\Frontend\Positions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BottomRight{

    public function __construct(){
        add_action("wp_enqueue_scripts", [$this, "enqueue_scripts"]);
    }

    public function enqueue_scripts(){
        wp_enqueue_style("connectapre-bottom-right-css",CONNECTAPRE_PLUGIN_URL . "assets/frontend/css/bottom-right.css", [], 1, false);
    }

    public function render($children, $link = "#", $tab = false){
        ob_start();
        ?>
        <div id="connectapre-wp-button" class="connectapre-bottom-right">
            <a href="<?php echo esc_url( $link ); ?>" <?php echo $tab ? "target='_blank'" : ''; ?>>
                <?php echo $children; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }    
}

