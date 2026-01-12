<?php


namespace Converso\Modules;
use Converso\Frontend\Buttons\BtnEight;
use Converso\Frontend\Buttons\BtnEighteen;
use Converso\Frontend\Buttons\BtnEleven;
use Converso\Frontend\Buttons\BtnFifteen;
use Converso\Frontend\Buttons\BtnFive;
use Converso\Frontend\Buttons\BtnFour;
use Converso\Frontend\Buttons\BtnFourteen;
use Converso\Frontend\Buttons\BtnNine;
use Converso\Frontend\Buttons\BtnNineteen;
use Converso\Frontend\Buttons\BtnOne;
use Converso\Frontend\Buttons\BtnSeven;
use Converso\Frontend\Buttons\BtnSeventeen;
use Converso\Frontend\Buttons\BtnSix;
use Converso\Frontend\Buttons\BtnSixteen;
use Converso\Frontend\Buttons\BtnTen;
use Converso\Frontend\Buttons\BtnThirteen;
use Converso\Frontend\Buttons\BtnThree;
use Converso\Frontend\Buttons\BtnTwelve;
use Converso\Frontend\Buttons\BtnTwenty;
use Converso\Frontend\Buttons\BtnTwo;
class StylingAndPosition{
    public function __construct(){
        add_action("admin_enqueue_scripts", [$this, 'enqueue_assets']);
        add_action("admin_init", [$this, "register_settings"]);
    }

    public function enqueue_assets($hook){
        if ($hook !== 'toplevel_page_converso') return;

        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        if ($tab !== 'styling-and-positioning') return;

        wp_enqueue_style("converso-styling-and-position", CONVERSO_PLUGIN_URL . "assets/css/styling-and-position.css",[] , '1.0', false);
        wp_enqueue_script("converso-styling-and-position", CONVERSO_PLUGIN_URL . "assets/js/styling-and-position.js", ['jquery'], '1.0', true);
    }

    public function register_settings(){
        register_setting(
            'converso_sap_button_style_settings',
            'converso_sap_button_style_data'   ,
            [
                'type' => 'string',
                'sanitize_callback' => [$this, 'sanitize_button_style'],
                'default' => 'btn-1',
            ]
        );

        register_setting(
        'converso_sap_button_style_settings',
        'converso_sap_button_position_data',
        [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitize_button_position'],
            'default' => 'bottom-right',
        ]
    );

    }
    
    public function sanitize_button_style($input) {
        $allowed = [    
            'btn-1', 
            'btn-2', 
            'btn-3', 
            'btn-4', 
            'btn-5', 
            'btn-6', 
            'btn-7', 
            'btn-8', 
            'btn-9', 
            'btn-10',
            'btn-11',
            'btn-12',
            'btn-13',
            'btn-14',
            'btn-15',
            'btn-16',
            'btn-17',
            'btn-18',
            'btn-19',
            'btn-20'
        ];
        return in_array($input, $allowed, true) ? $input : 'btn-1';
    }

    public function sanitize_button_position($input) {
        $allowed = ['top-right', 'top-left', 'bottom-right', 'bottom-left'];
        return in_array($input, $allowed, true) ? $input : 'bottom-right';
    }

    public function render(){
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Styling & Position</h1>
            <div class="mt-10">
                <?php 
                    $button_style = get_option('converso_sap_button_style_data', "btn-1");  
                    $button_position = get_option('converso_sap_button_position_data', 'bottom-right');
    
                ?>
                <?php // echo $button_style . "s" ?>
                <form method="POST" action="options.php" >
                    <h3>Button Style</h3>
                    <?php settings_fields('converso_sap_button_style_settings'); ?>
                    <?php do_settings_sections('converso_sap_button_style_settings'); ?>
                    <?php settings_errors('converso_sap_button_style_settings'); ?>
                    
                    <div class="button-grid">
                        <?php 
                        $styles = [
                            'btn-1'=> new BtnOne(), 
                            'btn-2'=> new BtnTwo(), 
                            'btn-3'=> new BtnThree(), 
                            'btn-4'=> new BtnFour(), 
                            'btn-5'=> new BtnFive(), 
                            'btn-6'=> new BtnSix(), 
                            'btn-7'=> new BtnSeven(), 
                            'btn-8'=> new BtnEight(), 
                            'btn-9'=> new BtnNine(), 
                            'btn-10'=> new BtnTen(),
                            'btn-11'=> new BtnEleven(),
                            'btn-12'=> new BtnTwelve(),
                            'btn-13'=> new BtnThirteen(),
                            'btn-14'=> new BtnFourteen(),
                            'btn-15'=> new BtnFifteen(),
                            'btn-16'=> new BtnSixteen(),
                            'btn-17'=> new BtnSeventeen(),
                            'btn-18'=> new BtnEighteen(),
                            'btn-19'=>new BtnNineteen,
                            'btn-20'=>new BtnTwenty,
                        ];
                        foreach ($styles as $style=> $button): ?>
                        <div class="button-demo  <?php echo $button_style === $style ? 'active' : ''; ?>" data-style="<?php echo esc_attr($style); ?>">
                            <?php echo $button->render(); ?>
                            <input 
                                type="radio" 
                                name="converso_sap_button_style_data" 
                                value="<?php echo esc_attr($style); ?>" 
                                <?php checked($button_style, $style); ?>
                            />
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <br>
                    <hr>
                    <h3>Button Position</h3>
        
                    
                    <div class="button-position-grid">
                        <?php 
                        $positions = ['top-right', 'top-left', 'bottom-right', 'bottom-left'];
                        foreach ($positions as $pos): ?>
                            <div>
                                <input 
                                    type="radio" 
                                    id="<?php echo esc_attr($pos); ?>" 
                                    name="converso_sap_button_position_data" 
                                    value="<?php echo esc_attr($pos); ?>"
                                    <?php checked($button_position, $pos); ?>
                                >
                                <label for="<?php echo esc_attr($pos); ?>">
                                    <?php echo ucfirst(str_replace('-', ' ', $pos)); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php submit_button('Save Changes'); ?>
                </form>
            </div>
        </div>
        <?php
    }




}