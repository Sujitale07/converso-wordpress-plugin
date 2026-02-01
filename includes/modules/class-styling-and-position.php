<?php


namespace Connectapre\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Connectapre\Frontend\Buttons\BtnEight;
use Connectapre\Frontend\Buttons\BtnEighteen;
use Connectapre\Frontend\Buttons\BtnEleven;
use Connectapre\Frontend\Buttons\BtnFifteen;
use Connectapre\Frontend\Buttons\BtnFive;
use Connectapre\Frontend\Buttons\BtnFour;
use Connectapre\Frontend\Buttons\BtnFourteen;
use Connectapre\Frontend\Buttons\BtnNine;
use Connectapre\Frontend\Buttons\BtnNineteen;
use Connectapre\Frontend\Buttons\BtnOne;
use Connectapre\Frontend\Buttons\BtnSeven;
use Connectapre\Frontend\Buttons\BtnSeventeen;
use Connectapre\Frontend\Buttons\BtnSix;
use Connectapre\Frontend\Buttons\BtnSixteen;
use Connectapre\Frontend\Buttons\BtnTen;
use Connectapre\Frontend\Buttons\BtnThirteen;
use Connectapre\Frontend\Buttons\BtnThree;
use Connectapre\Frontend\Buttons\BtnTwelve;
use Connectapre\Frontend\Buttons\BtnTwenty;
use Connectapre\Frontend\Buttons\BtnTwo;
class StylingAndPosition{
    public function __construct(){
        add_action("admin_enqueue_scripts", [$this, 'enqueue_scripts']);
        add_action("admin_init", [$this, "register_settings"]);
    }

    public function enqueue_scripts($hook){
        if ($hook !== 'toplevel_page_connectapre') return;

        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        if ($tab !== 'styling-and-positioning') return;

        wp_enqueue_style("connectapre-styling-and-position", CONNECTAPRE_PLUGIN_URL . "assets/css/styling-and-position.css",[] , '1.0', false);
        wp_enqueue_script("connectapre-styling-and-position", CONNECTAPRE_PLUGIN_URL . "assets/js/styling-and-position.js", ['jquery'], '1.0', true);
    }

    public function register_settings(){
        register_setting(
            'connectapre_sap_button_style_settings',
            'connectapre_sap_button_style_data'   ,
            [
                'type' => 'string',
                'sanitize_callback' => [$this, 'sanitize_button_style'],
                'default' => 'btn-1',
            ]
        );

        register_setting(
        'connectapre_sap_button_style_settings',
        'connectapre_sap_button_position_data',
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
        <div class="wrap relative !mt-5">
            
                <div class="h-full col-span-7 font-primary bg-white  rounded-lg !p-4 !px-6">
                    <h3 class="font-primary !mb-0 !mt-0 !text-xl"><?php esc_html_e('Styling & Position', 'connectapre'); ?></h3>
                    <p class="!mt-2 !text-sm text-gray-500"><?php esc_html_e('Customize the appearance and behavior of your WhatsApp widget', 'connectapre'); ?></p>
                    
                    <div class="!mt-8">
                        <?php 
                            $button_style = get_option('connectapre_sap_button_style_data', "btn-1");  
                            $button_position = get_option('connectapre_sap_button_position_data', 'bottom-right');
            
                        ?>
                        <?php // echo $button_style . "s" ?>
                        <form method="POST" action="options.php" >
                        
                            <?php settings_fields('connectapre_sap_button_style_settings'); ?>
                            <?php do_settings_sections('connectapre_sap_button_style_settings'); ?>
                            <?php settings_errors('connectapre_sap_button_style_settings'); ?>
                            
                            <h3 class="!text-sm !mt-0 !font-secondary !text-gray-600 !mb-2 !font-semibold"><?php esc_html_e('Button Styles', 'connectapre'); ?></h3>
                            <p class="!text-xs !text-gray-500 !mb-6 !font-secondary"><?php esc_html_e('Choose your WhatsApp chat button design - each style is optimized for engagement', 'connectapre'); ?></p>
                            
                            <div class="button-styles-gallery">
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
                                    'btn-19'=> new BtnNineteen,
                                    'btn-20'=> new BtnTwenty,
                                ];
                                
                                $counter = 0;
                                foreach ($styles as $style=> $button): 
                                    $counter++;
                                    $is_active = ($button_style === $style);
                                ?>
                                <div class="button-style-card <?php echo $is_active ? 'active' : ''; ?>" data-style="<?php echo esc_attr($style); ?>">
                                    <input 
                                        type="radio" 
                                        id="style-<?php echo esc_attr($style); ?>"
                                        name="connectapre_sap_button_style_data" 
                                        value="<?php echo esc_attr($style); ?>" 
                                        <?php checked($button_style, $style); ?>
                                        class="button-style-input"
                                    />
                                    <label for="style-<?php echo esc_attr($style); ?>" class="button-style-label">
                                        <!-- Selection Indicator -->
                                        <div class="style-selection-indicator">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </div>
                                        
                                        <!-- Style Number Badge -->
                                        <div class="style-number">
                                             <?php 
                                             /* translators: %d: style number */
                                             printf( esc_html__( 'Style %d', 'connectapre' ), (int) $counter ); 
                                             ?>
                                        </div>
                                        
                                        <!-- Button Preview -->
                                        <div class="button-preview-container">
                                            <?php echo $button->render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                        </div>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <h3 class="!text-sm !mt-6 !text-gray-600 !font-secondary !mb-2 !font-semibold"><?php esc_html_e('Button Position', 'connectapre'); ?></h3>
                            <p class="!text-xs !text-gray-500 !mb-6 !font-secondary"><?php esc_html_e('Select where your WhatsApp chat button will appear on your website', 'connectapre'); ?></p>
                            
                            <div class="position-cards-wrapper">
                                <?php 
                                $position_configs = [
                                    'bottom-right' => [
                                        'title' => __('Bottom Right', 'connectapre'),
                                        'description' => __('Most popular - Easy to reach', 'connectapre'),
                                        'recommended' => true
                                    ],
                                    'bottom-left' => [
                                        'title' => __('Bottom Left', 'connectapre'),
                                        'description' => __('Alternative placement', 'connectapre'),
                                        'recommended' => false
                                    ],
                                    'top-right' => [
                                        'title' => __('Top Right', 'connectapre'),
                                        'description' => __('Always visible on scroll', 'connectapre'),
                                        'recommended' => false
                                    ],
                                    'top-left' => [
                                        'title' => __('Top Left', 'connectapre'),
                                        'description' => __('Unique positioning', 'connectapre'),
                                        'recommended' => false
                                    ]
                                ];
                                
                                foreach ($position_configs as $pos => $config): 
                                    $is_active = ($button_position === $pos);
                                ?>
                                    <div class="position-card <?php echo $is_active ? 'active' : ''; ?>" data-position="<?php echo esc_attr($pos); ?>">
                                        <input 
                                            type="radio" 
                                            id="position-<?php echo esc_attr($pos); ?>" 
                                            name="connectapre_sap_button_position_data" 
                                            value="<?php echo esc_attr($pos); ?>"
                                            <?php checked($button_position, $pos); ?>
                                            class="position-input"
                                        >
                                        <label for="position-<?php echo esc_attr($pos); ?>" class="position-card-label">
                                            <?php if ($config['recommended']): ?>
                                                <span class="recommended-badge"><?php esc_html_e('Recommended', 'connectapre'); ?></span>
                                            <?php endif; ?>
                                            
                                            <!-- Browser Window Mockup -->
                                            <div class="browser-mockup">
                                                <!-- Browser Chrome -->
                                                <div class="browser-chrome">
                                                    <div class="browser-dots">
                                                        <span class="dot red"></span>
                                                        <span class="dot yellow"></span>
                                                        <span class="dot green"></span>
                                                    </div>
                                                    <div class="browser-url">yourwebsite.com</div>
                                                </div>
                                                
                                                <!-- Browser Content -->
                                                <div class="browser-content">
                                                    <!-- Simulated page content -->
                                                    <div class="content-lines">
                                                        <div class="line header"></div>
                                                        <div class="line"></div>
                                                        <div class="line"></div>
                                                        <div class="line short"></div>
                                                    </div>
                                                    
                                                    <!-- WhatsApp Button Preview -->
                                                    <div class="whatsapp-button-preview <?php echo esc_attr($pos); ?>">
                                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Card Info -->
                                            <div class="card-info">
                                                <h4 class="position-title !font-secondary"><?php echo esc_html($config['title']); ?></h4>
                                                <p class="position-description !font-secondary"><?php echo esc_html($config['description']); ?></p>
                                            </div>
                                            
                                            <!-- Selection Indicator -->
                                            <div class="selection-indicator">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php submit_button('Save Changes'); ?>
                        </form>
                    </div>
        
                </div>                
            
        </div> 
        <?php
    }




}

