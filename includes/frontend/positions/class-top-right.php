<?php

namespace Converso\Frontend\Positions;


class TopRight{

    public function render($children, $href = "#", $tab = false){
        ob_start();
        ?>
        <style>
            .converso-top-right{
                position: fixed;
                top: 4%;
                right: 2%;
            }
        </style>

        <div id="converso-wp-button"  class="converso-top-right">
            <a href="<?php echo $href ?>" <?php  echo $tab ?  "target='_blank'" :  "" ?> >
                <?php echo $children ?>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }    
}