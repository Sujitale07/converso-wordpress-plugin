<?php

namespace Connectapre\Frontend\Positions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TopLeft{

    public function render($children, $href = "#", $tab = false){
        ob_start();
        ?>
        <style>
            .connectapre-top-left{
                position: fixed;
                top: 2%;
                left: 2%;
            }
        </style>

        <div id="connectapre-wp-button" class="connectapre-top-left">
            <a href="<?php echo esc_url( $href ); ?>" <?php echo $tab ? "target='_blank'" : ''; ?>>
                <?php echo $children; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }    
}

