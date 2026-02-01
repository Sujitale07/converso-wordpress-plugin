<?php

namespace Connectapre\Frontend\Positions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



class BottomLeft{

    public function render($children, $link = "#", $tab = false){
        ob_start();
        ?>
        <style>
            .connectapre-bottom-left{
                position: fixed;
                bottom: 4%;
                left: 2%;
            }
        </style>

        <div id="connectapre-wp-button" class="connectapre-bottom-left">
            <a href="<?php echo esc_url( $link ); ?>" <?php echo $tab ? "target='_blank'" : ''; ?>>
                <?php echo $children; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }    
}

