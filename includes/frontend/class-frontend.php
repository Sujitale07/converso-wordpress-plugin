<?php

namespace Converso\Frontend;

use Converso\Frontend\Buttons\BtnOne;
use Converso\Frontend\Buttons\BtnTwo;
use Converso\Frontend\Buttons\BtnThree;
use Converso\Frontend\Buttons\BtnFour;
use Converso\Frontend\Buttons\BtnFive;
use Converso\Frontend\Buttons\BtnSix;
use Converso\Frontend\Buttons\BtnSeven;
use Converso\Frontend\Buttons\BtnEight;
use Converso\Frontend\Buttons\BtnNine;
use Converso\Frontend\Buttons\BtnTen;
use Converso\Frontend\Buttons\BtnEleven;
use Converso\Frontend\Buttons\BtnTwelve;
use Converso\Frontend\Buttons\BtnThirteen;
use Converso\Frontend\Buttons\BtnFourteen;
use Converso\Frontend\Buttons\BtnFifteen;
use Converso\Frontend\Buttons\BtnSixteen;
use Converso\Frontend\Buttons\BtnSeventeen;
use Converso\Frontend\Buttons\BtnEighteen;
use Converso\Frontend\Buttons\BtnNineteen;
use Converso\Frontend\Buttons\BtnTwenty;
use Converso\Frontend\Positions\BottomLeft;
use Converso\Frontend\Positions\BottomRight;
use Converso\Frontend\Positions\TopLeft;
use Converso\Frontend\Positions\TopRight;
use Converso\Helpers\Helper;

class Frontend{

    protected static $converso_wp_buttons = [];
    protected static $converso_wp_positions = [];

    
    public function __construct(){

        $is_active = get_option("converso_enable_whatsapp", true);

        if(!$is_active){
            return;
        }
        
        add_action('wp_footer', [$this, 'footer_scripts']);
        add_action('wp_enqueue_scripts',[$this, 'enqueue_scripts']);

        add_action('wp_ajax_converso_reverse_geo', [$this,'converso_reverse_geo']);
        add_action('wp_ajax_nopriv_converso_reverse_geo', [$this, 'converso_reverse_geo']);

        
        self::$converso_wp_buttons = array(
            "btn-1"=> new BtnOne(),
            "btn-2"=> new BtnTwo(),
            "btn-3"=> new BtnThree(),
            "btn-4"=> new BtnFour(),
            "btn-5"=> new BtnFive(),
            "btn-6"=> new BtnSix(),
            "btn-7"=> new BtnSeven(),
            "btn-8"=> new BtnEight(),
            "btn-9"=> new BtnNine(),
            "btn-10"=> new BtnTen(),
            "btn-11"=> new BtnEleven(),
            "btn-12"=> new BtnTwelve(),
            "btn-13"=> new BtnThirteen(),
            "btn-14"=> new BtnFourteen(),
            "btn-15"=> new BtnFifteen(),
            "btn-16"=> new BtnSixteen(),
            "btn-17"=> new BtnSeventeen(),
            "btn-18"=> new BtnEighteen(),
            "btn-19"=> new BtnNineteen(),
            "btn-20"=> new BtnTwenty()
        );

        self::$converso_wp_positions = array(
            "bottom-right"=>new BottomRight(),
            "bottom-left"=>new BottomLeft(),
            "top-right"=>new TopRight(),
            "top-left"=>new TopLeft()
        );
    }

    public function footer_scripts() {
        echo self::render_converso_wp_button();
    }
    function converso_reverse_geo() {
        $lat = sanitize_text_field($_POST['lat']);
        $lon = sanitize_text_field($_POST['lon']);

        if (!$lat || !$lon) {
            wp_send_json_error("Missing lat/lon");
        }

        // Get user location
        $user_location = Helper::get_client_location($lat, $lon);

        // Get all agents
        $get_agent = get_option("converso_agents_data", []);

        // Filter agent by city or get default
        $current_agent = Helper::filter_agent($get_agent, $user_location['city']);

        // Decode dynamic fields in greetings
        $decoded_agent_data = Helper::decode_dynamic_fields($current_agent);

        // Generate WhatsApp link
        $phone = preg_replace('/\D+/', '', $decoded_agent_data['phone']); // remove any non-digits
        $message = urlencode($decoded_agent_data['greetings']); // encode message for URL
        $wa_link = "https://wa.me/{$phone}?text={$message}";

        // Add WhatsApp link to agent data
        $decoded_agent_data['wa_link'] = $wa_link;

        wp_send_json_success($decoded_agent_data);
        wp_die();
    }



    public function enqueue_scripts() {

        wp_register_script(
            'converso-ip-scripts',
            CONVERSO_PLUGIN_URL . "assets/js/frontend/converso.js",
            [],
            1,
            true
        );

        wp_localize_script(
            "converso-ip-scripts",
            "converso_ajax",
            [
                "ajax_url" => admin_url("admin-ajax.php")
            ]
        );

        wp_enqueue_script('converso-ip-scripts');
    }


    public static function render_converso_wp_button(){
        $button_position = self::get_button_position();
        $current_button = self::get_current_button();

        $renderable_button = self::$converso_wp_positions["$button_position"]->render($current_button->render(), "#");

        return $renderable_button;
    }

    public static function get_button_position(){
        $button_style = get_option("converso_sap_button_position_data", "bottom-right");

        return $button_style;
    }
    
    public static function get_current_button(){
        $button_style = get_option('converso_sap_button_style_data', "btn-1");  
        
        return self::$converso_wp_buttons[$button_style];  
    }
}