<?php

namespace Converso\Frontend\Positions;


class BottomRight{

    public function render($children, $link = "#", $tab = false){
        ob_start();
        ?>
        <style>
            .converso-bottom-right{
                position: fixed;
                bottom: 4%;
                right: 2%;
            }
        </style>

        <div id="converso-wp-button"  class="converso-bottom-right">
            <a href="<?php echo $link?>" <?php  echo $tab ? "target='_blank'" : '' ?> >
                <?php echo $children ?>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }    
}