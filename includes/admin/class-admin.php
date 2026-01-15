<?php
namespace Converso\Admin;

use Converso\Modules\Agents;
use Converso\Modules\DynamicFields;
use Converso\Modules\General;
use Converso\Modules\Settings;
use Converso\Modules\StylingAndPosition;
use Converso\Core\Notification;
use Converso\Helpers\ReportExporter;
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
            __('Converso', 'converso'),
            __('Converso', 'converso'),
            'manage_options',
            'converso',
            [$this, 'render_admin_page'],
            'dashicons-admin-generic',
            25
        );
    }

    public function render_admin_page() {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';

        echo '<div class="wrap">';
        echo '<h1 class="font-primary !mb-4 !font-bold">Converso - Whatsapp Lead Collection</h1>';
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

        echo '<a href="?page=converso&tab=' . esc_attr($slug) . '" 
                class="text-sm px-5 py-2 ' . $bg_class . ' mr-2 rounded  font-primary">
                ' . esc_html($label) . '
            </a>';
    }

    public static function enqueue_global_scripts(){
        wp_enqueue_style("converso-global-css",CONVERSO_PLUGIN_URL . "assets/css/tailwind.css", [], 1, false);
    }
}
