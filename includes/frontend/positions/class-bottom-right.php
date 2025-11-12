<?php

namespace Converso\Frontend\Positions;


class BottomRight{

    public function render($children){
        ?>
        <style>
            .converso-bottom-right{
                position: fixed;
                bottom: 4%;
                right: 2%;
            }
        </style>

        <div class="converso-bottom-right">
            <?php echo $children ?>
        </div>
        <?php
    }    
}