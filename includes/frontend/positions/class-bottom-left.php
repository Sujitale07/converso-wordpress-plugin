<?php

namespace Converso\Frontend\Positions;


class BottomLeft{

    public function render($children, $link = "#", $tab = false){
        ob_start();
        ?>
        <style>
            .converso-bottom-left{
                position: fixed;
                bottom: 4%;
                left: 2%;
            }
        </style>

        <div id="converso-wp-button" class="converso-bottom-left">
            <a href="<?php echo $link ?>" <?php  echo $tab ? "target='_blank'" : '' ?>>
                <?php echo $children ?>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }    
}