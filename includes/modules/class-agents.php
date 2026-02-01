<?php
namespace Connectapre\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


use Connectapre\Core\Log\Log;
use Connectapre\Services\AgentsService;
use Connectapre\Core\Notification;

class Agents {

    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_init', [$this, 'register_settings']);
        
        add_action('admin_init', [$this, 'handle_add_agent']);
        add_action('admin_init', [$this, 'handle_delete_agent']);
        add_action('admin_init', [$this, 'handle_edit_agent']);

    }

    public function enqueue_assets($hook) {
        if ($hook !== 'toplevel_page_connectapre') return;

        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        if ($tab !== 'agents') return;

        if ( ! did_action( 'wp_enqueue_media' ) ) {
            wp_enqueue_media();
        } 

        wp_enqueue_script(
            'connectapre-agents-js',
            CONNECTAPRE_PLUGIN_URL . "/assets/js/agents.js",
            ['jquery'],
            '1.0',
            true
        );

        // Optional CSS tweaks
        wp_enqueue_style(
            'connectapre-admin-css',
            CONNECTAPRE_PLUGIN_URL . "/assets/css/agents.css",
            [],
            '1.0'
        );
    }

    public function register_settings() {
        register_setting(
            'connectapre_agents_settings',
            'connectapre_agents_data',
            [$this, 'sanitize_agents']
        );
    }

    public function handle_add_agent() {

        if (
            !isset($_POST['connectapre_action']) ||
            $_POST['connectapre_action'] !== 'add_agent'
        ) {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Unauthorized request', 'connectapre'));
        }

        check_admin_referer('connectapre_agents');
    
        $errors = [];

        // Name (required)
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        if (empty($name)) {
            $errors[] = 'name_required';
        }

        // Phone (required)
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        if (empty($phone)) {
            $errors[] = 'phone_required';
        }

        // Greetings (optional)
        $greetings = sanitize_text_field($_POST['greeting'] ?? '');

        // Location (required)
        $location = isset($_POST['location']) ? sanitize_text_field($_POST['location']) : '';
        if (empty($location)) {
            $errors[] = 'location_required';
        }

        $photo = esc_url_raw($_POST['photo'] ?? '');
        if (!empty($photo) && !filter_var($photo, FILTER_VALIDATE_URL)) {
            $errors[] = 'photo_invalid';
        }

        $is_default = isset($_POST['is_default']) && $_POST['is_default'] === '1';

        // Checkbox checked = '1' = Enabled/Online.
        $is_online = isset($_POST['status']) && $_POST['status'] === '1';

        if (!empty($errors)) {
            wp_redirect(
                add_query_arg(
                    ['agent_error' => implode(',', $errors)],
                    admin_url('admin.php?page=connectapre&tab=agents')
                )
            );
            exit;
        }

        $location_array = explode(',', $location);
        $location_city = $location_array[0];
        $location_state = $location_array[1];
        $location_country = $location_array[2];

        $agent_data = [
            'uuid'          => wp_generate_uuid4(),
            'name'          => $name,
            'phone'         => $phone,
            'greeting'      => $greetings,
            'location_city' => $location_city,
            'location_state' => $location_state,
            'location_country' => $location_country,
            'photo_url'     => $photo,
            'is_default'    => $is_default ? 1 : 0,
            'is_active'     => $is_online ? 1 : 0,
            'created_at'    => current_time('mysql'),
        ];

        AgentsService::create_agent($agent_data);
    
        Notification::success('Agent added successfully!', 'Success');

        wp_redirect(
            add_query_arg(
                [],
                admin_url('admin.php?page=connectapre&tab=agents')
            )
        );

        exit;
    }

    public function handle_edit_agent() {

        if (
            !isset($_POST['connectapre_action']) ||
            $_POST['connectapre_action'] !== 'edit_agent'
        ) {
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Unauthorized request', 'connectapre'));
        }

        // Nonce check
        check_admin_referer('connectapre_agents');

        $errors = [];
    
        $agent_id = sanitize_text_field($_POST['agent_id'] ?? '');
        if (empty($agent_id)) {
            $errors[] = 'invalid_agent';
        }

        $name = sanitize_text_field($_POST['edit--name'] ?? '');
        if (empty($name)) {
            $errors[] = 'name_required';
        }

        $phone = sanitize_text_field($_POST['edit--phone'] ?? '');
        if (empty($phone)) {
            $errors[] = 'phone_required';
        }

        $greetings = sanitize_text_field($_POST['edit--greeting'] ?? '');

        $location = sanitize_text_field($_POST['edit--location'] ?? '');
        if (empty($location)) {
            $errors[] = 'location_required';
        }

        // Photo (optional)
        $new_photo = esc_url_raw($_POST['photo'] ?? '');
        if (!empty($new_photo) && !filter_var($new_photo, FILTER_VALIDATE_URL)) {
            $errors[] = 'photo_invalid';
        }

        // Default agent
        $is_default = isset($_POST['edit--is_default']) && $_POST['edit--is_default'] === '1';

        // Status (online/offline)
        $is_online = isset($_POST['edit--status']) && $_POST['edit--status'] === '1';

        /* =========================
        Redirect if errors
        ========================== */
        if (!empty($errors)) {
            wp_redirect(
                add_query_arg(
                    ['agent_error' => implode(',', $errors)],
                    admin_url('admin.php?page=connectapre&tab=agents')
                )
            );
            exit;
        }

        
        $location_array = explode(',', $location);

        $location_city = $location_array[0];
        $location_state = $location_array[1];
        $location_country = $location_array[2];

        $update_data = [
            'name'          => $name,
            'phone'         => $phone,
            'greeting'      => $greetings,
            'location_city' => $location_city,
            'location_state' => $location_state,
            'location_country' => $location_country,
            'is_default'    => $is_default ? 1 : 0,
            'is_active'     => $is_online ? 1 : 0,
            'updated_at'    => current_time('mysql'),
        ];

        if (!empty($new_photo)) {
            $update_data['photo_url'] = $new_photo;
        }

        AgentsService::update_agent($agent_id, $update_data);

        Notification::success('Agent updated successfully!', 'Success');

        wp_redirect(
            add_query_arg(
                [],
                admin_url('admin.php?page=connectapre&tab=agents')
            )
        );
        exit;
    }

    public function handle_delete_agent() {

        if (
            !isset($_POST['connectapre_action']) ||
            $_POST['connectapre_action'] !== 'delete_agent'
        ) {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Unauthorized request', 'connectapre'));
        }

        check_admin_referer('connectapre_delete_agent');

        $agent_id = sanitize_text_field($_POST['agent_id'] ?? '');

        if (empty($agent_id)) {
            wp_redirect(add_query_arg('agent_deleted', 0));
            exit;
        }

        $result = AgentsService::delete_agent($agent_id);

        if ($result === false) {
            Notification::error('Cannot delete the last remaining agent.', 'Error');
        } else {
            Notification::success('Agent deleted successfully!', 'Deleted');
        }

        wp_redirect(
            add_query_arg(
                [],
                admin_url('admin.php?page=connectapre&tab=agents')
            )
        );
        exit;
    }

    public function get_agents() {

        $filters = [];
        $filters['search'] = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $filters['status'] = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $filters['sort']   = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : '';
        $filters['page']   = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
        $filters['limit']  = isset($_GET['limit']) ? max(1, (int) $_GET['limit']) : 5;

        $result = AgentsService::get_agents($filters);
        
        $mapped_agents = array_map(function($row) {
            return [
                'id'        => $row['id'],
                'name'      => $row['name'],
                'phone'     => $row['phone'],
                'greetings' => $row['greeting'], // Map greeting -> greetings
                'location'  => $row['location_city'] . ', ' . $row['location_state'] . ', ' . $row['location_country'], // Map location_city -> location
                'photo'     => $row['photo_url'], // Map photo_url -> photo
                'default'   => $row['is_default'] == 1,
                'connectapre_agents_is_offline' => $row['is_active'] == 0, // Invert Active -> Offline
                'status'    => $row['is_active'] == 1 ? 'online' : 'offline', // Added for completeness
                'created_at'=> $row['created_at']
            ];
        }, $result['agents']);

        return [
            "agents"       => $mapped_agents,
            "total_pages"  => $result['total_pages'],
            "total_agents" => $result['total_agents'],
            "page"         => $result['page'],
            "limit"        => $result['limit'],
            "search"       => $result['search'],
            "status"       => $result['status'],
            "sort"         => $result['sort']
        ];
    }


    public function render() {
        $data = $this->get_agents();
        $agents = is_array($data['agents']) ? $data['agents'] : [] ;
    
        $total_pages = $data['total_pages'];
        $limit = $data['limit'];
        $search = $data['search'];
        $page = $data['page'];
        ?>
<div class="wrap relative !bg-white !p-4 !px-6 !rounded !mt-5">
    <div class=" flex justify-between items-center">
        <div>

            <h3 class="font-primary !mb-0 !mt-0 !text-xl">Agents</h3>
            <p class="!mt-2 !font-primary !text-sm !text-gray-500">Manage your WhatsApp support team and their availability</p>
        </div>
        
        
        <div>
            <button onclick="openModal()" class="bg-primary py-2 px-5 font-primary text-white rounded cursor-pointer !font-secondary"
                id="add-agent"><i class="ri-add-large-line mr-2"></i> Add Agent</button>
        </div>
    </div>
    <div class="flex justify-between items-end gap-5 mt-5">
        

        <div class="w-3/5">
            <label for="" class="font-secondary !pb-2 !text-xs text-grey-3 ">Search</label>
            <div class="relative !mt-2">
                <div class="absolute top-[53%] left-4 -translate-y-1/2">
                    <i class="ri-search-line"></i>
                </div>
                <input type="text" id="agent-search" value="<?php echo esc_attr($data['search']); ?>" placeholder="Search by name or phone"
                    class="w-full h-full rounded-lg  !font-secondary !text-xs !pl-8 !py-3 !bg-gray-200 !border-none">
            </div>
        </div>
        <div class="w-1/5">
            <label for="" class="font-secondary !pb-2 !text-xs text-grey-3 ">Status</label>
            <div class="relative !mt-2">
                <div class="absolute top-[53%] left-4 -translate-y-1/2">
                    <i class="ri-equalizer-2-line"></i>
                </div>
                <select name=""
                    class="w-full h-full rounded-lg  !font-secondary !text-xs !pl-10 !py-3 !bg-gray-200 !border-none"
                    id="agent-status">
                    <option value="">All Status</option>
                    <option value="online" <?php selected($data['status'], 'online'); ?>>Online</option>
                    <option value="offline" <?php selected($data['status'], 'offline'); ?>>Offline</option>
                </select>
            </div>
        </div>       
        <div class="w-1/5">
            <label for="" class="font-secondary !pb-2 !text-xs text-grey-3 ">Sort By</label>
            <div class="relative !mt-2">
                <div class="absolute top-[53%] left-4 -translate-y-1/2">
                    <i class="ri-equalizer-2-line"></i>
                </div>
                <select name=""
                    class="w-full h-full rounded-lg  !font-secondary !text-xs !pl-10 !py-3 !bg-gray-200 !border-none"
                    id="agent-sort">
                    <option value=""><?php esc_html_e('All', 'connectapre'); ?></option>
                    <option value="date" <?php selected($data['sort'], 'date'); ?>><?php esc_html_e('Date Created', 'connectapre'); ?></option>
                    <option value="name" <?php selected($data['sort'], 'name'); ?>><?php esc_html_e('Name', 'connectapre'); ?></option>
                    <option value="oldest" <?php selected($data['sort'], 'oldest'); ?>><?php esc_html_e('Oldest', 'connectapre'); ?></option>
                </select>
            </div>
        </div>    
        <div class="w-1/5">        
            <button class="bg-gray-800 w-full !py-3 px-5 !font-primary text-white rounded cursor-pointer" id="apply-filter"><?php esc_html_e('Apply Filter', 'connectapre'); ?></button>
        </div>
    </div>
    
    <div class="toast-placeholder">
        <?php settings_errors("connectapre_agents_settings") ?>
    </div>

    <div id="modalBackdrop"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300">
        <div id="addAgentModal"
            class="add-agent-modal opacity-0 scale-90 !px-6 !py-5 !max-h-[70%]  absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 transition-all duration-300 ease-out overflow-y-scroll">
            <div class="flex justify-between items-center mb-2">
                <h2 class="text-xl !m-0 font-semibold" style="font-family: var(--font-primary);">
                    <?php esc_html_e('Add New Agent', 'connectapre'); ?>
                </h2>
                <button onclick="closeModal()" class="text-lg">✖</button>
            </div>

            <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=connectapre&tab=agents')); ?>"
                class="!mt-4" id="agentForm">
                <?php wp_nonce_field('connectapre_agents'); ?>
                <input type="hidden" name="connectapre_action" value="add_agent" />
                <div class="mb-8 agent-photo-picker">
                    <div class="flex items-center gap-6">
                        <div
                            class="photo-preview w-24 h-24 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-dashed border-blue-300 flex items-center justify-center overflow-hidden">
                            <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>

                        <div class="flex-1 mt-2">
                            <button type="button"
                                class="choose-photo-btn px-5 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg font-medium border border-blue-200">
                                Choose Photo
                            </button>
                            <p class="text-xs text-gray-500 mt-2">JPG, PNG or GIF (Max 2MB)</p>
                        </div>
                    </div>

                    <input type="hidden" name="photo" class="photo-input">
                </div>

                <!-- Name and Phone -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php esc_html_e('Full Name', 'connectapre'); ?><span
                                class="text-red-500">*</span></label>
                        <input type="text" name="name" placeholder="Eg. Sujit Ale Magar"
                            class="w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none"
                            required />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number<span
                                class="text-red-500">*</span></label>
                        <input type="text" name="phone" placeholder="Eg. +977 9813213123"
                            class="w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none"
                            required />
                    </div>
                </div>

                <!-- Location and Default -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Location<span
                                class="text-red-500">*</span></label>
                        <input type="text" name="location" placeholder="Eg. Kathmandu, Nepal"
                            class="location-select w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none"
                            required />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Set as Default<span
                                class="text-red-500">*</span></label>
                        <select name="is_default"
                            class="w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                </div>

                <div class=" mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Greeting <span
                            class="text-red-500">*</span></label>
                    <textarea name="greeting" placeholder="Eg. Hii How Can I Help You" rows="3"
                        class="w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none"
                        required></textarea>
                </div>

                <!-- Status Toggle -->
                <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Agent Status</label>
                            <p class="text-xs text-gray-500 mt-1">Enable or disable this agent</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="status" value="1" class="sr-only peer" checked>
                            <div
                                class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold shadow-lg shadow-blue-500/30 hover:shadow-xl hover:shadow-blue-500/40 transition-all">
                    Save Agent
                </button>
            </form>
        </div>
    </div>

    <div id="delete-agent-modal-backdrop"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300">
        <div id="delete-agent-modal"
            class="bg-white opacity-0 scale-90 !px-6 !py-6  absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 transition-all duration-300 ease-out">

            <h2 class="text-xl !m-0 font-semibold" style="font-family: var(--font-primary);">
                Delete Agent
            </h2>

            <p class="text-sm text-gray-600 mt-2">
                Are you sure you want to delete this agent? This action cannot be undone.
            </p>

            <form method="post" class="mt-6 flex items-center justify-end gap-3">
                <?php wp_nonce_field('connectapre_delete_agent'); ?>

                <input type="hidden" name="connectapre_action" value="delete_agent">
                <input type="hidden" name="agent_id" id="delete-agent-id">

                <button type="button" class="py-2 px-5  rounded bg-gray-200 text-gray-700 close-delete-modal">
                    Cancel
                </button>

                <button type="submit" class="py-2 px-5 font-primary text-white rounded cursor-pointer !bg-red-600">
                    Delete
                </button>
            </form>

        </div>
    </div>

    <div id="edit-agent-modal-backdrop"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300">
        <div id="edit-agent-modal"
            class="add-agent-modal opacity-0 scale-90 !px-6 !py-5  absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 transition-all duration-300 ease-out overflow-y-scroll max-h-[70%]">

            <div class="flex justify-between items-center mb-2">
                <h2 class="text-xl !m-0 font-semibold" style="font-family: var(--font-primary);">
                    Edit Agent
                </h2>
                <button class="text-lg close-edit-modal">✖</button>
            </div>

            <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=connectapre&tab=agents')); ?>"
                class="!mt-4" id="agentForm">
                <?php wp_nonce_field('connectapre_agents'); ?>
                <input type="hidden" name="connectapre_action" value="edit_agent" />

                <div class="mb-8 agent-photo-picker">
                    <div class="flex items-center gap-6">
                        <div id="edit--photoPreview"
                            class="photo-preview w-24 h-24 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-dashed border-blue-300 flex items-center justify-center overflow-hidden">
                            <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>

                        <div class="flex-1 mt-2">
                            <button type="button"
                                class="choose-photo-btn px-5 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg font-medium border border-blue-200">
                                Choose Photo
                            </button>
                            <p class="text-xs text-gray-500 mt-2">JPG, PNG or GIF (Max 2MB)</p>
                        </div>
                    </div>

                    <input type="hidden" name="photo" class="photo-input">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name<span
                                class="text-red-500">*</span></label>
                        <input type="text" id="edit--name" name="edit--name" placeholder="Eg. Sujit Ale Magar"
                            class="w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none"
                            required />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number<span
                                class="text-red-500">*</span></label>
                        <input type="text" id="edit--phone" name="edit--phone" placeholder="Eg. +977 9813213123"
                            class="w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none"
                            required />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Location<span
                                class="text-red-500">*</span></label>
                        <input type="text" id="edit--location" name="edit--location" placeholder="Eg. Kathmandu, Nepal"
                            class="location-select w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none"
                            required />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Set as Default<span
                                class="text-red-500">*</span></label>
                        <select id="edit--is_default" name="edit--is_default"
                            class="w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                </div>

                <div class=" mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Greeting <span
                            class="text-red-500">*</span></label>
                    <textarea id="edit--greeting" name="edit--greeting" placeholder="Eg. Hii How Can I Help You"
                        rows="3"
                        class="w-full !px-4 !py-2.5 !border !border-gray-300 !rounded-lg !focus:ring-2 !focus:ring-blue-500 !focus:border-transparent !transition-all !outline-none"
                        required></textarea>
                </div>

                <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Agent Status</label>
                            <p class="text-xs text-gray-500 mt-1">Enable or disable this agent</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="edit--status" name="edit--status" value="1" class="sr-only peer"
                                checked>
                            <div
                                class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                            </div>
                        </label>
                    </div>
                </div>
                <input type="hidden" name="agent_id" id="edit--agent_id">

                <button type="submit"
                    class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold shadow-lg shadow-blue-500/30 hover:shadow-xl hover:shadow-blue-500/40 transition-all">Save
                    Agent</button>
            </form>
        </div>
    </div>

    <div>
        <table class="w-full table-fixed text-left  mt-5" id="connectapre-agents-table">
            <thead>
                <tr>
                    <th class="!text-gray-800 w-[8%]  !pb-4 !font-semibold !font-primary">Photo</th>
                    <th class="!text-gray-800 w-[25%] !pb-4 !font-semibold !font-primary">Name</th>
                    <th class="!text-gray-800 w-[16%] !pb-4 !font-semibold !font-primary">Phone</th>
                    <th class="!text-gray-800 w-[23%] !pb-4 !font-semibold !font-primary">Location</th>
                    <th class="!text-gray-800 w-[12%] !pb-4 !font-semibold !font-primary">Status</th>
                    <th class="!text-gray-800 w-[16%] !pb-4 !font-semibold !font-primary">Actions</th>

                </tr>
            </thead>
            <tbody>
                <?php if (!empty($agents)) : ?>

                <?php foreach ($agents as $index => $agent) : ?>
                <tr class="!mt-4" id="<?php echo esc_attr($agent['id'])?>">
                    <td class="hidden agent-greeting">
                        <?php echo esc_attr($agent['greetings']); ?>
                    </td>
                    <td>
                        <img src="<?php echo esc_attr($agent['photo'] ?? CONNECTAPRE_PLUGIN_URL . 'assets/img/person-dummy.jpg'); ?>"
                            class="agent-image rounded-full w-full aspect-square max-w-7 object-cover" alt="">
                    </td>
                    <td class="">
                        <h3 class="!text-sm !text-gray-800 !font-normal !font-primary ">
                            <span class="agent-name"><?php echo esc_attr($agent['name']); ?></span>   <?php echo !empty($agent['default']) 
                                ? "<span class='bg-cyan-600 ml-2 agent-is-default rounded-xl !text-xs !font-primary px-4 py-1 text-gray-100'>Default</span>" 
                                : ""; 
                            ?>
                        </h3>
                    </td>
                    <td class="">
                        <h3 class="!text-sm !text-gray-800 !font-normal !font-primary agent-phone">
                            <?php echo esc_attr($agent['phone']); ?>
                        </h3>
                    </td>
                    <td>
                        <span class="bg-gray-300 rounded-xl !text-xs !font-primary px-4 py-1 text-black agent-location"><?php echo esc_attr($agent['location']); ?></span>
                    </td>
                    <td class="">
                        <h3 class="!text-sm !text-gray-800 !font-normal !font-primary agent-status">
                            <?php echo !empty($agent['connectapre_agents_is_offline']) ? "<span class='text-gray-700'>Offline</span>" : "<span class='text-green-700'>Online</span>" ?>
                        </h3>
                    </td>                
                    <td>
                        <button type="button" class="button bg-button-danger edit-agent"><i
                                class="ri-pencil-line"></i></button>
                        <button type="button" class="button bg-button-danger remove-agent"><i
                                class="ri-delete-bin-7-line"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
            <div class="mt-6 flex justify-end gap-2">
                <div class="flex gap-2">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php
                                        $url = add_query_arg([
                                            'paged' => $i,
                                            's'     => $search,
                                            'status' => $data['status'],
                                            'sort'  => $data['sort'],
                                        ]);
                                    ?>
                    <a href="<?php echo esc_url($url); ?>"
                        class="!px-4 !py-2 !font-primary rounded <?php echo $i === $page ? 'bg-primary !text-white' : 'bg-gray-200'; ?>">
                        <?php echo (int) $i; ?>
                    </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
    }
}

