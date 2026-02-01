<?php
namespace Connectapre\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


use Connectapre\Modules\Agents;
use Connectapre\Modules\DynamicFields;
use Connectapre\Modules\General;
use Connectapre\Modules\Settings;
use Connectapre\Modules\StylingAndPosition;
use Connectapre\Core\Notification;
use Connectapre\Helpers\ReportExporter;
class Admin {

    private $modules = [];

    public function __construct() {
        $this->modules['general'] = new General();
        $this->modules['agents']  = new Agents();
        $this->modules['dynamic-fields']  = new DynamicFields();
        $this->modules['styling-and-positioning']  = new StylingAndPosition();
        $this->modules['settings'] = new Settings();
        
        Notification::init();
        ReportExporter::init();

        add_action('admin_menu', [$this, 'register_admin_pages']);
        add_action("admin_enqueue_scripts", [$this, "enqueue_global_scripts"]);
    }

    public function register_admin_pages() {
        add_menu_page(
            __('Connectapre', 'connectapre'),
            __('Connectapre', 'connectapre'),
            'manage_options',
            'connectapre',
            [$this, 'render_admin_page'],
            'dashicons-admin-generic',
            25
        );
    }

    public function render_admin_page() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab navigation does not require nonce.
        $active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'general';

        echo '<div class="wrap">';
        echo '<h1 class="font-primary !mb-4 !font-bold">Connectapre - Whatsapp Lead Collection</h1>';
        echo '<div class="!mt-5">';
            $this->render_tab_link('general', 'General', $active_tab);
            $this->render_tab_link('agents', 'Agents', $active_tab);
            $this->render_tab_link('dynamic-fields', 'Dynamic Fields', $active_tab);        
            $this->render_tab_link('styling-and-positioning', 'Styling & Position', $active_tab);
            $this->render_tab_link('settings', 'Settings', $active_tab);
        echo '</div>';

        switch ($active_tab) {
            case 'agents':
                if (!isset($this->modules['agents'])) {
                    $this->modules['agents'] = new Agents();
                }
                $this->modules['agents']->render();
                break;

            case 'dynamic-fields':
                if (!isset($this->modules['dynamic-fields'])) {
                    $this->modules['dynamic-fields'] = new DynamicFields();
                }
                $this->modules['dynamic-fields']->render();
                break;
                
            case 'styling-and-positioning':
                if (!isset($this->modules['styling-and-positioning'])) {
                    $this->modules['styling-and-positioning'] = new StylingAndPosition();
                }
                $this->modules['styling-and-positioning']->render();
                break;

            case 'settings':
                if (!isset($this->modules['settings'])) {
                    $this->modules['settings'] = new Settings();
                }
                $this->modules['settings']->render();
                break;

            case 'greetings':
                require_once plugin_dir_path(__FILE__) . 'tabs/class-greetings-tab.php';
                break;

            default:
                if (!isset($this->modules['general'])) {
                    $this->modules['general'] = new General();
                }
                $this->modules['general']->render();
                break;
        }


        echo '</div>';
    }

    private function render_tab_link($slug, $label, $active_tab) {
        $is_active = ($active_tab === $slug);

        $bg_class = $is_active ? 'bg-primary !text-grey-7' : 'bg-grey-6 !text-black';

        echo '<a href="?page=connectapre&tab=' . esc_attr($slug) . '" 
                class="text-sm px-5 py-2 ' . esc_attr($bg_class) . ' mr-2 rounded  font-primary">
                ' . esc_html($label) . '
            </a>';
    }

    public static function enqueue_global_scripts(){
        wp_enqueue_style("connectapre-global-css",CONNECTAPRE_PLUGIN_URL . "assets/css/tailwind.css", [], 1, false);
        wp_enqueue_style("connectapre-global-remixicon-css",CONNECTAPRE_PLUGIN_URL . "assets/fonts/remix/remixicon.css", [], 1, false);
    }
}

