<?php

namespace Converso\Frontend\Positions;


class TopLeft{

    public function render($children){
        ?>
        <style>
            .converso-top-left{
                position: fixed;
                top: 4%;
                left: 2%;
            }
        </style>

        <div class="converso-top-left">
            <?php echo $children ?>
        </div>
        <?php
    }    
}