<?php

namespace Converso\Frontend;

use Converso\Core\Log;
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
use Converso\Services\AgentsService;
use Converso\Services\ClicksService;

class Frontend{

    protected static $converso_wp_buttons = [];
    protected static $converso_wp_positions = [];

    
    public function __construct(){

        $is_active = get_option("converso_enable_whatsapp", false);
        
        if(!$is_active){
            return;
        }
        
        add_action('wp_footer', [$this, 'footer_scripts']);
        add_action('wp_enqueue_scripts',[$this, 'enqueue_scripts']);

        add_action('wp_ajax_converso_reverse_geo', [$this,'converso_reverse_geo']);
        add_action('wp_ajax_nopriv_converso_reverse_geo', [$this, 'converso_reverse_geo']);

        add_action('wp_ajax_converso_register_click', [$this, 'converso_register_click']);
        add_action('wp_ajax_nopriv_converso_register_click', [$this, 'converso_register_click']);

        
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
        try {
            $lat = isset($_POST['lat']) ? sanitize_text_field($_POST['lat']) : '';
            $lon = isset($_POST['lon']) ? sanitize_text_field($_POST['lon']) : '';
            
            if (!$lat || !$lon) {
                // Try IP detection if GPS is missing
                $user_location = Helper::get_client_location_by_ip();
            } else {
                $user_location = Helper::get_client_location($lat, $lon);
            }

            // Ensure $user_location is ALWAYS an array with keys
            $user_location = wp_parse_args($user_location, [
                'city'    => 'Unknown',
                'state'   => '',
                'country' => 'Unknown'
            ]);

            $current_agent = Helper::filter_agent( 
                [
                    "city"=> $user_location['city'], 
                    "state"=> $user_location['state'], 
                    "country"=> $user_location['country']
                ]
            );

            $response = [
                'visitor_location' => [
                    'country' => $user_location['country'],
                    'state'   => $user_location['state'],
                    'city'    => $user_location['city'],
                ],
                'agent' => null
            ];

            if ($current_agent) {
                $decoded = Helper::decode_dynamic_fields($current_agent);
                $phone = preg_replace('/\D+/', '', $decoded['phone'] ?? '');
                $message = urlencode($decoded['greetings'] ?? '');
                
                $decoded['wa_link'] = "https://wa.me/{$phone}?text={$message}";
                $response['agent'] = $decoded;
            } else {
                 // Check offline behavior if no agent found
                 $offline_behavior = get_option('converso_offline_behavior', '');
                 if ($offline_behavior === '1') {
                     $primary = get_option('converso_primary_number', '');
                     if ($primary) {
                         $offline_msg = get_option('converso_offline_message', 'We are currently offline.');
                         $phone = preg_replace('/\D+/', '', $primary);
                         $wa_link = "https://wa.me/{$phone}?text=" . urlencode($offline_msg);
                         
                         $response['agent'] = [
                             'id' => 0,
                             'name' => 'Support (Offline)',
                             'wa_link' => $wa_link,
                             'greetings' => $offline_msg,
                             'photo_url' => ''
                         ];
                     }
                 }
            }

            wp_send_json_success($response);

        } catch (\Exception $e) {
            Log::info("Critical Error in converso_reverse_geo: " . $e->getMessage());
            // Fail-safe response to prevent JS errors
            wp_send_json_success([
                'visitor_location' => ['country' => 'Unknown', 'state' => '', 'city' => 'Unknown'],
                'agent' => null
            ]);
        }
        wp_die();
    }

    public function converso_register_click() {
        $agent_id = isset($_POST['agent_id']) ? intval($_POST['agent_id']) : 0;
        $visitor_id = isset($_POST['visitor_id']) ? sanitize_text_field($_POST['visitor_id']) : '';
        $page_path = isset($_POST['page_path']) ? sanitize_text_field($_POST['page_path']) : '';
        
        if (!$visitor_id) {
            wp_send_json_error('Missing visitor ID');
        }

        $result = ClicksService::create_click([
            'agent_id' => $agent_id,
            'visitor_id' => $visitor_id,
            'page_path' => $page_path,
            'location_country' => isset($_POST['location_country']) ? sanitize_text_field($_POST['location_country']) : '',
            'location_state' => isset($_POST['location_state']) ? sanitize_text_field($_POST['location_state']) : '',
            'location_city' => isset($_POST['location_city']) ? sanitize_text_field($_POST['location_city']) : '',
            'stat_date' => current_time('Y-m-d'),
            'created_at' => current_time('mysql'),
            'source' => 'widget'
        ]);
        
        Log::info($result);
        if ($result) {
            wp_send_json_success();
        } else {
            wp_send_json_error('DB Error');
        }
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
                "ajax_url" => admin_url("admin-ajax.php"),
                "display_delay" => get_option('converso_display_delay', 0),
                "scroll_delay" => get_option('converso_scroll_delay', 0)
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