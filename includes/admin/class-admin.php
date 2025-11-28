<?php
namespace Converso\Admin;

use Converso\Modules\Agents;
use Converso\Modules\DynamicFields;
use Converso\Modules\General;
use Converso\Modules\StylingAndPosition;

class Admin {

    private $modules = [];

    public function __construct() {
        $this->modules['general'] = new General();
        $this->modules['agents']  = new Agents();
        $this->modules['dynamic-fields']  = new DynamicFields();
        $this->modules['styling-and-positioning']  = new StylingAndPosition();

        add_action('admin_menu', [$this, 'register_admin_pages']);
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
        echo '<h1 style="margin-bottom: 10px;">Converso Settings</h1>';
        echo '<h2 class="nav-tab-wrapper">';
            $this->render_tab_link('general', 'General', $active_tab);
            $this->render_tab_link('agents', 'Agents', $active_tab);
            $this->render_tab_link('dynamic-fields', 'Dynamic Fields', $active_tab);        
            $this->render_tab_link('styling-and-positioning', 'Styling & Position', $active_tab);
            $this->render_tab_link('settings', 'Settings', $active_tab);
        echo '</h2>';

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
        $active = ($active_tab === $slug) ? ' nav-tab-active' : '';
        echo '<a href="?page=converso&tab=' . esc_attr($slug) . '" class="nav-tab' . $active . '">' . esc_html($label) . '</a>';
    }
}
