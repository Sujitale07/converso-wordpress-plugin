<?php

namespace Converso\Frontend\Positions;


class TopLeft{

    public function render($children, $href = "#", $tab = false){
        ob_clean();
        ?>
        <style>
            .converso-top-left{
                position: fixed;
                top: 4%;
                left: 2%;
            }
        </style>

        <div id="converso-wp-button"  class="converso-top-left">
            <a href="<?php echo $href ?>" <?php  echo $tab ? "target='_blank'" : '' ?>>
                <?php echo $children ?>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }    
}