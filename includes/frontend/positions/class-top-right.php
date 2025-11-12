<?php

namespace Converso\Frontend\Positions;


class TopRight{

    public function render($children){
        ?>
        <style>
            .converso-top-right{
                position: fixed;
                top: 4%;
                right: 2%;
            }
        </style>

        <div class="converso-top-right">
            <?php echo $children ?>
        </div>
        <?php
    }    
}