<?php
namespace Connectapre\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Wizard {

    public function __construct() {
        add_action('admin_menu', [$this, 'register_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_connectapre_save_wizard', [$this, 'save_wizard_data']);
    }

    public function register_page() {
        add_submenu_page(
            '', // Hidden page
            __('Connectapre Setup Wizard', 'connectapre'),
            __('Setup Wizard', 'connectapre'),
            'manage_options',
            'connectapre-wizard',
            [$this, 'render']
        );
    }

    public function enqueue_assets($hook) {
        if ($hook !== 'admin_page_connectapre-wizard') {
            return;
        }

        wp_enqueue_style('connectapre-wizard-css', CONNECTAPRE_PLUGIN_URL . 'assets/css/wizard.css', [], '1.0');
        wp_enqueue_script('connectapre-wizard-js', CONNECTAPRE_PLUGIN_URL . 'assets/js/wizard.js', ['jquery'], '1.0', true);
        
        wp_localize_script('connectapre-wizard-js', 'connectapreWizard', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('connectapre_wizard_nonce'),
            'redirect_url' => admin_url('admin.php?page=connectapre')
        ]);
        
        wp_enqueue_media();
    }

    public function render() {
        ?>
        <div class="connectapre-wizard-wrap">
            <div class="connectapre-wizard-header">
                <h1>Welcome to Connectapre</h1>
                <p>Let's get your WhatsApp chat widget set up in just a few steps.</p>
            </div>

            <div class="connectapre-wizard-steps">
                <div class="step active" data-step="1">1. Business Info</div>
                <div class="step" data-step="2">2. Add Agent</div>
                <div class="step" data-step="3">3. Finish</div>
            </div>

            <form id="connectapre-wizard-form">
                <!-- Step 1: Business Info -->
                <div class="wizard-step-content active" data-step="1">
                    <h2>Business Information</h2>
                    <table class="form-table">
                        <tr>
                            <th><label for="business_name">Business Name</label></th>
                            <td><input type="text" name="business_name" id="business_name" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th><label for="business_type">Business Type</label></th>
                            <td>
                                <select name="business_type" id="business_type" required>
                                    <option value="">Select Type</option>
                                    <option value="retail">Retail</option>
                                    <option value="service">Service</option>
                                    <option value="restaurant">Restaurant</option>
                                    <option value="education">Education</option>
                                    <option value="other">Other</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <div class="wizard-nav">
                        <button type="button" class="button button-primary next-step">Next</button>
                    </div>
                </div>

                <!-- Step 2: Add Agent -->
                <div class="wizard-step-content" data-step="2">
                    <h2>Add Your First Agent</h2>
                    <p>You need at least one agent to handle chats.</p>
                    <table class="form-table">
                        <tr>
                            <th><label for="agent_name">Agent Name</label></th>
                            <td><input type="text" name="agent_name" id="agent_name" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th><label for="agent_phone">WhatsApp Number</label></th>
                            <td>
                                <input type="text" name="agent_phone" id="agent_phone" class="regular-text" required>
                                <p class="description">Include country code (e.g., +1234567890)</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="agent_photo">Photo</label></th>
                            <td>
                                <input type="text" name="agent_photo" id="agent_photo" class="regular-text">
                                <button type="button" class="button select-photo">Select Image</button>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="agent_location">Location</label></th>
                            <td class="connectapre_location_parent">
                                <input type="text" name="agent_location" id="agent_location" class="regular-text connectapre_location" placeholder="Select location" required>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="agent_role">Role/Title</label></th>
                            <td><input type="text" name="agent_role" id="agent_role" class="regular-text" placeholder="e.g. Support Agent"></td>
                        </tr>
                    </table>
                    <div class="wizard-nav">
                        <button type="button" class="button button-secondary prev-step">Previous</button>
                        <button type="button" class="button button-primary next-step">Next</button>
                    </div>
                </div>

                <!-- Step 3: Finish -->
                <div class="wizard-step-content" data-step="3">
                    <h2>All Set!</h2>
                    <p>Your WhatsApp chat widget is ready to go.</p>
                    <div class="wizard-nav">
                        <button type="button" class="button button-secondary prev-step">Previous</button>
                        <button type="submit" class="button button-primary finish-wizard">Finish & Go to Dashboard</button>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }

    public function save_wizard_data() {
        check_ajax_referer('connectapre_wizard_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $business_name = isset($_POST['business_name']) ? sanitize_text_field(wp_unslash($_POST['business_name'])) : '';
        $business_type = isset($_POST['business_type']) ? sanitize_text_field(wp_unslash($_POST['business_type'])) : '';
        
        $agent_name     = isset($_POST['agent_name']) ? sanitize_text_field(wp_unslash($_POST['agent_name'])) : '';
        $agent_phone    = isset($_POST['agent_phone']) ? sanitize_text_field(wp_unslash($_POST['agent_phone'])) : '';
        $agent_photo    = isset($_POST['agent_photo']) ? esc_url_raw(wp_unslash($_POST['agent_photo'])) : '';
        $agent_location = isset($_POST['agent_location']) ? sanitize_text_field(wp_unslash($_POST['agent_location'])) : '';
        $agent_role     = isset($_POST['agent_role']) ? sanitize_text_field(wp_unslash($_POST['agent_role'])) : ''; // Note: Role is not in the main schema yet, but we'll keep it if needed or map it. 
        // Actually, looking at class-agents.php, there is no 'role' field in the schema, only name, phone, greetings, location, photo, default, is_offline.
        // I will append role to name or ignore it if not needed, but user asked for "Role/Title" in my previous plan. 
        // Let's assume 'greetings' can be used or just ignore role for now if schema doesn't support it. 
        // Or better, let's put it in greetings as a default "Hi, I am [Name], [Role]".
        
        $greetings = "Hi! How can I help you?";
        if ($agent_role) {
            $greetings = "Hi! I am $agent_name, $agent_role. How can I help you?";
        }

        // Save General Settings
        update_option('connectapre_business_name', $business_name);
        update_option('connectapre_business_type', $business_type);
        update_option('connectapre_enable_whatsapp', 1); // Enable by default

        // Save Agent
        $agents = [];
        $agents[] = [
            'name' => $agent_name,
            'phone' => $agent_phone,
            'photo' => $agent_photo,
            'location' => $agent_location,
            'greetings' => $greetings,
            'default' => true, // Set as default
            'connectapre_agents_is_offline' => false
        ];
        
        update_option('connectapre_agents_data', $agents);
        update_option('connectapre_wizard_completed', true);

        wp_send_json_success();
    }
}

