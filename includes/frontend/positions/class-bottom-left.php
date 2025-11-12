<?php

namespace Converso\Frontend\Positions;


class BottomLeft{

    public function render($children){
        ?>
        <style>
            .converso-bottom-left{
                position: fixed;
                bottom: 4%;
                left: 2%;
            }
        </style>

        <div class="converso-bottom-left">
            <?php echo $children ?>
        </div>
        <?php
    }    
}